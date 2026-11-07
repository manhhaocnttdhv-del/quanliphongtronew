<?php

namespace Tests\Feature;

use App\Http\Controllers\PaymentController;
use App\Models\BookingRequest;
use App\Models\Contract;
use App\Models\House;
use App\Models\Invoice;
use App\Models\PaymentOrder;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Booking\BookingDepositService;
use App\Services\Booking\BookingExpiryService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Mockery;
use PayOS\PayOS;
use PayOS\Utils\PayOSSignatureUtils;
use ReflectionClass;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RentalWorkflowRegressionTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        foreach (['admin', 'tenant', 'customer'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
        config(['services.payos' => ['client_id' => 'qa-client', 'api_key' => 'qa-api', 'checksum_key' => 'qa-checksum']]);
    }

    private function user(string $role = 'customer'): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function fixture(string $status = 'draft'): array
    {
        $user = $this->user('tenant');
        $tenant = Tenant::create(['user_id' => $user->id, 'cccd' => sprintf('%012d', $user->id), 'phone' => '0900000000']);
        $house = House::create(['name' => 'QA branch', 'address' => 'QA address', 'is_active' => true]);
        $room = Room::create([
            'house_id' => $house->id, 'name' => 'QA room', 'floor' => 1,
            'price' => 2500000, 'max_occupants' => 2, 'area' => 25,
            'status' => $status === 'active' ? 'rented' : 'reserved',
        ]);
        $contract = Contract::create([
            'room_id' => $room->id, 'tenant_id' => $tenant->id,
            'start_date' => today(), 'end_date' => today()->addYear(),
            'deposit' => 1000000, 'monthly_price' => 2500000,
            'occupants' => 1, 'status' => $status,
        ]);
        $booking = BookingRequest::create([
            'user_id' => $user->id, 'room_id' => $room->id,
            'tenant_id' => $tenant->id, 'contract_id' => $contract->id,
            'cccd' => $tenant->cccd, 'phone' => '0900000000',
            'desired_move_in_date' => today(), 'desired_occupants' => 1,
            'desired_lease_months' => 12, 'deposit_amount' => 1000000,
            'status' => 'approved', 'approved_at' => now(),
        ]);

        return compact('user', 'tenant', 'house', 'room', 'contract', 'booking');
    }

    private function monthlyInvoice(Contract $contract, array $extra = []): Invoice
    {
        return Invoice::create(array_merge([
            'contract_id' => $contract->id, 'type' => 'monthly',
            'month' => today()->month, 'year' => today()->year,
            'room_fee' => 2500000, 'total' => 2500000,
            'paid_amount' => 0, 'debt' => 2500000,
            'due_date' => today()->addDays(5), 'status' => 'unpaid',
            'notes' => '[PayOS] OrderCode: 990001',
        ], $extra));
    }

    private function fakeGateway(?callable $inspect = null): void
    {
        $gateway = Mockery::mock(PayOS::class);
        $gateway->shouldReceive('createPaymentLink')->andReturnUsing(function (array $data) use ($inspect) {
            if ($inspect) {
                $inspect($data);
            }

            return ['checkoutUrl' => 'https://example.invalid/qa-checkout'];
        });
        $reflection = new ReflectionClass(PaymentController::class);
        $controller = $reflection->newInstanceWithoutConstructor();
        $reflection->getProperty('payOS')->setValue($controller, $gateway);
        $this->app->instance(PaymentController::class, $controller);
    }

    public function test_public_screens_render_and_admin_area_is_protected(): void
    {
        $this->get('/')->assertOk();
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
        $this->get('/admin/dashboard')->assertRedirect(route('login'));
        $this->actingAs($this->user())->get('/admin/dashboard')->assertForbidden();
    }

    public function test_admin_work_screens_render(): void
    {
        $this->withoutExceptionHandling();
        $fixture = $this->fixture('active');
        $invoice = $this->monthlyInvoice($fixture['contract']);
        $this->actingAs($this->user('admin'));
        foreach (['dashboard', 'houses', 'houses/create', 'rooms', 'rooms/create', 'tenants', 'tenants/create', 'contracts', 'contracts/create', 'invoices', 'invoices/create', 'booking-requests', 'maintenance-tickets', 'announcements', 'settings'] as $path) {
            $this->get('/admin/'.$path)->assertOk();
        }
        $this->get('/admin/invoices/'.$invoice->id)->assertOk();
        $this->get('/admin/contracts/'.$fixture['contract']->id)->assertOk();
    }

    public function test_admin_room_detail_renders(): void
    {
        $this->withoutExceptionHandling();
        $fixture = $this->fixture('active');
        $this->actingAs($this->user('admin'))->get('/admin/rooms/'.$fixture['room']->id)->assertOk();
    }

    public function test_tenant_cannot_view_another_tenants_invoice(): void
    {
        $fixture = $this->fixture('active');
        $invoice = $this->monthlyInvoice($fixture['contract']);
        $this->actingAs($this->user('tenant'))->get(route('tenant.invoices.show', $invoice))->assertForbidden();
    }

    public function test_unsigned_return_url_does_not_settle_invoice(): void
    {
        $fixture = $this->fixture('active');
        $invoice = $this->monthlyInvoice($fixture['contract']);
        $this->get('/payment/success?orderCode=990001');
        $this->assertSame('unpaid', $invoice->fresh()->status, 'An unsigned guest GET must not settle an invoice.');
    }

    public function test_return_url_without_order_code_does_not_settle_any_invoice(): void
    {
        $fixture = $this->fixture('active');
        $invoice = $this->monthlyInvoice($fixture['contract']);
        $this->get('/payment/success');
        $this->assertSame('unpaid', $invoice->fresh()->status);
    }

    public function test_guest_cannot_create_checkout_for_an_invoice(): void
    {
        $fixture = $this->fixture('active');
        $invoice = $this->monthlyInvoice($fixture['contract']);
        $this->fakeGateway();
        $this->get(route('payment.checkout', $invoice))->assertRedirect(route('login'));
    }

    public function test_checkout_uses_actual_outstanding_balance(): void
    {
        $fixture = $this->fixture('active');
        $invoice = $this->monthlyInvoice($fixture['contract'], ['paid_amount' => 500000, 'debt' => 2000000, 'status' => 'partial']);
        $sentAmount = null;
        $this->fakeGateway(function (array $data) use (&$sentAmount) {
            $sentAmount = $data['amount'];
        });
        $this->actingAs($fixture['user'])->get(route('payment.checkout', $invoice));
        $this->assertSame(2000000, $sentAmount);
    }

    public function test_valid_signed_webhook_records_payment(): void
    {
        $fixture = $this->fixture('active');
        $invoice = $this->monthlyInvoice($fixture['contract']);
        $data = ['orderCode' => 990001, 'amount' => 2500000, 'reference' => 'QA-WEBHOOK-001', 'code' => '00'];
        PaymentOrder::create(['invoice_id' => $invoice->id, 'order_code' => 990001, 'amount' => 2500000]);
        $signature = PayOSSignatureUtils::createSignatureFromObj('qa-checksum', $data);
        $response = $this->postJson('/payment/payos_transfer_handler', ['code' => '00', 'success' => true, 'data' => $data, 'signature' => $signature]);
        $this->assertSame('paid', $invoice->fresh()->status, 'Signed webhook status '.$response->status().': '.$response->getContent());
        $response->assertOk();
    }

    public function test_repeated_partial_deposit_reference_is_counted_once(): void
    {
        $fixture = $this->fixture();
        $service = app(BookingDepositService::class);
        $invoice = $service->createDepositInvoice($fixture['booking']);
        $service->markDepositPaid($invoice, 200000, 'QA-RETRY-001');
        $service->markDepositPaid($invoice->fresh(), 200000, 'QA-RETRY-001');
        $this->assertSame(200000, (int) $invoice->fresh()->paid_amount);
        $this->assertSame(1, $invoice->payments()->count());
    }

    public function test_late_deposit_does_not_rent_an_expired_booking(): void
    {
        $fixture = $this->fixture();
        $service = app(BookingDepositService::class);
        $invoice = $service->createDepositInvoice($fixture['booking']);
        $fixture['booking']->update(['approved_at' => now()->subHours(49)]);
        app(BookingExpiryService::class)->expireOverdue();
        $this->assertSame('cancelled', $fixture['contract']->fresh()->status);
        $service->markDepositPaid($invoice->fresh(), 1000000, 'QA-LATE-001');
        $this->assertSame('available', $fixture['room']->fresh()->status);
    }

    public function test_admin_cannot_create_second_active_contract_for_rented_room(): void
    {
        $fixture = $this->fixture('active');
        $this->actingAs($this->user('admin'))->post(route('admin.contracts.store'), [
            'room_id' => $fixture['room']->id, 'tenant_id' => $fixture['tenant']->id,
            'start_date' => today()->toDateString(), 'end_date' => today()->addYear()->toDateString(),
            'deposit' => 1000000, 'monthly_price' => 2500000, 'occupants' => 1,
        ]);
        $this->assertSame(1, Contract::where('room_id', $fixture['room']->id)->where('status', 'active')->count());
    }

    public function test_reactivating_contract_keeps_room_marked_rented(): void
    {
        $fixture = $this->fixture('active');
        $admin = $this->user('admin');
        $this->actingAs($admin)->put(route('admin.contracts.update', $fixture['contract']), ['status' => 'expired']);
        $this->assertSame('available', $fixture['room']->fresh()->status);
        $this->actingAs($admin)->put(route('admin.contracts.update', $fixture['contract']), ['status' => 'active']);
        $this->assertSame('active', $fixture['contract']->fresh()->status);
        $this->assertSame('rented', $fixture['room']->fresh()->status);
    }

    public function test_admin_created_tenant_can_access_tenant_portal(): void
    {
        $this->actingAs($this->user('admin'))->post(route('admin.tenants.store'), [
            'name' => 'QA new tenant', 'email' => 'qa-new-tenant@example.invalid',
            'cccd' => '999999999999', 'phone' => '0900000000', 'address' => 'QA address',
            'hometown' => 'QA hometown', 'birthday' => '2000-01-01', 'gender' => 'other',
        ])->assertRedirect(route('admin.tenants.index'));
        $newUser = User::where('email', 'qa-new-tenant@example.invalid')->firstOrFail();
        $this->actingAs($newUser)->get(route('tenant.dashboard'))->assertOk();
    }

    public function test_invoice_with_outstanding_debt_is_not_marked_paid(): void
    {
        $fixture = $this->fixture('active');
        $invoice = $this->monthlyInvoice($fixture['contract']);
        $this->actingAs($this->user('admin'))->put(route('admin.invoices.update', $invoice), [
            'electricity_old' => 0, 'electricity_new' => 0, 'water_old' => 0, 'water_new' => 0,
            'electricity_fee' => 0, 'water_fee' => 0, 'room_fee' => 2500000, 'service_fee' => 0,
            'paid_amount' => 0, 'status' => 'paid', 'notes' => 'QA inconsistent state',
        ]);
        $this->assertFalse($invoice->fresh()->is_paid);
    }

    public function test_deleting_account_does_not_erase_unpaid_rental_debt(): void
    {
        $fixture = $this->fixture('active');
        $invoice = $this->monthlyInvoice($fixture['contract']);
        $this->actingAs($fixture['user'])->delete(route('profile.destroy'), ['password' => 'password']);
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'debt' => 2500000]);
    }

    public function test_reserved_room_description_can_be_edited_without_releasing_it(): void
    {
        $fixture = $this->fixture();
        $this->actingAs($this->user('admin'))->put(route('admin.rooms.update', $fixture['room']), [
            'house_id' => $fixture['house']->id, 'name' => 'QA updated room', 'floor' => 1,
            'price' => 2500000, 'area' => 25, 'max_occupants' => 2,
            'status' => 'reserved', 'description' => 'QA updated description',
        ])->assertSessionHasNoErrors();
        $this->assertSame('reserved', $fixture['room']->fresh()->status);
    }

    public function test_first_full_deposit_activates_contract_and_records_receipt(): void
    {
        $fixture = $this->fixture();
        $fixture['contract']->update(['signed_at' => now(), 'signature_path' => 'qa/signature.png']);
        $service = app(BookingDepositService::class);
        $invoice = $service->createDepositInvoice($fixture['booking']);
        $service->markDepositPaid($invoice, 1000000, 'QA-FULL-001');
        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertSame('active', $fixture['contract']->fresh()->status);
        $this->assertSame('rented', $fixture['room']->fresh()->status);
        $this->assertNotNull($fixture['booking']->fresh()->deposit_paid_at);
        $this->assertSame(1, $invoice->payments()->count());
    }

    public function test_expiry_releases_room_and_cancels_draft_contract(): void
    {
        $fixture = $this->fixture();
        $fixture['booking']->update(['approved_at' => now()->subHours(49)]);
        $this->assertSame(1, app(BookingExpiryService::class)->expireOverdue());
        $this->assertSame('expired', $fixture['booking']->fresh()->status);
        $this->assertSame('cancelled', $fixture['contract']->fresh()->status);
        $this->assertSame('available', $fixture['room']->fresh()->status);
    }

    public function test_expiry_does_not_cancel_paid_booking(): void
    {
        $fixture = $this->fixture('active');
        $fixture['booking']->update(['approved_at' => now()->subHours(49), 'deposit_paid_at' => now()]);
        $this->assertSame(0, app(BookingExpiryService::class)->expireOverdue());
        $this->assertSame('active', $fixture['contract']->fresh()->status);
    }

    public function test_customer_cannot_read_another_customers_booking(): void
    {
        $fixture = $this->fixture();
        $this->actingAs($this->user())->get(route('booking.show', $fixture['booking']))->assertForbidden();
    }

    private function webhook(Invoice $invoice, int $amount, string $reference = 'QA-CALLBACK', int $code = 990002): array
    {
        PaymentOrder::firstOrCreate(['order_code' => $code], ['invoice_id' => $invoice->id, 'amount' => $amount]);
        $data = ['orderCode' => $code, 'amount' => $amount, 'reference' => $reference, 'code' => '00'];

        return ['data' => $data, 'signature' => PayOSSignatureUtils::createSignatureFromObj('qa-checksum', $data)];
    }

    public function test_webhook_retry_creates_only_one_receipt(): void
    {
        $fixture = $this->fixture('active');
        $invoice = $this->monthlyInvoice($fixture['contract']);
        $payload = $this->webhook($invoice, 2500000);
        $this->postJson('/payment/payos_transfer_handler', $payload)->assertOk();
        $this->postJson('/payment/payos_transfer_handler', $payload)->assertOk();
        $this->assertSame(2500000, (int) $invoice->fresh()->paid_amount);
        $this->assertSame(1, $invoice->payments()->count());
    }

    public function test_invalid_signature_cannot_change_invoice(): void
    {
        $fixture = $this->fixture('active');
        $invoice = $this->monthlyInvoice($fixture['contract']);
        $payload = $this->webhook($invoice, 2500000);
        $payload['data']['amount'] = 1;
        $this->postJson('/payment/payos_transfer_handler', $payload)->assertStatus(400);
        $this->assertSame(0, (int) $invoice->fresh()->paid_amount);
        $this->assertSame(0, $invoice->payments()->count());
    }

    public function test_unknown_order_does_not_match_invoice_notes(): void
    {
        $fixture = $this->fixture('active');
        $invoice = $this->monthlyInvoice($fixture['contract']);
        $data = ['orderCode' => 990001, 'amount' => 2500000, 'reference' => 'QA-UNKNOWN', 'code' => '00'];
        $this->postJson('/payment/payos_transfer_handler', ['data' => $data, 'signature' => PayOSSignatureUtils::createSignatureFromObj('qa-checksum', $data)])->assertOk();
        $this->assertSame('unpaid', $invoice->fresh()->status);
    }

    public function test_checkout_rejects_a_different_tenant(): void
    {
        $fixture = $this->fixture('active');
        $invoice = $this->monthlyInvoice($fixture['contract']);
        $this->fakeGateway();
        $this->actingAs($this->user('tenant'))->get(route('payment.checkout', $invoice))->assertForbidden();
        $this->assertSame(0, $invoice->paymentOrders()->count());
    }

    public function test_direct_checkout_cannot_bypass_signing_step(): void
    {
        $fixture = $this->fixture();
        $invoice = app(BookingDepositService::class)->createDepositInvoice($fixture['booking']);
        $this->fakeGateway();
        $this->actingAs($fixture['user'])->get(route('payment.checkout', $invoice))->assertForbidden();
    }

    public function test_checkout_reuses_pending_link(): void
    {
        $fixture = $this->fixture('active');
        $invoice = $this->monthlyInvoice($fixture['contract']);
        $requests = 0;
        $this->fakeGateway(function () use (&$requests) {
            $requests++;
        });
        $this->actingAs($fixture['user'])->getJson(route('payment.checkout', $invoice))->assertOk();
        $this->getJson(route('payment.checkout', $invoice))->assertOk();
        $this->assertSame(1, $requests);
        $this->assertSame(1, $invoice->paymentOrders()->count());
    }

    public function test_payment_return_page_is_safe_for_guests_without_credentials(): void
    {
        config(['services.payos' => []]);
        $this->get('/payment/success')->assertOk()->assertSee('Đang xác minh thanh toán');
        $this->get('/payment/cancel')->assertOk();
    }

    public function test_stale_invoice_objects_do_not_lose_partial_receipts(): void
    {
        $fixture = $this->fixture();
        $service = app(BookingDepositService::class);
        $invoice = $service->createDepositInvoice($fixture['booking']);
        $stale = $invoice->fresh();
        $service->markDepositPaid($invoice, 200000, 'QA-FIRST');
        $service->markDepositPaid($stale, 300000, 'QA-SECOND');
        $this->assertSame(500000, (int) $invoice->fresh()->paid_amount);
        $this->assertSame(2, $invoice->payments()->count());
    }

    public function test_late_receipt_is_preserved_for_review(): void
    {
        $fixture = $this->fixture();
        $service = app(BookingDepositService::class);
        $invoice = $service->createDepositInvoice($fixture['booking']);
        $fixture['booking']->update(['approved_at' => now()->subHours(49)]);
        app(BookingExpiryService::class)->expireOverdue();
        $service->markDepositPaid($invoice, 1000000, 'QA-LATE-REVIEW');
        $this->assertSame(1000000, (int) $invoice->fresh()->paid_amount);
        $this->assertTrue($invoice->payments()->firstOrFail()->requires_review);
        $this->assertSame('cancelled', $fixture['contract']->fresh()->status);
        $this->assertNull($fixture['booking']->fresh()->deposit_paid_at);
    }

    private function invoiceUpdate(array $overrides = []): array
    {
        return array_merge([
            'electricity_old' => 0, 'electricity_new' => 0, 'water_old' => 0, 'water_new' => 0,
            'electricity_fee' => 0, 'water_fee' => 0, 'room_fee' => 2500000, 'service_fee' => 0,
            'paid_amount' => 500000, 'notes' => 'QA receipt',
        ], $overrides);
    }

    public function test_admin_receipt_is_recorded_once_and_balance_is_derived(): void
    {
        $fixture = $this->fixture('active');
        $invoice = $this->monthlyInvoice($fixture['contract']);
        $this->actingAs($this->user('admin'))->put(route('admin.invoices.update', $invoice), $this->invoiceUpdate())->assertSessionHasNoErrors();
        $this->put(route('admin.invoices.update', $invoice), $this->invoiceUpdate())->assertSessionHasNoErrors();
        $this->assertSame('partial', $invoice->fresh()->status);
        $this->assertSame(2000000, (int) $invoice->fresh()->debt);
        $this->assertSame(1, $invoice->payments()->count());
        $this->assertSame(500000, (int) $invoice->payments()->sum('amount'));
    }

    public function test_admin_cannot_reduce_already_received_money(): void
    {
        $fixture = $this->fixture('active');
        $invoice = $this->monthlyInvoice($fixture['contract'], ['paid_amount' => 500000, 'debt' => 2000000]);
        $this->actingAs($this->user('admin'))->put(route('admin.invoices.update', $invoice), $this->invoiceUpdate(['paid_amount' => 0]))->assertSessionHasErrors('paid_amount');
        $this->assertSame(500000, (int) $invoice->fresh()->paid_amount);
    }

    public function test_deposit_cannot_be_zeroed_by_monthly_invoice_editor(): void
    {
        $fixture = $this->fixture();
        $invoice = app(BookingDepositService::class)->createDepositInvoice($fixture['booking']);
        $this->actingAs($this->user('admin'))->put(route('admin.invoices.update', $invoice), $this->invoiceUpdate(['room_fee' => 0, 'paid_amount' => 0]))->assertSessionHasErrors('invoice');
        $this->assertSame(1000000, (int) $invoice->fresh()->total);
    }

    public function test_delete_actions_preserve_rental_history(): void
    {
        $fixture = $this->fixture('active');
        $invoice = $this->monthlyInvoice($fixture['contract']);
        $this->actingAs($this->user('admin'));
        foreach (['houses' => $fixture['house']->id, 'rooms' => $fixture['room']->id, 'tenants' => $fixture['tenant']->id, 'contracts' => $fixture['contract']->id, 'invoices' => $invoice->id] as $resource => $id) {
            $this->delete('/admin/'.$resource.'/'.$id)->assertSessionHasErrors('history');
        }
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id]);
        $this->assertDatabaseHas('contracts', ['id' => $fixture['contract']->id]);
    }

    public function test_private_documents_require_ownership_and_are_not_public(): void
    {
        Storage::fake('private');
        Storage::fake('public');
        $fixture = $this->fixture();
        $this->actingAs($fixture['user'])->post(route('booking.documents.update', $fixture['booking']), [
            'cccd_front' => UploadedFile::fake()->image('front.png'),
            'cccd_back' => UploadedFile::fake()->image('back.png'),
        ])->assertSessionHasNoErrors();
        $tenant = $fixture['tenant']->fresh();
        Storage::disk('private')->assertExists($tenant->cccd_front_path);
        Storage::disk('public')->assertMissing($tenant->cccd_front_path);
        $this->get(route('rental-documents.show', [$tenant, 'front']))->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->actingAs($this->user())->get(route('rental-documents.show', [$tenant, 'front']))->assertForbidden();
        $this->actingAs($this->user('admin'))->get(route('rental-documents.show', [$tenant, 'front']))->assertOk();
    }

    public function test_old_public_documents_are_moved_without_changing_paths(): void
    {
        Storage::fake('private');
        Storage::fake('public');
        $path = 'tenants/cccd/42/legacy.png';
        Storage::disk('public')->put($path, 'synthetic legacy document');
        $this->artisan('documents:privatize')->assertSuccessful();
        Storage::disk('public')->assertMissing($path);
        $this->assertSame('synthetic legacy document', Storage::disk('private')->get($path));
        $this->artisan('documents:privatize')->assertSuccessful();
    }

    public function test_signed_documents_cannot_be_replaced(): void
    {
        $fixture = $this->fixture();
        $fixture['contract']->update(['signed_at' => now(), 'signature_path' => 'qa/signature.png']);
        $this->actingAs($fixture['user'])->post(route('booking.documents.update', $fixture['booking']), [
            'cccd_front' => UploadedFile::fake()->image('replacement.png'),
            'cccd_back' => UploadedFile::fake()->image('replacement-back.png'),
        ])->assertForbidden();
    }

    public function test_admin_form_pages_render_after_changes(): void
    {
        $fixture = $this->fixture('active');
        $invoice = $this->monthlyInvoice($fixture['contract']);
        $this->withoutExceptionHandling();
        $this->actingAs($this->user('admin'))->get(route('admin.invoices.edit', $invoice))->assertOk();
        $this->get(route('admin.contracts.edit', $fixture['contract']))->assertOk();
        $this->get(route('admin.rooms.edit', $fixture['room']))->assertOk();
    }

    private function prepareSigning(array $fixture): void
    {
        Storage::fake('private');
        $fixture['tenant']->update([
            'cccd_front_path' => 'tenants/cccd/'.$fixture['tenant']->id.'/front.png',
            'cccd_back_path' => 'tenants/cccd/'.$fixture['tenant']->id.'/back.png',
        ]);
    }

    public function test_zero_deposit_contract_activates_after_signing_without_a_payment(): void
    {
        $fixture = $this->fixture();
        $this->prepareSigning($fixture);
        $fixture['contract']->update(['deposit' => 0]);
        $fixture['booking']->update(['deposit_amount' => 0]);
        $this->actingAs($fixture['user'])->post(route('booking.sign.store', $fixture['booking']), [
            'signature_data' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            'agreed' => true,
        ])->assertRedirect(route('tenant.dashboard'));
        $this->assertSame('active', $fixture['contract']->fresh()->status);
        $this->assertSame('rented', $fixture['room']->fresh()->status);
        $this->assertNotNull($fixture['booking']->fresh()->deposit_paid_at);
        $invoice = $fixture['contract']->invoices()->where('type', 'deposit')->firstOrFail();
        $this->assertTrue($invoice->is_paid);
        $this->assertSame(0, $invoice->payments()->count());
        Storage::disk('private')->assertExists($fixture['contract']->fresh()->signature_path);
    }

    public function test_invalid_png_cannot_sign_a_contract(): void
    {
        $fixture = $this->fixture();
        $this->prepareSigning($fixture);
        $this->actingAs($fixture['user'])->post(route('booking.sign.store', $fixture['booking']), [
            'signature_data' => 'data:image/png;base64,'.base64_encode('This is not an image.'),
            'agreed' => true,
        ])->assertSessionHasErrors('signature_data');
        $this->assertNull($fixture['contract']->fresh()->signed_at);
        $this->assertSame([], Storage::disk('private')->allFiles());
    }

    public function test_signatures_are_only_visible_to_the_owner_and_admin(): void
    {
        Storage::fake('private');
        $fixture = $this->fixture();
        $path = 'contracts/signatures/'.$fixture['contract']->id.'/signature.png';
        Storage::disk('private')->put($path, 'synthetic signature');
        $fixture['contract']->update(['signature_path' => $path, 'signed_at' => now()]);
        $url = route('rental-documents.signature', $fixture['contract']);
        $this->get($url)->assertRedirect(route('login'));
        $this->actingAs($fixture['user'])->get($url)->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->actingAs($this->user())->get($url)->assertForbidden();
        $this->actingAs($this->user('admin'))->get($url)->assertOk();
    }

    public function test_admin_cannot_activate_draft_or_cancelled_contracts_directly(): void
    {
        $admin = $this->user('admin');
        foreach (['draft', 'cancelled'] as $status) {
            $fixture = $this->fixture($status);
            $this->actingAs($admin)->put(route('admin.contracts.update', $fixture['contract']), ['status' => 'active'])
                ->assertSessionHasErrors('status');
            $this->assertSame($status, $fixture['contract']->fresh()->status);
        }
    }

    public function test_refunded_contract_cannot_be_reactivated(): void
    {
        $fixture = $this->fixture('terminated');
        $fixture['contract']->update(['deposit_refund' => 1000000]);
        $fixture['room']->update(['status' => 'available']);
        $this->actingAs($this->user('admin'))->put(route('admin.contracts.update', $fixture['contract']), ['status' => 'active'])
            ->assertSessionHasErrors('status');
        $this->assertSame('terminated', $fixture['contract']->fresh()->status);
        $this->assertSame('available', $fixture['room']->fresh()->status);
    }

    public function test_reactivation_cannot_record_a_refund_at_the_same_time(): void
    {
        $fixture = $this->fixture('terminated');
        $fixture['room']->update(['status' => 'available']);
        $this->actingAs($this->user('admin'))->put(route('admin.contracts.update', $fixture['contract']), [
            'status' => 'active', 'deposit_refund' => 1000000,
        ])->assertSessionHasErrors('status');
        $this->assertSame('terminated', $fixture['contract']->fresh()->status);
        $this->assertSame(0, (int) $fixture['contract']->fresh()->deposit_refund);
    }

    public function test_zero_deposit_signing_cannot_activate_an_unavailable_room(): void
    {
        $fixture = $this->fixture();
        $this->prepareSigning($fixture);
        $fixture['contract']->update(['deposit' => 0]);
        $fixture['booking']->update(['deposit_amount' => 0]);
        $fixture['house']->update(['is_active' => false]);
        $this->actingAs($fixture['user'])->post(route('booking.sign.store', $fixture['booking']), [
            'signature_data' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            'agreed' => true,
        ])->assertSessionHasErrors('room_id');
        $this->assertSame('draft', $fixture['contract']->fresh()->status);
        $this->assertNull($fixture['contract']->fresh()->signed_at);
        $this->assertNull($fixture['booking']->fresh()->deposit_paid_at);
        $this->assertSame([], Storage::disk('private')->allFiles());
    }

    private function transferData(Room $room): array
    {
        return [
            'new_room_id' => $room->id, 'transfer_date' => today()->toDateString(),
            'new_monthly_price' => 3000000, 'deposit_transfer' => 1500000, 'extra_fee' => 100000,
        ];
    }

    public function test_transfer_cannot_take_a_room_with_a_competing_contract(): void
    {
        $fixture = $this->fixture('active');
        $target = $this->fixture('active');
        $target['room']->update(['status' => 'available']);
        $this->actingAs($this->user('admin'))->post(route('admin.contracts.transfer', $fixture['contract']), $this->transferData($target['room']))
            ->assertSessionHasErrors('room_id');
        $this->assertSame('active', $fixture['contract']->fresh()->status);
        $this->assertSame(1, $target['room']->contracts()->count());
        $this->assertSame(0, $fixture['contract']->invoices()->count());
    }

    public function test_transfer_rejects_a_room_without_enough_capacity(): void
    {
        $fixture = $this->fixture('active');
        $fixture['contract']->update(['occupants' => 2]);
        $room = $fixture['room']->replicate();
        $room->fill(['name' => 'QA small room', 'status' => 'available', 'max_occupants' => 1])->save();
        $this->actingAs($this->user('admin'))->post(route('admin.contracts.transfer', $fixture['contract']), $this->transferData($room))
            ->assertSessionHasErrors('new_room_id');
        $this->assertSame('active', $fixture['contract']->fresh()->status);
        $this->assertSame(0, $room->contracts()->count());
    }

    public function test_successful_transfer_preserves_old_contract_and_syncs_both_rooms(): void
    {
        $fixture = $this->fixture('active');
        $room = $fixture['room']->replicate();
        $room->fill(['name' => 'QA transfer room', 'status' => 'available'])->save();
        $this->actingAs($this->user('admin'))->post(route('admin.contracts.transfer', $fixture['contract']), $this->transferData($room))
            ->assertRedirect(route('admin.contracts.index'))->assertSessionHasNoErrors();
        $this->assertSame('terminated', $fixture['contract']->fresh()->status);
        $this->assertSame('available', $fixture['room']->fresh()->status);
        $this->assertSame('rented', $room->fresh()->status);
        $newContract = $room->contracts()->firstOrFail();
        $this->assertSame('active', $newContract->status);
        $this->assertSame($fixture['tenant']->id, $newContract->tenant_id);
        $this->assertSame(600000, (int) $newContract->invoices()->sum('debt'));
        $this->assertSame(500000, (int) $newContract->invoices()->deposit()->firstOrFail()->debt);
        $this->assertSame(100000, (int) $newContract->invoices()->where('type', 'adjustment')->firstOrFail()->total);
    }

    public function test_admin_can_see_late_payment_requiring_review(): void
    {
        $fixture = $this->fixture();
        $service = app(BookingDepositService::class);
        $invoice = $service->createDepositInvoice($fixture['booking']);
        $fixture['booking']->update(['approved_at' => now()->subHours(49)]);
        app(BookingExpiryService::class)->expireOverdue();
        $service->markDepositPaid($invoice, 1000000, 'QA-REVIEW-VISIBLE');
        $this->actingAs($this->user('admin'))->get(route('admin.invoices.show', $invoice))
            ->assertOk()->assertSee('Cần đối soát')->assertSee('QA-REVIEW-VISIBLE')->assertSee('Lịch sử thu tiền');
    }
}
