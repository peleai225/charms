<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        $query = Banner::query();

        if ($request->filled('position')) {
            $query->where('position', $request->position);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $banners = $query->orderBy('position')->orderBy('order')->paginate(20)->withQueryString();
        $positions = Banner::POSITIONS;
        $types = Banner::TYPES;

        return view('admin.banners.index', compact('banners', 'positions', 'types'));
    }

    public function create()
    {
        $positions = Banner::POSITIONS;
        $types = Banner::TYPES;
        return view('admin.banners.create', compact('positions', 'types'));
    }

    public function store(Request $request)
    {
        // L'image n'est pas obligatoire pour les barres d'annonce et les popups
        $isAnnouncementBar = $request->input('position') === 'announcement_bar';
        $isPopup = $request->input('position') === 'popup_center';
        
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'title' => ($isAnnouncementBar ? 'required' : 'nullable') . '|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'image' => (($isAnnouncementBar || $isPopup) ? 'nullable' : 'required') . '|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'link' => 'nullable|string|max:255',
            'button_text' => 'nullable|string|max:100',
            'position' => 'required|string',
            'type' => 'required|string',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('banners', 'public');
        }

        $validated['is_active'] = $request->boolean('is_active');
        $validated['order'] = $request->input('order', 0);
        
        // Auto-set name from title or position if not provided
        if (empty($validated['name'])) {
            $validated['name'] = $validated['title'] ?? Banner::POSITIONS[$validated['position']] ?? 'Bannière';
        }
        
        // Auto-set type for announcement bar and popup
        if ($isAnnouncementBar) {
            $validated['type'] = 'announcement';
        }
        if ($isPopup) {
            $validated['type'] = 'popup';
        }

        Banner::create($validated);

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'Bannière créée avec succès.');
    }

    public function edit(Banner $banner)
    {
        $positions = Banner::POSITIONS;
        $types = Banner::TYPES;
        return view('admin.banners.edit', compact('banner', 'positions', 'types'));
    }

    public function update(Request $request, Banner $banner)
    {
        $isAnnouncementBar = $request->input('position') === 'announcement_bar';
        $isPopup = $request->input('position') === 'popup_center';
        
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'title' => ($isAnnouncementBar ? 'required' : 'nullable') . '|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'link' => 'nullable|string|max:255',
            'button_text' => 'nullable|string|max:100',
            'position' => 'required|string',
            'type' => 'required|string',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image
            if ($banner->image) {
                Storage::disk('public')->delete($banner->image);
            }
            $validated['image'] = $request->file('image')->store('banners', 'public');
        }

        $validated['is_active'] = $request->boolean('is_active');
        
        // Auto-set name from title if not provided
        if (empty($validated['name']) && !empty($validated['title'])) {
            $validated['name'] = $validated['title'];
        }
        
        // Auto-set type for announcement bar and popup
        if ($isAnnouncementBar) {
            $validated['type'] = 'announcement';
        }
        if ($isPopup) {
            $validated['type'] = 'popup';
        }

        $banner->update($validated);

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'Bannière mise à jour.');
    }

    public function destroy(Banner $banner)
    {
        if ($banner->image) {
            Storage::disk('public')->delete($banner->image);
        }

        $banner->delete();

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'Bannière supprimée.');
    }

    public function toggle(Banner $banner)
    {
        $banner->update(['is_active' => !$banner->is_active]);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['is_active' => $banner->is_active]);
        }

        return back()->with('success', 'Bannière ' . ($banner->is_active ? 'activée' : 'désactivée') . '.');
    }
}

