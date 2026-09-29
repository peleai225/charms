<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index()
    {
        Inertia::setRootView('layouts.admin-inertia');

        $categories = Category::with('children.children')
            ->withCount('products')
            ->ordered()
            ->get();

        $tree = $categories->whereNull('parent_id')->values();

        $mapCat = fn($c) => [
            'id'                  => $c->id,
            'name'                => $c->name,
            'slug'                => $c->slug,
            'image'               => $c->image,
            'is_active'           => $c->is_active,
            'is_featured'         => $c->is_featured,
            'parent_id'           => $c->parent_id,
            'order'               => $c->order,
            'products_count'      => $c->products_count,
            'description'         => $c->description,
            'meta_title'          => $c->meta_title,
            'meta_description'    => $c->meta_description,
            'bulk_pricing_rules'  => $c->bulk_pricing_rules,
        ];

        $treeData = $tree->map(function ($cat) use ($mapCat) {
            $data = $mapCat($cat);
            $data['children'] = ($cat->children ?? collect())->map(function ($child) use ($mapCat) {
                $c = $mapCat($child);
                $c['children'] = ($child->children ?? collect())->map($mapCat)->values()->all();
                return $c;
            })->values()->all();
            return $data;
        })->values();

        $allFlat = $categories->map($mapCat)->values();

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $allFlat,
            'tree'       => $treeData,
        ]);
    }

    public function create()
    {
        // Redirige vers index (création via modal inline)
        return redirect()->route('admin.categories.index');
    }

    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name'                => 'required|string|max:255',
            'description'         => 'nullable|string',
            'parent_id'           => 'nullable|exists:categories,id',
            'image'               => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active'           => 'boolean',
            'is_featured'         => 'boolean',
            'order'               => 'nullable|integer|min:0',
            'meta_title'          => 'nullable|string|max:255',
            'meta_description'    => 'nullable|string|max:500',
            'bulk_pricing_rules'  => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.categories.index', ['open_modal' => 'create'])
                ->withInput()
                ->withErrors($validator);
        }

        $validated = $validator->validated();

        $validated['slug']        = Str::slug($validated['name']);
        $validated['is_active']   = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');

        // Décoder et valider les paliers de tarification en gros
        $validated['bulk_pricing_rules'] = $this->decodeBulkPricingRules($request->input('bulk_pricing_rules'));

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create($validated);

        ActivityLog::logCreated($category, "Catégorie {$category->name} créée");

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Catégorie créée avec succès.');
    }

    public function edit(Category $category)
    {
        // Édition via modal dans Index.vue
        return redirect()->route('admin.categories.index');
    }

    public function update(Request $request, Category $category)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name'                => 'required|string|max:255',
            'description'         => 'nullable|string',
            'parent_id'           => 'nullable|exists:categories,id',
            'image'               => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active'           => 'boolean',
            'is_featured'         => 'boolean',
            'order'               => 'nullable|integer|min:0',
            'meta_title'          => 'nullable|string|max:255',
            'meta_description'    => 'nullable|string|max:500',
            'bulk_pricing_rules'  => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.categories.index', ['open_modal' => 'edit', 'category_id' => $category->id])
                ->withInput()
                ->withErrors($validator);
        }

        $validated = $validator->validated();
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');

        // Décoder et valider les paliers de tarification en gros
        $validated['bulk_pricing_rules'] = $this->decodeBulkPricingRules($request->input('bulk_pricing_rules'));

        // Empêcher de se définir comme son propre parent
        if ($validated['parent_id'] == $category->id) {
            return redirect()
                ->route('admin.categories.index', ['open_modal' => 'edit', 'category_id' => $category->id])
                ->with('error', 'Une catégorie ne peut pas être son propre parent.')
                ->withInput();
        }

        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }

        $oldValues = $category->toArray();
        $category->update($validated);

        ActivityLog::logUpdated($category, $oldValues, "Catégorie {$category->name} modifiée");

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(Category $category)
    {
        // Vérifier s'il y a des produits
        if ($category->products()->exists()) {
            return back()->with('error', 'Impossible de supprimer : cette catégorie contient des produits.');
        }

        // Vérifier s'il y a des sous-catégories
        if ($category->children()->exists()) {
            return back()->with('error', 'Impossible de supprimer : cette catégorie contient des sous-catégories.');
        }

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        ActivityLog::logDeleted($category, "Catégorie {$category->name} supprimée");

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Catégorie supprimée.');
    }

    /**
     * Décode et valide les paliers de tarification en gros depuis un JSON string.
     */
    private function decodeBulkPricingRules(?string $json): ?array
    {
        if (empty($json) || $json === 'null' || $json === '[]') {
            return null;
        }

        $rules = json_decode($json, true);

        if (!is_array($rules)) {
            return null;
        }

        // Filtrer les paliers valides (min_qty >= 2, unit_price >= 0)
        $valid = collect($rules)
            ->filter(fn($r) => !empty($r['min_qty']) && isset($r['unit_price']))
            ->map(fn($r) => [
                'min_qty'    => (int) $r['min_qty'],
                'unit_price' => (int) $r['unit_price'],
            ])
            ->filter(fn($r) => $r['min_qty'] >= 2 && $r['unit_price'] >= 0)
            ->sortBy('min_qty')
            ->values()
            ->all();

        return count($valid) ? $valid : null;
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:categories,id',
            'categories.*.order' => 'required|integer|min:0',
        ]);

        foreach ($request->categories as $item) {
            Category::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['success' => true]);
    }
}
