<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        Inertia::setRootView('layouts.admin-inertia');

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

        return Inertia::render('Admin/Banners/Index', [
            'banners' => $banners->through(fn ($b) => [
                'id' => $b->id,
                'name' => $b->name,
                'title' => $b->title,
                'subtitle' => $b->subtitle,
                'image' => $b->image,
                'link' => $b->link,
                'button_text' => $b->button_text,
                'position' => $b->position,
                'type' => $b->type,
                'order' => $b->order,
                'is_active' => $b->is_active,
                'starts_at' => $b->starts_at?->format('d/m/Y'),
                'ends_at' => $b->ends_at?->format('d/m/Y'),
            ]),
            'positions' => $positions,
            'types' => $types,
            'filters' => $request->only(['position', 'type', 'status']),
        ]);
    }

    public function create()
    {
        Inertia::setRootView('layouts.admin-inertia');

        return Inertia::render('Admin/Banners/Create', [
            'positions' => Banner::selectablePositions(),
            'types' => Banner::TYPES,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateBanner($request);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('banners', 'public');
        }
        if ($request->hasFile('image_mobile')) {
            $validated['image_mobile'] = $request->file('image_mobile')->store('banners', 'public');
        }

        $validated['order'] = $request->input('order', 0);

        if (empty($validated['name'])) {
            $validated['name'] = $validated['title'] ?? Banner::POSITIONS[$validated['position']] ?? 'Bannière';
        }

        Banner::create($validated);
        $this->flushSiteBanners();

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'Bannière créée avec succès.');
    }

    public function edit(Banner $banner)
    {
        Inertia::setRootView('layouts.admin-inertia');

        return Inertia::render('Admin/Banners/Edit', [
            'banner' => [
                'id' => $banner->id,
                'name' => $banner->name,
                'title' => $banner->title,
                'subtitle' => $banner->subtitle,
                'description' => $banner->description,
                'image' => $banner->image,
                'image_mobile' => $banner->image_mobile,
                'link' => $banner->link,
                'button_text' => $banner->button_text,
                'background_color' => $banner->background_color,
                'text_color' => $banner->text_color,
                'position' => $banner->position,
                'type' => $banner->type,
                'order' => $banner->order,
                'is_active' => $banner->is_active,
                'show_countdown' => $banner->show_countdown,
                'starts_at' => $banner->starts_at?->format('Y-m-d\TH:i'),
                'ends_at' => $banner->ends_at?->format('Y-m-d\TH:i'),
            ],
            'positions' => Banner::selectablePositions($banner->position),
            'types' => Banner::TYPES,
        ]);
    }

    public function update(Request $request, Banner $banner)
    {
        $validated = $this->validateBanner($request, $banner);

        if ($request->hasFile('image')) {
            if ($banner->image) {
                Storage::disk('public')->delete($banner->image);
            }
            $validated['image'] = $request->file('image')->store('banners', 'public');
        }

        if ($request->hasFile('image_mobile') || $request->boolean('remove_image_mobile')) {
            if ($banner->image_mobile) {
                Storage::disk('public')->delete($banner->image_mobile);
            }
            $validated['image_mobile'] = $request->hasFile('image_mobile')
                ? $request->file('image_mobile')->store('banners', 'public')
                : null;
        }

        if (empty($validated['name']) && ! empty($validated['title'])) {
            $validated['name'] = $validated['title'];
        }

        $banner->update(array_filter($validated, fn ($v, $k) => $k !== 'name' || $v !== null, ARRAY_FILTER_USE_BOTH));
        $this->flushSiteBanners();

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'Bannière mise à jour.');
    }

    public function destroy(Banner $banner)
    {
        foreach (['image', 'image_mobile'] as $field) {
            if ($banner->$field) {
                Storage::disk('public')->delete($banner->$field);
            }
        }

        $banner->delete();
        $this->flushSiteBanners();

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'Bannière supprimée.');
    }

    public function toggle(Banner $banner)
    {
        $banner->update(['is_active' => ! $banner->is_active]);
        $this->flushSiteBanners();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['is_active' => $banner->is_active]);
        }

        return back()->with('success', 'Bannière '.($banner->is_active ? 'activée' : 'désactivée').'.');
    }

    /**
     * Règles communes à la création et à la modification.
     */
    private function validateBanner(Request $request, ?Banner $banner = null): array
    {
        $position = $request->input('position');
        $isAnnouncementBar = $position === 'announcement_bar';
        $isPopup = $position === 'popup_center';
        // L'image n'est obligatoire qu'à la création, et pas pour les barres d'annonce ni les popups
        $imageRequired = ! $banner && ! $isAnnouncementBar && ! $isPopup;

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'title' => ($isAnnouncementBar ? 'required' : 'nullable').'|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'image' => ($imageRequired ? 'required' : 'nullable').'|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'image_mobile' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'link' => 'nullable|string|max:255',
            'button_text' => 'nullable|string|max:100',
            'background_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'text_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'position' => ['required', 'string', Rule::in(array_keys(Banner::selectablePositions($banner?->position)))],
            'type' => 'required|string',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
            'show_countdown' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at|required_if_accepted:show_countdown',
        ], [
            'ends_at.required_if_accepted' => 'Indiquez la date et l\'heure de fin pour afficher le compte à rebours.',
            'position.in' => 'Cet emplacement n\'est pas affiché sur le site.',
        ]);

        unset($validated['image'], $validated['image_mobile']);

        // Une date de fin sans heure couvre toute la journée
        if (! empty($validated['ends_at']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $validated['ends_at'])) {
            $validated['ends_at'] .= ' 23:59:59';
        }

        $validated['is_active'] = $request->boolean('is_active');
        $validated['show_countdown'] = $request->boolean('show_countdown');

        if ($isAnnouncementBar) {
            $validated['type'] = 'announcement';
        }
        if ($isPopup) {
            $validated['type'] = 'popup';
        }

        return $validated;
    }

    /**
     * La barre d'annonce et le popup sont mis en cache pour tout le site.
     */
    private function flushSiteBanners(): void
    {
        cache()->forget('banners_global');
    }
}
