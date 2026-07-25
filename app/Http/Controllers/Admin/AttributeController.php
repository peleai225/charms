<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AttributeController extends Controller
{
    public function index()
    {
        Inertia::setRootView('layouts.admin-inertia');

        $attributes = Attribute::with(['values' => fn($q) => $q->orderBy('order')->orderBy('value')])
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Attributes/Index', [
            'attributes' => $attributes->map(fn($a) => [
                'id'     => $a->id,
                'name'   => $a->name,
                'slug'   => $a->slug,
                'type'   => $a->type,
                'values' => $a->values->map(fn($v) => [
                    'id'         => $v->id,
                    'value'      => $v->value,
                    'color_code' => $v->color_code,
                ]),
            ]),
        ]);
    }

    public function storeAttribute(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:attributes,name',
            'type' => 'required|in:text,color,size',
        ]);

        Attribute::create([
            'name'          => $request->name,
            'slug'          => Str::slug($request->name),
            'type'          => $request->type,
            'is_filterable' => true,
            'is_visible'    => true,
        ]);

        return back()->with('success', 'Attribut "' . $request->name . '" créé.');
    }

    public function destroyAttribute(Attribute $attribute)
    {
        if ($attribute->values()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer : cet attribut a des valeurs. Supprimez-les d\'abord.');
        }

        $attribute->delete();
        return back()->with('success', 'Attribut supprimé.');
    }

    public function storeValue(Request $request, Attribute $attribute)
    {
        $request->validate([
            'value'      => 'required|string|max:100',
            'color_code' => 'nullable|string|max:20|regex:/^#[0-9A-Fa-f]{3,6}$/',
            'image'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        // Vérifier doublon
        if ($attribute->values()->where('value', $request->value)->exists()) {
            return back()->with('error', '"' . $request->value . '" existe déjà dans cet attribut.');
        }

        // Upload image si fournie
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('attributes', 'public');
        }

        $attribute->values()->create([
            'value'      => $request->value,
            'slug'       => Str::slug($request->value),
            'color_code' => $request->color_code,
            'image'      => $imagePath,
            'order'      => $attribute->values()->max('order') + 1,
        ]);

        return back()->with('success', '"' . $request->value . '" ajouté à ' . $attribute->name . '.');
    }

    public function destroyValue(Attribute $attribute, AttributeValue $value)
    {
        $value->delete();
        return back()->with('success', 'Valeur supprimée.');
    }

    public function updateValue(Request $request, Attribute $attribute, AttributeValue $value)
    {
        $validated = $request->validate([
            'color_code'   => 'nullable|string|max:20|regex:/^#[0-9A-Fa-f]{3,6}$/',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'remove_image' => 'nullable|boolean',
        ]);

        // Supprimer image si demandé
        if ($request->boolean('remove_image')) {
            $value->deleteImage();
        }

        // Uploader nouvelle image
        if ($request->hasFile('image')) {
            // Supprimer ancienne
            if ($value->image) {
                \Storage::disk('public')->delete($value->image);
            }
            $value->image = $request->file('image')->store('attributes', 'public');
        }

        // Mettre à jour color_code
        if ($request->has('color_code')) {
            $value->color_code = $request->color_code;
        }

        $value->save();

        return response()->json([
            'success'   => true,
            'image'     => $value->image,
            'image_url' => $value->image_url,
        ]);
    }

    public function bulkStoreValues(Request $request, Attribute $attribute)
    {
        $request->validate([
            'values' => 'required|string',
        ]);

        $lines  = preg_split('/[\r\n,;]+/', $request->values);
        $added  = 0;
        $skipped = 0;

        foreach ($lines as $line) {
            $val = trim($line);
            if ($val === '') continue;

            if ($attribute->values()->where('value', $val)->exists()) {
                $skipped++;
                continue;
            }

            $attribute->values()->create([
                'value' => $val,
                'slug'  => Str::slug($val),
                'order' => $attribute->values()->max('order') + 1,
            ]);
            $added++;
        }

        $msg = "{$added} valeur(s) ajoutée(s)";
        if ($skipped > 0) $msg .= ", {$skipped} ignorée(s) (déjà existantes)";

        return back()->with('success', $msg . '.');
    }
}
