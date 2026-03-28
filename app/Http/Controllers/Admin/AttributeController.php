<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttributeController extends Controller
{
    public function index()
    {
        $attributes = Attribute::with(['values' => fn($q) => $q->orderBy('order')->orderBy('value')])
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        return view('admin.attributes.index', compact('attributes'));
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
        ]);

        // Vérifier doublon
        if ($attribute->values()->where('value', $request->value)->exists()) {
            return back()->with('error', '"' . $request->value . '" existe déjà dans cet attribut.');
        }

        $attribute->values()->create([
            'value'      => $request->value,
            'slug'       => Str::slug($request->value),
            'color_code' => $request->color_code,
            'order'      => $attribute->values()->max('order') + 1,
        ]);

        return back()->with('success', '"' . $request->value . '" ajouté à ' . $attribute->name . '.');
    }

    public function destroyValue(Attribute $attribute, AttributeValue $value)
    {
        $value->delete();
        return back()->with('success', 'Valeur supprimée.');
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
