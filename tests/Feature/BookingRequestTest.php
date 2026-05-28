<?php

namespace Tests\Feature;

use App\Models\BookingRequest;
use App\Models\Room;
use App\Models\User;
use App\Models\Contract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BookingRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed the roles and users
        $this->seed();
    }

    public function test_customer_can_submit_booking_request()
    {
        $customer = User::where('email', 'customer@demo.com')->first();
        $room = Room::where('status', 'available')->first();

        $response = $this->actingAs($customer)->post(route('booking.store'), [
            'room_id' => $room->id,
            'cccd' => '123456789012',
            'phone' => '0987654321',
            'desired_move_in_date' => now()->addDays(5)->toDateString(),
            'desired_occupants' => 1,
            'desired_lease_months' => 12,
            'customer_note' => 'Tôi muốn phòng sạch sẽ.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('booking_requests', [
            'user_id' => $customer->id,
            'room_id' => $room->id,
            'status' => 'pending',
            'cccd' => '123456789012',
        ]);
    }

    public function test_admin_can_approve_booking_request()
    {
        $admin = User::where('email', 'admin@demo.com')->first();
        $customer = User::where('email', 'customer@demo.com')->first();
        $room = Room::where('status', 'available')->first();

        // Create a booking request first
        $booking = BookingRequest::create([
            'user_id' => $customer->id,
            'room_id' => $room->id,
            'status' => 'pending',
            'cccd' => '123456789012',
            'phone' => '0987654321',
            'desired_move_in_date' => now()->addDays(5)->toDateString(),
            'desired_occupants' => 1,
            'desired_lease_months' => 12,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.booking-requests.approve', $booking), [
            'deposit_amount' => 2000000,
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(5)->addMonths(12)->toDateString(),
            'admin_note' => 'Đã duyệt yêu cầu đặt phòng.',
        ]);

        $response->assertRedirect();
        $booking->refresh();

        $this->assertEquals('approved', $booking->status);
        $this->assertNotNull($booking->contract_id);
        $this->assertNotNull($booking->tenant_id);

        // Check user role changed from customer to tenant
        $customer->refresh();
        $this->assertTrue($customer->hasRole('tenant'));
        $this->assertFalse($customer->hasRole('customer'));

        // Check contract created in draft status
        $contract = Contract::find($booking->contract_id);
        $this->assertEquals('draft', $contract->status);
        $this->assertEquals(2000000, $contract->deposit);
    }

    public function test_admin_can_reject_booking_request()
    {
        $admin = User::where('email', 'admin@demo.com')->first();
        $customer = User::where('email', 'customer@demo.com')->first();
        $room = Room::where('status', 'available')->first();

        $booking = BookingRequest::create([
            'user_id' => $customer->id,
            'room_id' => $room->id,
            'status' => 'pending',
            'cccd' => '123456789012',
            'phone' => '0987654321',
            'desired_move_in_date' => now()->addDays(5)->toDateString(),
            'desired_occupants' => 1,
            'desired_lease_months' => 12,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.booking-requests.reject', $booking), [
            'rejected_reason' => 'Không đủ điều kiện tối thiểu 10 ký tự.',
        ]);

        $response->assertRedirect();
        $booking->refresh();

        $this->assertEquals('rejected', $booking->status);
        $this->assertEquals('Không đủ điều kiện tối thiểu 10 ký tự.', $booking->rejected_reason);
    }

    public function test_customer_can_upload_documents()
    {
        Storage::fake('public');

        $admin = User::where('email', 'admin@demo.com')->first();
        $customer = User::where('email', 'customer@demo.com')->first();
        $room = Room::where('status', 'available')->first();

        // Create booking and approve it
        $booking = BookingRequest::create([
            'user_id' => $customer->id,
            'room_id' => $room->id,
            'status' => 'pending',
            'cccd' => '123456789012',
            'phone' => '0987654321',
            'desired_move_in_date' => now()->addDays(5)->toDateString(),
            'desired_occupants' => 1,
            'desired_lease_months' => 12,
        ]);

        // Approve
        $this->actingAs($admin)->post(route('admin.booking-requests.approve', $booking), [
            'deposit_amount' => 2000000,
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(5)->addMonths(12)->toDateString(),
        ]);

        $booking->refresh();

        // Acting as customer (now tenant) to upload documents
        $cccdFront = UploadedFile::fake()->image('cccd_front.jpg');
        $cccdBack = UploadedFile::fake()->image('cccd_back.jpg');

        $response = $this->actingAs($customer)->post(route('booking.documents.update', $booking), [
            'cccd_front' => $cccdFront,
            'cccd_back' => $cccdBack,
        ]);

        $response->assertRedirect();

        $booking->refresh();
        $this->assertNotNull($booking->tenant->cccd_front_path);
        $this->assertNotNull($booking->tenant->cccd_back_path);

        Storage::disk('public')->assertExists($booking->tenant->cccd_front_path);
        Storage::disk('public')->assertExists($booking->tenant->cccd_back_path);
    }

    public function test_customer_can_sign_contract()
    {
        Storage::fake('public');

        $admin = User::where('email', 'admin@demo.com')->first();
        $customer = User::where('email', 'customer@demo.com')->first();
        $room = Room::where('status', 'available')->first();

        $booking = BookingRequest::create([
            'user_id' => $customer->id,
            'room_id' => $room->id,
            'status' => 'pending',
            'cccd' => '123456789012',
            'phone' => '0987654321',
            'desired_move_in_date' => now()->addDays(5)->toDateString(),
            'desired_occupants' => 1,
            'desired_lease_months' => 12,
        ]);

        // 1. Approve
        $this->actingAs($admin)->post(route('admin.booking-requests.approve', $booking), [
            'deposit_amount' => 2000000,
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(5)->addMonths(12)->toDateString(),
        ]);

        $booking->refresh();

        // 2. Upload documents
        $cccdFront = UploadedFile::fake()->image('cccd_front.jpg');
        $cccdBack = UploadedFile::fake()->image('cccd_back.jpg');
        $this->actingAs($customer)->post(route('booking.documents.update', $booking), [
            'cccd_front' => $cccdFront,
            'cccd_back' => $cccdBack,
        ]);

        $booking->refresh();

        // 3. Sign contract
        $response = $this->actingAs($customer)->post(route('booking.sign.store', $booking), [
            'signature_data' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            'agreed' => true,
        ]);

        $response->assertRedirect();
        
        $contract = Contract::find($booking->contract_id);
        $this->assertNotNull($contract->signature_path);
        Storage::disk('public')->assertExists($contract->signature_path);
    }
}
