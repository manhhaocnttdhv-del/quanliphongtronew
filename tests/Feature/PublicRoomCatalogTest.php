<?php

namespace Tests\Feature;

use App\Models\House;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PublicRoomCatalogTest extends TestCase
{
    use DatabaseTransactions;

    private function branch(bool $active = true): House
    {
        return House::create([
            'name' => 'Catalog test branch',
            'address' => 'Vinh, Nghe An',
            'is_active' => $active,
        ]);
    }

    private function room(House $branch, array $attributes = []): Room
    {
        return Room::create(array_merge([
            'house_id' => $branch->id,
            'name' => 'Catalog test room',
            'floor' => 1,
            'price' => 2500000,
            'area' => 25.5,
            'max_occupants' => 2,
            'status' => 'available',
        ], $attributes));
    }

    public function test_catalog_only_lists_available_rooms_at_active_branches(): void
    {
        $active = $this->branch();
        $visible = $this->room($active);
        $this->room($active, ['status' => 'rented']);
        $this->room($this->branch(false));

        $this->get(route('home', ['q' => 'Catalog test']))
            ->assertOk()
            ->assertViewHas('rooms', fn ($rooms) => $rooms->pluck('id')->all() === [$visible->id])
            ->assertViewHas('branches', fn ($branches) => $branches->where('is_active', false)->isEmpty());
    }

    public function test_catalog_combines_branch_budget_capacity_and_sort_filters(): void
    {
        $branch = $this->branch();
        $lower = $this->room($branch, ['price' => 2100000, 'max_occupants' => 3]);
        $higher = $this->room($branch, ['price' => 2800000, 'max_occupants' => 4]);
        $this->room($branch, ['price' => 4000000, 'max_occupants' => 4]);
        $this->room($branch, ['price' => 2000000, 'max_occupants' => 2]);
        $this->room($this->branch(), ['price' => 2000000, 'max_occupants' => 4]);

        $this->get(route('home', [
            'house_id' => $branch->id,
            'max_price' => 3000000,
            'occupants' => 3,
            'sort' => 'price_asc',
        ]))->assertOk()
            ->assertViewHas('rooms', fn ($rooms) => $rooms->pluck('id')->all() === [$lower->id, $higher->id]);
    }

    public function test_catalog_supports_lower_and_upper_budget_bounds(): void
    {
        $branch = $this->branch();
        $this->room($branch, ['price' => 2400000]);
        $lower = $this->room($branch, ['price' => 2500000]);
        $middle = $this->room($branch, ['price' => 3200000]);
        $boundary = $this->room($branch, ['price' => 4000000]);
        $upper = $this->room($branch, ['price' => 4500000]);

        $this->get(route('home', [
            'house_id' => $branch->id,
            'min_price' => 2500000,
            'max_price' => 4000000,
            'sort' => 'price_asc',
        ]))->assertOk()
            ->assertViewHas('rooms', fn ($rooms) => $rooms->pluck('id')->all() === [$lower->id, $middle->id, $boundary->id]);

        $this->get(route('home', [
            'house_id' => $branch->id,
            'min_price' => 4000000,
            'sort' => 'price_asc',
        ]))->assertOk()
            ->assertViewHas('rooms', fn ($rooms) => $rooms->pluck('id')->all() === [$boundary->id, $upper->id]);

        $this->get(route('home', ['min_price' => 4000000, 'max_price' => 2500000]))
            ->assertSessionHasErrors('max_price');
    }

    public function test_inactive_branch_room_cannot_be_viewed_or_booked(): void
    {
        $room = $this->room($this->branch(false));
        $customer = User::factory()->create();

        $this->get(route('room.show', $room))->assertNotFound();
        $this->actingAs($customer)->get(route('booking.create', ['room_id' => $room->id]))
            ->assertRedirect(route('home'));

        $this->actingAs($customer)->post(route('booking.store'), [
            'room_id' => $room->id,
            'cccd' => '123456789012',
            'phone' => '0987654321',
            'desired_move_in_date' => now()->addDays(5)->toDateString(),
            'desired_occupants' => 1,
            'desired_lease_months' => 12,
        ])->assertSessionHasErrors('room_id');

        $this->assertDatabaseMissing('booking_requests', ['room_id' => $room->id]);
    }

    public function test_administrator_manages_rooms_instead_of_submitting_rental_requests(): void
    {
        $room = $this->room($this->branch(), ['images' => ['rooms/catalog-test.jpg']]);
        $admin = User::factory()->create();
        $admin->assignRole(Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']));

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('branches', fn ($branches) => $branches->firstWhere('id', $room->house_id)->available_rooms_count === 1);

        $this->get(route('admin.rooms.index', ['house_id' => $room->house_id]))
            ->assertOk()
            ->assertViewHas('rooms', fn ($rooms) => $rooms->firstWhere('id', $room->id)->max_occupants === 2
                && $rooms->firstWhere('id', $room->id)->images === ['rooms/catalog-test.jpg']);

        $this->actingAs($admin)->get(route('room.show', $room))
            ->assertOk()
            ->assertSee(route('admin.rooms.edit', $room));
        $this->actingAs($admin)->get(route('booking.create', ['room_id' => $room->id]))
            ->assertRedirect(route('admin.rooms.index'));
        $this->actingAs($admin)->post(route('booking.store'), [])->assertForbidden();
        $this->assertDatabaseMissing('booking_requests', ['room_id' => $room->id]);
    }
}
