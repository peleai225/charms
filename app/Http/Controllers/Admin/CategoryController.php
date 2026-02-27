<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('parent', 'children')
            ->withCount('products')
            ->ordered()
            ->get();

        // Organiser en arbre
        $tree = $categories->whereNull('parent_id');

        return view('admin.categories.index', compact('categories', 'tree'));
    }

    public function create()
    {
        $categories = Category::whereNull('parent_id')->ordered()->get();
        return view('admin.categories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.categories.index', ['open_modal' => 'create'])
                ->withInput()
                ->withErrors($validator);
        }

        $validated = $validator->validated();

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');

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
        $categories = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->ordered()
            ->get();

        return view('admin.categories.edit', compact('category', 'categories'));
    }

    public function update(Request $request, Category $category)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'order' => 'nullable|integer|min:0',
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
