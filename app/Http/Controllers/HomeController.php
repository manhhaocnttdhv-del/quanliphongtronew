<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\House;
use App\Models\Room;
use App\Models\Setting;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'house_id' => ['nullable', 'integer', 'exists:houses,id'],
            'q' => ['nullable', 'string', 'max:100'],
            'min_price' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'max_price' => ['nullable', 'integer', 'min:0', 'max:100000000', Rule::when($request->filled('min_price'), ['gte:min_price'])],
            'occupants' => ['nullable', 'integer', 'between:1,20'],
            'sort' => ['nullable', Rule::in(['newest', 'price_asc', 'price_desc', 'area_desc'])],
        ]);

        $branches = House::query()
            ->where('is_active', true)
            ->withCount([
                'rooms as total_rooms_count',
                'rooms as available_rooms_count' => fn ($query) => $query->available(),
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'address', 'phone', 'description']);

        $catalogStats = Room::query()
            ->available()
            ->inActiveBranch()
            ->selectRaw('COUNT(*) AS available_count, MIN(price) AS starting_price, MAX(area) AS largest_area')
            ->first();

        $highlightRooms = Room::query()
            ->available()
            ->inActiveBranch()
            ->with(['house', 'roomType'])
            ->latest('id')
            ->limit(3)
            ->get();

        $query = Room::query()->available()->inActiveBranch()->with([
            'house',
            'roomType',
            'services' => fn ($serviceQuery) => $serviceQuery->wherePivot('is_active', true),
        ]);

        if ($request->filled('house_id')) {
            $query->where('house_id', $filters['house_id']);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $filters['max_price']);
        }
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if ($request->filled('occupants')) {
            $query->where('max_occupants', '>=', $filters['occupants']);
        }
        if ($request->filled('q')) {
            $term = '%' . trim($filters['q']) . '%';
            $query->where(function ($query) use ($term) {
                $query->where('name', 'like', $term)
                    ->orWhereHas('house', fn ($branch) => $branch->where('name', 'like', $term)->orWhere('address', 'like', $term));
            });
        }

        match ($filters['sort'] ?? 'newest') {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'area_desc' => $query->orderByDesc('area'),
            default => $query->orderByDesc('id'),
        };

        $rooms = $query->orderByDesc('id')->paginate(9)->withQueryString()->fragment('tim-phong');

        // Lấy số Zalo từ cài đặt
        $zaloNumber = Setting::get('zalo_number', '');

        return view('welcome', compact('rooms', 'branches', 'filters', 'zaloNumber', 'catalogStats', 'highlightRooms'));
    }
    public function show($id)
    {
        $room = Room::inActiveBranch()->with(['house', 'roomType', 'services'])->findOrFail($id);
        $zaloNumber = Setting::get('zalo_number', '');
        return view('room_detail', compact('room', 'zaloNumber'));
    }
}
