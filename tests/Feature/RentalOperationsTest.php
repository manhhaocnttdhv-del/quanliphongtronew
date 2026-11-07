<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\ContractSettlement;
use App\Models\Expense;
use App\Models\House;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentResolution;
use App\Models\ReminderDelivery;
use App\Models\Room;
use App\Models\Service;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\RentalReminder;
use App\Services\ContractSettlementService;
use App\Services\FinanceReportService;
use App\Services\InvoicePaymentService;
use App\Services\RentalReminderService;
use App\Services\SpreadsheetExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PharData;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RentalOperationsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 11, 6)->setTime(10, 0));
        Notification::fake();
        Storage::fake('private');
        foreach (['admin', 'tenant', 'customer'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
        $this->admin = $this->user('admin');
        $this->actingAs($this->admin);
    }

    private function user(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function fixture(): array
    {
        $user = $this->user('tenant');
        $tenant = Tenant::create(['user_id' => $user->id]);
        $house = House::create(['name' => 'QA House '.$tenant->id, 'address' => 'QA address', 'is_active' => true]);
        $room = Room::create(['house_id' => $house->id, 'name' => 'P'.$tenant->id, 'price' => 3000000, 'area' => 25, 'max_occupants' => 2, 'status' => 'rented']);
        $contract = Contract::create([
            'tenant_id' => $tenant->id, 'room_id' => $room->id, 'start_date' => '2026-10-01', 'end_date' => '2027-09-30',
            'deposit' => 1000000, 'monthly_price' => 3000000, 'occupants' => 1, 'status' => 'active',
        ]);
        foreach (['electricity' => 4000, 'water' => 20000, 'fixed' => 100000] as $type => $price) {
            $service = Service::create(['name' => $type, 'type' => $type, 'price' => $price, 'is_active' => true]);
            $room->services()->attach($service, ['is_active' => true]);
        }

        return compact('user', 'tenant', 'house', 'room', 'contract');
    }

    private function invoice(Contract $contract, array $data = []): Invoice
    {
        return Invoice::create($data + [
            'contract_id' => $contract->id, 'type' => 'monthly', 'month' => 11, 'year' => 2026,
            'room_fee' => 3000000, 'total' => 3000000, 'paid_amount' => 0, 'debt' => 3000000,
            'electricity_old' => 0, 'electricity_new' => 100, 'water_old' => 0, 'water_new' => 10,
            'status' => 'unpaid', 'due_date' => '2026-11-09',
        ]);
    }

    private function batchRow(Contract $contract): array
    {
        return ['contract_id' => $contract->id, 'selected' => 1, 'electricity_old' => 0, 'electricity_new' => 100, 'water_old' => 0, 'water_new' => 10];
    }

    private function batchPreview(array $rows)
    {
        return $this->post(route('admin.billing.preview'), ['period' => '2026-11', 'due_date' => '2026-12-05', 'rows' => $rows]);
    }

    private function settlementInput(array $data = []): array
    {
        return $data + ['checkout_date' => '2026-11-06', 'electricity_old' => 0, 'electricity_new' => 0,
            'water_old' => 0, 'water_new' => 0, 'damage_fee' => 0, 'other_fee' => 0, 'deposit_held' => 1000000,
            'handover_notes' => 'QA handover: room and keys returned, no damage.'];
    }

    private function settle(Contract $contract, array $data = []): ContractSettlement
    {
        $input = $this->settlementInput($data);
        $service = app(ContractSettlementService::class);

        return $service->finalize($contract, $input, $service->calculate($contract, $input), $this->admin->id);
    }

    public function test_bulk_preview_does_not_write_and_confirm_creates_server_calculated_invoices(): void
    {
        $a = $this->fixture();
        $b = $this->fixture();
        $response = $this->batchPreview([$this->batchRow($a['contract']), $this->batchRow($b['contract'])])->assertOk();
        $this->assertSame(0, Invoice::count());
        $this->post(route('admin.billing.store'), ['token' => $response->viewData('token')])->assertRedirect(route('admin.invoices.index'));
        $this->assertSame(2, Invoice::count());
        $this->assertSame(3700000, (int) $a['contract']->invoices()->firstOrFail()->total);
        $this->post(route('admin.billing.store'), ['token' => $response->viewData('token')])->assertSessionHasErrors('token');
        $this->assertSame(2, Invoice::count());
    }

    public function test_bulk_rejects_stale_prices_without_partial_writes(): void
    {
        $a = $this->fixture();
        $b = $this->fixture();
        $preview = $this->batchPreview([$this->batchRow($a['contract']), $this->batchRow($b['contract'])])->assertOk();
        $b['contract']->update(['monthly_price' => 4000000]);
        $this->post(route('admin.billing.store'), ['token' => $preview->viewData('token')])->assertSessionHasErrors('rows');
        $this->assertSame(0, Invoice::count());
    }

    public function test_bulk_rejects_an_invoice_created_after_preview(): void
    {
        $fixture = $this->fixture();
        $preview = $this->batchPreview([$this->batchRow($fixture['contract'])])->assertOk();
        $this->invoice($fixture['contract']);
        $this->post(route('admin.billing.store'), ['token' => $preview->viewData('token')])->assertSessionHasErrors('rows');
        $this->assertSame(1, Invoice::count());
    }

    public function test_bulk_ignores_disabled_services_and_uses_custom_rates(): void
    {
        $fixture = $this->fixture();
        $electricity = $fixture['room']->services()->where('type', 'electricity')->first();
        $fixture['room']->services()->updateExistingPivot($electricity->id, ['custom_price' => 5000]);
        $fixture['room']->services()->where('type', 'water')->first()->update(['is_active' => false]);
        $fixed = $fixture['room']->services()->where('type', 'fixed')->first();
        $fixture['room']->services()->updateExistingPivot($fixed->id, ['is_active' => false]);
        $response = $this->batchPreview([$this->batchRow($fixture['contract'])])->assertOk();
        $this->assertSame(3500000, $response->viewData('rows')[0]['invoice']['total']);
    }

    public function test_bulk_rejects_negative_usage_and_changed_previous_readings(): void
    {
        $fixture = $this->fixture();
        $this->invoice($fixture['contract'], ['month' => 10, 'electricity_new' => 100]);
        $this->batchPreview([$this->batchRow($fixture['contract'])])->assertSessionHasErrors('rows');
        $row = $this->batchRow($fixture['contract']);
        $row['electricity_old'] = 100;
        $row['electricity_new'] = 99;
        $row['water_old'] = 10;
        $this->batchPreview([$row])->assertSessionHasErrors('electricity_new');
    }

    public function test_bulk_preview_cannot_be_submitted_from_a_different_session(): void
    {
        $fixture = $this->fixture();
        $preview = $this->batchPreview([$this->batchRow($fixture['contract'])])->assertOk();
        session()->flush();
        $this->actingAs($this->admin)->post(route('admin.billing.store'), ['token' => $preview->viewData('token')])->assertSessionHasErrors('token');
        $this->assertSame(0, Invoice::count());
    }

    public function test_returning_to_bulk_draft_preserves_period_branch_readings_and_selection(): void
    {
        $a = $this->fixture();
        $b = $this->fixture();
        $b['room']->update(['house_id' => $a['house']->id]);
        $unselected = $this->batchRow($b['contract']);
        unset($unselected['selected']);
        $unselected['electricity_new'] = 321;
        $preview = $this->post(route('admin.billing.preview'), [
            'period' => '2026-10', 'house_id' => $a['house']->id, 'due_date' => '2026-11-12',
            'rows' => [$this->batchRow($a['contract']), $unselected],
        ])->assertOk();

        $edit = $this->get(route('admin.billing.index', ['draft' => $preview->viewData('token')]))->assertOk();
        $this->assertSame('2026-10', $edit->viewData('period')->format('Y-m'));
        $this->assertSame($a['house']->id, $edit->viewData('houseId'));
        $this->assertSame('2026-11-12', $edit->viewData('dueDate'));
        $this->assertSame(321, $edit->viewData('draftRows')->get($b['contract']->id)['electricity_new']);
        $edit->assertSee('name="rows[1][electricity_new]" value="321"', false);
        $this->assertDoesNotMatchRegularExpression('/name="rows\[1\]\[selected\]"[^>]*checked/', $edit->getContent());
        $this->post(route('admin.billing.store'), ['token' => $preview->viewData('token')])->assertSessionHasNoErrors();
        $this->assertSame(1, Invoice::count());
        $this->assertSame(10, $a['contract']->invoices()->firstOrFail()->month);
    }

    public function test_expired_bulk_draft_cannot_restore_or_create_invoices(): void
    {
        $fixture = $this->fixture();
        $preview = $this->batchPreview([$this->batchRow($fixture['contract'])])->assertOk();
        $token = $preview->viewData('token');
        $this->travel(21)->minutes();
        $this->get(route('admin.billing.index', ['draft' => $token]))->assertOk()->assertViewHas('draft', null);
        $this->post(route('admin.billing.store'), ['token' => $token])->assertSessionHasErrors('token');
        $this->assertSame(0, Invoice::count());
    }

    public function test_reminders_are_deduplicated_and_overdue_reminders_repeat_on_schedule(): void
    {
        $fixture = $this->fixture();
        $invoice = $this->invoice($fixture['contract'], ['due_date' => '2026-11-05']);
        $service = app(RentalReminderService::class);
        $this->assertSame(1, $service->send());
        $this->assertSame(0, $service->send());
        $this->travel(7)->days();
        $this->assertSame(1, $service->send());
        $invoice->update(['paid_amount' => $invoice->total, 'debt' => 0, 'status' => 'paid']);
        $this->travel(7)->days();
        $this->assertSame(0, $service->send());
        $this->assertSame(2, ReminderDelivery::count());
        Notification::assertSentToTimes($fixture['user'], RentalReminder::class, 2);
    }

    public function test_reminders_skip_paid_voided_and_outside_window_invoices(): void
    {
        $a = $this->fixture();
        $b = $this->fixture();
        $c = $this->fixture();
        $this->invoice($a['contract'], ['paid_amount' => 3000000, 'debt' => 0, 'status' => 'paid']);
        $this->invoice($b['contract'], ['voided_at' => now()]);
        $this->invoice($c['contract'], ['due_date' => '2026-12-01']);
        $this->assertSame(0, app(RentalReminderService::class)->send());
        Notification::assertNothingSent();
    }

    public function test_contract_reminders_reach_tenant_and_admin_once(): void
    {
        $fixture = $this->fixture();
        $fixture['contract']->update(['end_date' => '2026-11-15']);
        $service = app(RentalReminderService::class);
        $this->assertSame(2, $service->send());
        $this->assertSame(0, $service->send());
        Notification::assertSentTo([$fixture['user'], $this->admin], RentalReminder::class);
        $fixture['contract']->update(['end_date' => '2027-11-15']);
        $this->assertSame(0, $service->send());
    }

    public function test_reminders_can_be_disabled_and_settings_validate_limits(): void
    {
        $fixture = $this->fixture();
        $this->invoice($fixture['contract']);
        $this->put(route('admin.reminders.update'), ['reminder_before_days' => 3, 'reminder_repeat_days' => 7, 'reminder_contract_days' => 30])->assertSessionHasNoErrors();
        $this->artisan('rentals:send-reminders')->assertSuccessful();
        $this->assertSame(0, ReminderDelivery::count());
        $this->put(route('admin.reminders.update'), ['reminder_before_days' => -1, 'reminder_repeat_days' => 0, 'reminder_contract_days' => 1000])
            ->assertSessionHasErrors(['reminder_before_days', 'reminder_repeat_days', 'reminder_contract_days']);
    }

    private function overpayment(array $fixture): Payment
    {
        $invoice = $this->invoice($fixture['contract']);
        app(InvoicePaymentService::class)->record($invoice, 3200000, 'QA-OVER-'.$invoice->id);

        return $invoice->payments()->firstOrFail();
    }

    public function test_overpayment_refund_preserves_gross_receipt_and_records_net_balance(): void
    {
        $fixture = $this->fixture();
        $payment = $this->overpayment($fixture);
        $this->post(route('admin.payment-reviews.store', $payment), [
            'outcome' => 'refund', 'refund_amount' => 200000, 'reference' => 'QA-REFUND-1',
            'reason' => 'Customer overpaid and the excess was returned.', 'evidence' => UploadedFile::fake()->image('refund.png'),
        ])->assertSessionHasNoErrors();
        $invoice = $payment->invoice->fresh();
        $this->assertSame(3200000, (int) $invoice->paid_amount);
        $this->assertSame(200000, (int) $invoice->refunded_amount);
        $this->assertSame(3000000, $invoice->net_paid_amount);
        $this->assertTrue($invoice->is_paid);
        $this->assertFalse($payment->fresh()->requires_review);
        Storage::disk('private')->assertExists($payment->resolution->evidence_path);
        $this->get(route('admin.payment-reviews.evidence', $payment))->assertOk();
    }

    public function test_refund_rejects_excess_amount_and_removes_uncommitted_evidence(): void
    {
        $fixture = $this->fixture();
        $payment = $this->overpayment($fixture);
        $this->post(route('admin.payment-reviews.store', $payment), [
            'outcome' => 'refund', 'refund_amount' => 300000, 'reference' => 'QA-REFUND-2',
            'reason' => 'QA attempted excessive refund.', 'evidence' => UploadedFile::fake()->image('refund.png'),
        ])->assertSessionHasErrors('refund_amount');
        $this->assertSame(0, PaymentResolution::count());
        $this->assertTrue($payment->fresh()->requires_review);
        $this->assertSame([], Storage::disk('private')->allFiles());
    }

    public function test_refund_requires_evidence_and_cannot_be_recorded_twice(): void
    {
        $fixture = $this->fixture();
        $payment = $this->overpayment($fixture);
        $data = ['outcome' => 'refund', 'refund_amount' => 200000, 'reference' => 'QA-REFUND-3', 'reason' => 'QA refunded excess transfer.'];
        $this->post(route('admin.payment-reviews.store', $payment), $data)->assertSessionHasErrors('evidence');
        $this->post(route('admin.payment-reviews.store', $payment), $data + ['evidence' => UploadedFile::fake()->image('refund.png')])->assertSessionHasNoErrors();
        $this->post(route('admin.payment-reviews.store', $payment), $data + ['evidence' => UploadedFile::fake()->image('refund-again.png')])->assertSessionHasErrors('payment');
        $this->assertSame(1, PaymentResolution::count());
        $this->assertCount(1, Storage::disk('private')->allFiles());
    }

    public function test_late_deposit_refund_does_not_reopen_debt_or_activate_contract(): void
    {
        $fixture = $this->fixture();
        $fixture['contract']->update(['status' => 'cancelled']);
        $fixture['room']->update(['status' => 'available']);
        $invoice = $this->invoice($fixture['contract'], ['type' => 'deposit', 'month' => null, 'year' => null, 'total' => 1000000, 'debt' => 1000000]);
        app(InvoicePaymentService::class)->record($invoice, 1000000, 'QA-LATE-DEPOSIT');
        $payment = $invoice->payments()->firstOrFail();
        $this->post(route('admin.payment-reviews.store', $payment), [
            'outcome' => 'refund', 'refund_amount' => 1000000, 'reference' => 'QA-LATE-REFUND',
            'reason' => 'Booking cancelled; returned late deposit.', 'evidence' => UploadedFile::fake()->image('late.png'),
        ])->assertSessionHasNoErrors();
        $this->assertNotNull($invoice->fresh()->voided_at);
        $this->assertSame(0, (int) $invoice->fresh()->debt);
        $this->assertSame('cancelled', $fixture['contract']->fresh()->status);
        $this->get(route('payment.checkout', $invoice))->assertStatus(409);
    }

    public function test_retaining_a_reviewed_receipt_does_not_invent_another_payment(): void
    {
        $fixture = $this->fixture();
        $payment = $this->overpayment($fixture);
        $this->post(route('admin.payment-reviews.store', $payment), ['outcome' => 'retain', 'reason' => 'QA verified with tenant and retained the receipt.'])->assertSessionHasNoErrors();
        $this->assertSame(1, Payment::count());
        $this->assertSame(0, (int) $payment->resolution->refund_amount);
    }

    public function test_settlement_prorates_unbilled_rent_and_creates_a_refund_obligation(): void
    {
        $fixture = $this->fixture();
        $preview = $this->post(route('admin.settlements.preview', $fixture['contract']), $this->settlementInput())->assertOk();
        $this->assertSame(0, ContractSettlement::count());
        $this->post(route('admin.settlements.store', $fixture['contract']), ['token' => $preview->viewData('token')])->assertSessionHasNoErrors();
        $settlement = $fixture['contract']->settlement()->firstOrFail();
        $this->assertSame(600000, $settlement->breakdown['invoice']['room_fee']);
        $this->assertSame(700000, $settlement->deposit_applied);
        $this->assertSame(300000, $settlement->refund_due);
        $this->assertSame(0, $settlement->cash_due);
        $this->assertSame('terminated', $fixture['contract']->fresh()->status);
        $this->assertSame('available', $fixture['room']->fresh()->status);
        $this->assertSame(0, (int) $fixture['contract']->fresh()->deposit_refund);
    }

    public function test_returning_to_settlement_draft_restores_inputs_only_for_its_contract(): void
    {
        $fixture = $this->fixture();
        $other = $this->fixture();
        $input = $this->settlementInput(['electricity_new' => 83, 'water_new' => 7, 'damage_fee' => 25000]);
        $preview = $this->post(route('admin.settlements.preview', $fixture['contract']), $input)->assertOk();
        $token = $preview->viewData('token');
        $this->get(route('admin.settlements.create', ['contract' => $fixture['contract'], 'draft' => $token]))
            ->assertOk()->assertViewHas('draftInput', $input)->assertSee('value="83"', false)->assertSee($input['handover_notes']);
        $this->get(route('admin.settlements.create', ['contract' => $other['contract'], 'draft' => $token]))
            ->assertOk()->assertViewHas('draftInput', []);
        $this->travel(21)->minutes();
        $this->get(route('admin.settlements.create', ['contract' => $fixture['contract'], 'draft' => $token]))
            ->assertOk()->assertViewHas('draftInput', []);
        $this->post(route('admin.settlements.store', $fixture['contract']), ['token' => $token])->assertSessionHasErrors('token');
        $this->assertSame(0, ContractSettlement::count());
    }

    public function test_settlement_does_not_bill_rent_or_services_twice_and_keeps_remaining_debt(): void
    {
        $fixture = $this->fixture();
        $invoice = $this->invoice($fixture['contract']);
        $settlement = $this->settle($fixture['contract'], ['electricity_old' => 100, 'electricity_new' => 110, 'water_old' => 10, 'water_new' => 12]);
        $this->assertSame(0, $settlement->breakdown['invoice']['room_fee']);
        $this->assertSame(80000, $settlement->breakdown['invoice']['total']);
        $this->assertSame(1000000, (int) $invoice->fresh()->paid_amount);
        $this->assertSame(2080000, $settlement->cash_due);
        $this->actingAs($fixture['user'])->get(route('tenant.invoices.show', $invoice))->assertOk();
    }

    public function test_settlement_rejects_money_changes_after_preview(): void
    {
        $fixture = $this->fixture();
        $invoice = $this->invoice($fixture['contract']);
        $input = $this->settlementInput(['electricity_old' => 100, 'electricity_new' => 100, 'water_old' => 10, 'water_new' => 10]);
        $preview = $this->post(route('admin.settlements.preview', $fixture['contract']), $input)->assertOk();
        app(InvoicePaymentService::class)->record($invoice, 500000, 'QA-RACE-PAYMENT');
        $this->post(route('admin.settlements.store', $fixture['contract']), ['token' => $preview->viewData('token')])->assertSessionHasErrors('contract');
        $this->assertSame(0, ContractSettlement::count());
        $this->assertSame('active', $fixture['contract']->fresh()->status);
        $this->assertSame(1, Invoice::count());
    }

    public function test_settlement_requires_review_completion_and_real_deposit_limits(): void
    {
        $fixture = $this->fixture();
        $this->overpayment($fixture);
        $this->post(route('admin.settlements.preview', $fixture['contract']), $this->settlementInput())->assertSessionHasErrors('contract');
        $other = $this->fixture();
        $this->post(route('admin.settlements.preview', $other['contract']), $this->settlementInput(['deposit_held' => 2000000]))->assertSessionHasErrors('deposit_held');
    }

    public function test_settlement_cannot_be_repeated_or_reactivated(): void
    {
        $fixture = $this->fixture();
        $this->settle($fixture['contract']);
        $this->post(route('admin.settlements.preview', $fixture['contract']), $this->settlementInput())->assertSessionHasErrors('contract');
        $this->put(route('admin.contracts.update', $fixture['contract']), ['status' => 'active'])->assertSessionHasErrors('status');
        $this->assertSame(1, ContractSettlement::count());
    }

    public function test_settlement_refund_requires_confirmation_evidence_and_is_recorded_once(): void
    {
        $fixture = $this->fixture();
        $settlement = $this->settle($fixture['contract']);
        $url = route('admin.settlements.refund', $settlement);
        $this->post($url, ['reference' => 'QA-REFUND'])->assertSessionHasErrors(['evidence', 'confirmed']);
        $this->post($url, ['reference' => 'QA-REFUND', 'confirmed' => 1, 'evidence' => UploadedFile::fake()->image('refund.png')])->assertSessionHasNoErrors();
        $this->assertNotNull($settlement->fresh()->refunded_at);
        $this->assertSame(300000, (int) $fixture['contract']->fresh()->deposit_refund);
        $this->post($url, ['reference' => 'QA-REFUND', 'confirmed' => 1, 'evidence' => UploadedFile::fake()->image('refund2.png')])->assertSessionHasErrors('refund');
        $this->assertCount(1, Storage::disk('private')->allFiles());
    }

    public function test_settled_invoice_charges_cannot_change_but_remaining_debt_can_be_collected(): void
    {
        $fixture = $this->fixture();
        $settlement = $this->settle($fixture['contract'], ['deposit_held' => 0]);
        $invoice = $settlement->invoice;
        $data = $invoice->only(['electricity_old', 'electricity_new', 'water_old', 'water_new', 'electricity_fee', 'water_fee', 'room_fee', 'service_fee']);
        $this->put(route('admin.invoices.update', $invoice), array_merge($data, ['paid_amount' => 0, 'room_fee' => 0]))->assertSessionHasErrors('invoice');
        $this->put(route('admin.invoices.update', $invoice), $data + ['paid_amount' => $invoice->total])->assertSessionHasNoErrors();
        $this->assertTrue($invoice->fresh()->is_paid);
    }

    private function expenseData(array $data = []): array
    {
        return $data + ['submission_token' => (string) Str::uuid(), 'category' => 'repair', 'description' => 'QA repaired plumbing', 'amount' => 200000, 'spent_on' => '2026-11-06'];
    }

    public function test_expense_form_stores_private_receipt_and_blocks_double_submit(): void
    {
        $form = $this->get(route('admin.expenses.create'))->assertOk();
        $data = $this->expenseData(['submission_token' => $form->viewData('token')]);
        $this->post(route('admin.expenses.store'), $data + ['evidence' => UploadedFile::fake()->image('expense.png')])->assertRedirect(route('admin.expenses.index'));
        $expense = Expense::firstOrFail();
        Storage::disk('private')->assertExists($expense->evidence_path);
        $this->post(route('admin.expenses.store'), $data)->assertSessionHasErrors('submission_token');
        $this->assertSame(1, Expense::count());
        $this->get(route('admin.expenses.evidence', $expense))->assertOk();
    }

    public function test_expense_reversal_preserves_history_and_excludes_it_from_reports(): void
    {
        $expense = Expense::create(['submission_key' => Str::uuid(), 'created_by' => $this->admin->id] + $this->expenseData());
        $this->post(route('admin.expenses.void', $expense), ['void_reason' => 'QA duplicate entry corrected.'])->assertSessionHasNoErrors();
        $this->assertNotNull($expense->fresh()->voided_at);
        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'amount' => 200000]);
        $entries = app(FinanceReportService::class)->entries(today()->startOfMonth(), today()->endOfMonth(), null);
        $this->assertSame(0, $entries->count());
        $this->post(route('admin.expenses.void', $expense), ['void_reason' => 'QA duplicate cancellation.'])->assertSessionHasErrors('expense');
    }

    public function test_expenses_reject_invalid_dates_categories_and_negative_values(): void
    {
        $this->post(route('admin.expenses.store'), $this->expenseData(['amount' => -1, 'spent_on' => '2026-12-01', 'category' => 'invalid']))
            ->assertSessionHasErrors(['amount', 'spent_on', 'category']);
        $this->assertSame(0, Expense::count());
    }

    public function test_report_uses_receipt_dates_and_separates_deposits_and_refunds(): void
    {
        $fixture = $this->fixture();
        $invoice = $this->invoice($fixture['contract'], ['month' => 10]);
        Payment::create(['invoice_id' => $invoice->id, 'amount' => 800000, 'method' => 'cash', 'paid_at' => now()]);
        Payment::create(['invoice_id' => $invoice->id, 'amount' => 100000, 'method' => 'other', 'kind' => 'deposit_application', 'paid_at' => now()]);
        $deposit = $this->invoice($fixture['contract'], ['type' => 'deposit', 'month' => null, 'year' => null]);
        Payment::create(['invoice_id' => $deposit->id, 'amount' => 1000000, 'method' => 'transfer', 'paid_at' => now()]);
        Expense::create(['submission_key' => Str::uuid(), 'category' => 'repair', 'description' => 'QA expense', 'amount' => 200000, 'spent_on' => today(), 'created_by' => $this->admin->id]);
        $service = app(FinanceReportService::class);
        $summary = $service->summary($service->entries(today()->startOfMonth(), today()->endOfMonth(), null));
        $this->assertSame(800000, $summary['receipts']);
        $this->assertSame(1000000, $summary['deposit_received']);
        $this->assertSame(700000, $summary['profit']);
        $this->assertSame(1600000, $summary['cash_change']);
    }

    public function test_reports_filter_by_branch_without_including_other_or_common_expenses(): void
    {
        $a = $this->fixture();
        $b = $this->fixture();
        foreach ([$a, $b] as $fixture) {
            $invoice = $this->invoice($fixture['contract']);
            Payment::create(['invoice_id' => $invoice->id, 'amount' => 500000, 'method' => 'cash', 'paid_at' => now()]);
            Expense::create(['submission_key' => Str::uuid(), 'house_id' => $fixture['house']->id, 'category' => 'repair', 'description' => 'QA branch cost', 'amount' => 100000, 'spent_on' => today(), 'created_by' => $this->admin->id]);
        }
        $service = app(FinanceReportService::class);
        $summary = $service->summary($service->entries(today()->startOfMonth(), today()->endOfMonth(), $a['house']->id));
        $this->assertSame(500000, $summary['receipts']);
        $this->assertSame(400000, $summary['profit']);
    }

    public function test_excel_export_is_valid_openxml_and_user_strings_are_never_formulas(): void
    {
        $path = app(SpreadsheetExport::class)->create([['Date', 'Value'], ['=HYPERLINK("https://example.invalid")', 123]]);
        try {
            $archive = new PharData($path);
            $xml = (string) $archive['xl/worksheets/sheet1.xml']->getContent();
            $this->assertStringContainsString('inlineStr', $xml);
            $this->assertStringNotContainsString('<f>', $xml);
            $this->assertNotFalse(simplexml_load_string($xml));
            $this->assertTrue(isset($archive['[Content_Types].xml']));
            unset($archive);
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function test_all_new_admin_pages_render_with_data(): void
    {
        $fixture = $this->fixture();
        $payment = $this->overpayment($fixture);
        foreach (['admin.billing.index', 'admin.reminders.index', 'admin.payment-reviews.index', 'admin.expenses.index', 'admin.expenses.create', 'admin.reports.finance'] as $route) {
            $this->get(route($route))->assertOk();
        }
        $this->get(route('admin.payment-reviews.show', $payment))->assertOk();
        $other = $this->fixture();
        $this->get(route('admin.settlements.create', $other['contract']))->assertOk();
        $settlement = $this->settle($other['contract']);
        $this->get(route('admin.settlements.show', $settlement))->assertOk();
        $this->get(route('admin.reports.finance', ['export' => 'xlsx']))->assertOk()->assertDownload('thu-chi-2026-11.xlsx');
    }

    public function test_tenants_cannot_use_financial_admin_routes_or_read_evidence(): void
    {
        $fixture = $this->fixture();
        $payment = $this->overpayment($fixture);
        $this->actingAs($fixture['user']);
        foreach (['admin.billing.index', 'admin.reminders.index', 'admin.payment-reviews.index', 'admin.expenses.index', 'admin.reports.finance'] as $route) {
            $this->get(route($route))->assertForbidden();
        }
        $this->get(route('admin.payment-reviews.evidence', $payment))->assertForbidden();
        $this->post(route('admin.payment-reviews.store', $payment), ['outcome' => 'retain', 'reason' => 'QA unauthorized action.'])->assertForbidden();
        $this->post(route('admin.settlements.preview', $fixture['contract']), $this->settlementInput())->assertForbidden();
        $this->assertSame(0, PaymentResolution::count());
    }

    public function test_new_late_deposit_after_settlement_can_be_refunded_without_using_reserved_money(): void
    {
        $fixture = $this->fixture();
        $deposit = $this->invoice($fixture['contract'], ['type' => 'deposit', 'month' => null, 'year' => null,
            'total' => 1000000, 'debt' => 0, 'paid_amount' => 1000000, 'status' => 'paid']);
        $settlement = $this->settle($fixture['contract']);
        app(InvoicePaymentService::class)->record($deposit, 200000, 'QA-LATE-AFTER-CHECKOUT');
        $payment = $deposit->payments()->latest('id')->firstOrFail();
        $this->get(route('admin.payment-reviews.show', $payment))->assertOk()->assertViewHas('refundable', 200000);
        $this->post(route('admin.payment-reviews.store', $payment), [
            'outcome' => 'refund', 'refund_amount' => 200000, 'reference' => 'QA-LATE-RETURN',
            'reason' => 'New transfer after checkout returned to tenant.', 'evidence' => UploadedFile::fake()->image('late-refund.png'),
        ])->assertSessionHasNoErrors();
        $this->assertSame(1000000, $deposit->fresh()->net_paid_amount);
        $this->assertSame(300000, $settlement->fresh()->refund_due);
        $this->assertNull($settlement->fresh()->refunded_at);
    }

    public function test_finance_report_subtracts_rent_refunds_but_keeps_deposit_refunds_out_of_profit(): void
    {
        $fixture = $this->fixture();
        $payment = $this->overpayment($fixture);
        $this->post(route('admin.payment-reviews.store', $payment), [
            'outcome' => 'refund', 'refund_amount' => 200000, 'reference' => 'QA-RENT-RETURN',
            'reason' => 'Excess rent returned to customer.', 'evidence' => UploadedFile::fake()->image('refund.png'),
        ])->assertSessionHasNoErrors();
        $other = $this->fixture();
        $settlement = $this->settle($other['contract']);
        $this->post(route('admin.settlements.refund', $settlement), [
            'reference' => 'QA-DEPOSIT-RETURN', 'confirmed' => 1, 'evidence' => UploadedFile::fake()->image('deposit.png'),
        ])->assertSessionHasNoErrors();
        $service = app(FinanceReportService::class);
        $summary = $service->summary($service->entries(today()->startOfMonth(), today()->endOfMonth(), null));
        $this->assertSame(200000, $summary['income_refunds']);
        $this->assertSame(300000, $summary['deposit_refund']);
        $this->assertSame(3700000, $summary['profit']);
        $this->assertSame(2700000, $summary['cash_change']);
    }

    public function test_admin_can_save_a_fully_paid_invoice_after_excess_money_was_refunded(): void
    {
        $fixture = $this->fixture();
        $payment = $this->overpayment($fixture);
        $this->post(route('admin.payment-reviews.store', $payment), [
            'outcome' => 'refund', 'refund_amount' => 200000, 'reference' => 'QA-RENT-RETURN',
            'reason' => 'Excess rent returned to customer.', 'evidence' => UploadedFile::fake()->image('refund.png'),
        ])->assertSessionHasNoErrors();
        $invoice = $payment->invoice->fresh();
        $data = $invoice->only(['electricity_old', 'electricity_new', 'water_old', 'water_new', 'electricity_fee', 'water_fee', 'room_fee', 'service_fee', 'paid_amount']);
        $this->put(route('admin.invoices.update', $invoice), $data)->assertSessionHasNoErrors();
        $this->assertSame(3200000, (int) $invoice->fresh()->paid_amount);
        $this->assertTrue($invoice->fresh()->is_paid);
        $this->assertSame(1, $invoice->payments()->count());
    }

    public function test_settlement_rejects_a_future_invoice_and_invalid_final_meter_reading(): void
    {
        $fixture = $this->fixture();
        $this->invoice($fixture['contract'], ['month' => 12]);
        $this->post(route('admin.settlements.preview', $fixture['contract']), $this->settlementInput())->assertSessionHasErrors('contract');
        $other = $this->fixture();
        $this->post(route('admin.settlements.preview', $other['contract']), $this->settlementInput(['electricity_old' => 100, 'electricity_new' => 99]))->assertSessionHasErrors('electricity_new');
        $this->assertSame(0, ContractSettlement::count());
    }

    public function test_settled_contract_cannot_receive_a_new_monthly_bill_through_old_form(): void
    {
        $fixture = $this->fixture();
        $this->settle($fixture['contract']);
        $this->post(route('admin.invoices.store'), [
            'contract_id' => $fixture['contract']->id, 'month' => 11, 'year' => 2026, 'room_fee' => 3000000,
            'electricity_old' => 0, 'electricity_new' => 0, 'water_old' => 0, 'water_new' => 0,
            'electricity_fee' => 0, 'water_fee' => 0, 'service_fee' => 0, 'due_date' => '2026-11-10',
        ])->assertSessionHasErrors('contract_id');
        $this->assertSame(1, $fixture['contract']->invoices()->count());
        $this->actingAs($fixture['user'])->get(route('tenant.contracts.show', $fixture['contract']))->assertOk()->assertSee('Quyết toán trả phòng')->assertSee('Chờ hoàn cọc');
    }

    public function test_a_settled_contract_cannot_bypass_the_refund_evidence_workflow(): void
    {
        $fixture = $this->fixture();
        $this->settle($fixture['contract']);
        $this->put(route('admin.contracts.update', $fixture['contract']), ['status' => 'terminated', 'deposit_refund' => 300000])->assertSessionHasErrors('deposit_refund');
        $this->assertSame(0, (int) $fixture['contract']->fresh()->deposit_refund);
    }

    private function transfer(Contract $contract, array $data = [])
    {
        $room = $contract->room->replicate();
        $room->fill(['name' => 'QA transfer '.$contract->id, 'status' => 'available'])->save();

        return $this->post(route('admin.contracts.transfer', $contract), $data + [
            'new_room_id' => $room->id, 'transfer_date' => today()->toDateString(),
            'new_monthly_price' => 3000000, 'deposit_transfer' => 1500000, 'extra_fee' => 100000,
        ]);
    }

    public function test_transfer_carries_only_received_deposit_and_separates_new_cash_from_income(): void
    {
        $fixture = $this->fixture();
        $oldDeposit = $this->invoice($fixture['contract'], ['type' => 'deposit', 'month' => null, 'year' => null,
            'total' => 1000000, 'debt' => 0, 'paid_amount' => 1000000, 'status' => 'paid']);
        $this->transfer($fixture['contract'])->assertRedirect(route('admin.contracts.index'))->assertSessionHasNoErrors();
        $oldContract = $fixture['contract']->fresh();
        $newContract = Contract::findOrFail($oldContract->transferred_to_contract_id);
        $deposit = $newContract->invoices()->deposit()->firstOrFail();
        $fee = $newContract->invoices()->where('type', 'adjustment')->firstOrFail();
        $this->assertSame(1000000, $oldContract->deposit_transferred);
        $this->assertSame(500000, (int) $deposit->debt);
        $this->assertSame(100000, (int) $fee->total);
        $this->assertNotNull($oldDeposit->fresh()->voided_at);
        $this->assertSame(0, app(ContractSettlementService::class)->availableDeposit($oldContract));
        $this->assertSame(1000000, app(ContractSettlementService::class)->availableDeposit($newContract));
        app(InvoicePaymentService::class)->record($deposit, 500000, 'QA-TRANSFER-DEPOSIT');
        app(InvoicePaymentService::class)->record($fee, 100000, 'QA-TRANSFER-FEE');
        $this->assertFalse($deposit->payments()->where('kind', 'receipt')->firstOrFail()->requires_review);
        $this->assertSame(1500000, app(ContractSettlementService::class)->availableDeposit($newContract));
        $reports = app(FinanceReportService::class);
        $summary = $reports->summary($reports->entries(today()->startOfMonth(), today()->endOfMonth(), null));
        $this->assertSame(500000, $summary['deposit_received']);
        $this->assertSame(100000, $summary['receipts']);
        $this->assertSame(100000, $summary['profit']);
        $this->assertSame(600000, $summary['cash_change']);
        $this->put(route('admin.contracts.update', $oldContract), ['status' => 'active'])->assertSessionHasErrors('status');
        $this->put(route('admin.contracts.update', $oldContract), ['status' => 'terminated', 'deposit_refund' => 1000000])->assertSessionHasErrors('deposit_refund');
    }

    public function test_transfer_does_not_treat_unpaid_deposit_as_received(): void
    {
        $fixture = $this->fixture();
        $this->invoice($fixture['contract'], ['type' => 'deposit', 'month' => null, 'year' => null,
            'total' => 1000000, 'debt' => 600000, 'paid_amount' => 400000, 'status' => 'partial']);
        $this->transfer($fixture['contract'])->assertRedirect(route('admin.contracts.index'))->assertSessionHasNoErrors();
        $newContract = Contract::findOrFail($fixture['contract']->fresh()->transferred_to_contract_id);
        $this->assertSame(1100000, (int) $newContract->invoices()->deposit()->firstOrFail()->debt);
        $this->assertSame(400000, app(ContractSettlementService::class)->availableDeposit($newContract));
    }

    public function test_transfer_rejects_a_deposit_reduction_or_unresolved_receipt_without_changing_contract(): void
    {
        $fixture = $this->fixture();
        $this->transfer($fixture['contract'], ['deposit_transfer' => 500000])->assertSessionHasErrors('deposit_transfer');
        $this->overpayment($fixture);
        $this->transfer($fixture['contract'])->assertSessionHasErrors('contract');
        $this->assertSame('active', $fixture['contract']->fresh()->status);
        $this->assertNull($fixture['contract']->fresh()->transferred_to_contract_id);
        $this->assertSame(1, Contract::count());
    }

    public function test_late_payment_after_transfer_cannot_refund_deposit_already_carried(): void
    {
        $fixture = $this->fixture();
        $deposit = $this->invoice($fixture['contract'], ['type' => 'deposit', 'month' => null, 'year' => null,
            'total' => 1000000, 'debt' => 0, 'paid_amount' => 1000000, 'status' => 'paid']);
        $this->transfer($fixture['contract'])->assertRedirect(route('admin.contracts.index'))->assertSessionHasNoErrors();
        app(InvoicePaymentService::class)->record($deposit, 200000, 'QA-LATE-AFTER-TRANSFER');
        $payment = $deposit->payments()->firstOrFail();
        $this->get(route('admin.payment-reviews.show', $payment))->assertOk()->assertViewHas('refundable', 200000);
        $this->post(route('admin.payment-reviews.store', $payment), [
            'outcome' => 'refund', 'refund_amount' => 200000, 'reference' => 'QA-RETURN-LATE-TRANSFER',
            'reason' => 'Late transfer returned; original deposit remains on the new contract.', 'evidence' => UploadedFile::fake()->image('late-transfer.png'),
        ])->assertSessionHasNoErrors();
        $this->assertSame(1000000, $deposit->fresh()->net_paid_amount);
    }

    public function test_financial_staff_account_deletion_is_rejected_without_logging_out(): void
    {
        Expense::create(['submission_key' => Str::uuid(), 'category' => 'repair', 'description' => 'QA recorded cost', 'amount' => 10000, 'spent_on' => today(), 'created_by' => $this->admin->id]);
        $this->delete(route('profile.destroy'), ['password' => 'password'])->assertSessionHasErrorsIn('userDeletion', 'password');
        $this->assertAuthenticatedAs($this->admin);
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }
}
