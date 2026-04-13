<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\House;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with('house')->orderBy('is_pinned', 'desc')->latest()->paginate(15);
        return view('admin.announcements.index', compact('announcements'));
    }

    public function create()
    {
        $houses = House::all();
        return view('admin.announcements.create', compact('houses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'house_id' => 'nullable|exists:houses,id',
            'type' => 'required|in:notice,warning,event',
            'is_pinned' => 'boolean',
        ]);

        $published_at = $request->has('publish_now') ? Carbon::now() : null;

        $validated['is_pinned'] = $request->has('is_pinned') ? true : false;

        Announcement::create($validated + ['published_at' => $published_at]);

        return redirect()->route('admin.announcements.index')->with('success', 'Đã tạo thông báo mới!');
    }

    public function show(Announcement $announcement)
    {
        return view('admin.announcements.show', compact('announcement'));
    }

    public function edit(Announcement $announcement)
    {
        $houses = House::all();
        return view('admin.announcements.edit', compact('announcement', 'houses'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'house_id' => 'nullable|exists:houses,id',
            'type' => 'required|in:notice,warning,event',
        ]);

        $validated['is_pinned'] = $request->has('is_pinned') ? true : false;
        
        if ($request->has('publish_now') && !$announcement->published_at) {
            $validated['published_at'] = Carbon::now();
        }

        $announcement->update($validated);

        return redirect()->route('admin.announcements.index')->with('success', 'Cập nhật thông báo thành công!');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();
        return redirect()->route('admin.announcements.index')->with('success', 'Đã xóa thông báo!');
    }
}
