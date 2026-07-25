# Design Spec : Images de couleurs pour variants de produits

**Date :** 2026-07-25  
**Statut :** Approuvé  
**Auteur :** Claude (brainstorming avec utilisateur)

---

## 🎯 Objectif

Permettre d'associer une image à chaque valeur de couleur dans les attributs produits, afin que :
1. Les variantes générées en masse héritent automatiquement de l'image de leur couleur
2. Le front boutique affiche l'image correspondante quand le client sélectionne une couleur
3. L'admin peut uploader/gérer ces images depuis la page Attributs

**Exemple concret :**
- T-shirt avec 3 couleurs (Rouge, Bleu, Vert) × 3 tailles (S, M, L) = 9 variantes
- Admin upload 3 images (1 par couleur) au lieu de 9
- Client clique "Rouge" → affiche image rouge
- Client change la taille → garde l'image rouge

---

## 🏗️ Architecture

### 1. Base de données

**Migration : `YYYY_MM_DD_add_image_to_attribute_values`**

```php
Schema::table('attribute_values', function (Blueprint $table) {
    $table->string('image')->nullable()->after('color_code');
});
```

**Détails :**
- Colonne `image` : chemin relatif (ex: `attributes/rouge-tshirt.jpg`)
- Nullable : rétrocompatibilité + optionnel pour valeurs non-couleur
- Storage : `storage/app/public/attributes/`
- Pas d'index nécessaire

---

### 2. Modèle Eloquent

**`app/Models/AttributeValue.php`**

Ajouts :

```php
protected $fillable = [
    'attribute_id',
    'value',
    'slug',
    'color_code',
    'image',  // ← Nouveau
    'order',
];

// Accessor pour URL complète
public function getImageUrlAttribute(): ?string
{
    return $this->image ? asset('storage/' . $this->image) : null;
}

// Suppression fichier
public function deleteImage(): void
{
    if ($this->image) {
        Storage::disk('public')->delete($this->image);
        $this->update(['image' => null]);
    }
}
```

**Raison :**
- Accessor cohérent avec `ProductVariant::getImageUrlAttribute()`
- Method centralisée pour éviter duplication dans controllers

---

### 3. Controller Backend

**`app/Http/Controllers/Admin/AttributeController.php`**

#### 3.1 Méthode `storeValue()` (ajout unitaire)

**Modifications :**

```php
public function storeValue(Request $request, Attribute $attribute)
{
    $validated = $request->validate([
        'value'      => 'required|string|max:100',
        'color_code' => 'nullable|string|max:20|regex:/^#[0-9A-Fa-f]{3,6}$/',
        'image'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120', // ← Nouveau
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
        'image'      => $imagePath,  // ← Nouveau
        'order'      => $attribute->values()->max('order') + 1,
    ]);

    return back()->with('success', '"' . $request->value . '" ajouté à ' . $attribute->name . '.');
}
```

#### 3.2 Méthode `updateValue()` (nouvelle)

```php
public function updateValue(Request $request, Attribute $attribute, AttributeValue $value)
{
    $validated = $request->validate([
        'color_code' => 'nullable|string|max:20|regex:/^#[0-9A-Fa-f]{3,6}$/',
        'image'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
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
            Storage::disk('public')->delete($value->image);
        }
        $value->image = $request->file('image')->store('attributes', 'public');
    }

    // Mettre à jour color_code
    if ($request->has('color_code')) {
        $value->color_code = $request->color_code;
    }

    $value->save();

    return response()->json([
        'success' => true,
        'image' => $value->image,
        'image_url' => $value->image_url,
    ]);
}
```

#### 3.3 Route

```php
// routes/web.php (groupe admin.attributes)
Route::patch('attributes/{attribute}/values/{value}', [AttributeController::class, 'updateValue'])
    ->name('attributes.values.update');
```

---

### 4. Interface Admin

**`resources/views/admin/attributes/index.blade.php`**

#### 4.1 Formulaire d'ajout unitaire (lignes ~145-164)

**Modifications :**

```blade
<form method="POST" action="{{ route('admin.attributes.values.store', $attribute) }}"
      enctype="multipart/form-data"  {{-- ← Nouveau --}}
      class="space-y-3"
      x-data="{ imgPreview: null }">  {{-- ← Nouveau --}}
    @csrf
    
    <div class="flex gap-3 items-end flex-wrap">
        {{-- Champ valeur (inchangé) --}}
        <div class="flex-1 min-w-36">
            <label class="block text-[11px] font-medium text-gray-500 mb-1">Nouvelle valeur</label>
            <input type="text" name="value" required
                placeholder="{{ $attribute->type === 'color' ? 'Ex: Rouge' : 'Valeur...' }}"
                class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        {{-- Color picker (inchangé) --}}
        @if($attribute->type === 'color')
        <div>
            <label class="block text-[11px] font-medium text-gray-500 mb-1">Code couleur</label>
            <input type="color" name="color_code" value="#000000"
                class="h-9 w-12 px-1 py-1 border border-gray-200 rounded-lg cursor-pointer">
        </div>
        
        {{-- Image upload (NOUVEAU) --}}
        <div>
            <label class="block text-[11px] font-medium text-gray-500 mb-1">Image</label>
            <div class="flex items-center gap-2">
                <div class="w-9 h-9 rounded-lg border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden cursor-pointer hover:border-blue-300 transition-colors"
                     @click="$refs.imgInput.click()">
                    <img x-show="imgPreview" :src="imgPreview" class="w-full h-full object-cover">
                    <svg x-show="!imgPreview" class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <input x-ref="imgInput" type="file" name="image" accept="image/*" class="hidden"
                       @change="imgPreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
            </div>
        </div>
        @endif
        
        <button type="submit" class="h-9 px-3 bg-green-600 hover:bg-green-700 text-white font-medium text-[12px] rounded-lg transition-colors">
            + Ajouter
        </button>
    </div>
</form>
```

#### 4.2 Affichage des valeurs existantes (lignes ~124-143)

**Modifications :**

```blade
<div class="flex flex-wrap gap-1.5 mb-4">
    @foreach($attribute->values as $val)
    <span class="inline-flex items-center gap-1.5 pl-2 pr-1 py-1 bg-gray-100 rounded text-[12px] group relative"
          x-data="{ 
              editing: false, 
              newColorCode: '{{ $val->color_code }}',
              imgPreview: '{{ $val->image_url }}',
              imgFile: null 
          }">
        
        {{-- Miniature image (NOUVEAU) --}}
        @if($attribute->type === 'color' && $val->image)
            <img :src="imgPreview || '{{ $val->image_url }}'" 
                 class="w-6 h-6 rounded border border-gray-200 object-cover flex-shrink-0"
                 @click="editing = true">
        @endif
        
        {{-- Pastille couleur (existant) --}}
        @if($val->color_code)
            <span class="w-3.5 h-3.5 rounded-full border border-gray-300 flex-shrink-0 cursor-pointer"
                  :style="'background:' + (editing ? newColorCode : '{{ $val->color_code }}')"
                  @click="editing = true"></span>
        @endif
        
        <span class="font-medium text-gray-700">{{ $val->value }}</span>
        
        {{-- Bouton éditer (NOUVEAU) --}}
        @if($attribute->type === 'color')
            <button type="button" @click="editing = !editing"
                class="p-0.5 text-gray-300 hover:text-blue-500 transition-colors opacity-0 group-hover:opacity-100 rounded">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
            </button>
        @endif
        
        {{-- Bouton supprimer (existant) --}}
        <form method="POST" action="{{ route('admin.attributes.values.destroy', [$attribute, $val]) }}" 
              onsubmit="return confirm('Supprimer ?')">
            @csrf @method('DELETE')
            <button type="submit" class="p-0.5 text-gray-300 hover:text-red-500 transition-colors opacity-0 group-hover:opacity-100 rounded">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </form>
        
        {{-- Drawer édition inline (NOUVEAU) --}}
        <div x-show="editing" x-cloak @click.away="editing = false"
             class="absolute top-full left-0 mt-1 z-50 w-72 bg-white border border-gray-200 rounded-lg shadow-lg p-3 space-y-2">
            
            {{-- Color picker --}}
            <div>
                <label class="block text-[10px] font-medium text-gray-500 mb-1">Couleur</label>
                <input type="color" x-model="newColorCode"
                    class="h-8 w-full px-1 py-1 border border-gray-200 rounded cursor-pointer">
            </div>
            
            {{-- Image upload --}}
            <div>
                <label class="block text-[10px] font-medium text-gray-500 mb-1">Image</label>
                <div class="flex items-center gap-2">
                    <div class="w-12 h-12 rounded border-2 border-dashed border-gray-200 flex items-center justify-center overflow-hidden cursor-pointer hover:border-blue-300"
                         @click="$refs.editImg{{ $val->id }}.click()">
                        <img x-show="imgPreview" :src="imgPreview" class="w-full h-full object-cover">
                        <svg x-show="!imgPreview" class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <input x-ref="editImg{{ $val->id }}" type="file" accept="image/*" class="hidden"
                           @change="imgFile = $event.target.files[0]; imgPreview = imgFile ? URL.createObjectURL(imgFile) : '{{ $val->image_url }}'">
                    <div class="flex-1 text-[10px] text-gray-500">
                        <button type="button" @click="imgPreview = null; imgFile = null; $refs.editImg{{ $val->id }}.value = ''"
                            x-show="imgPreview"
                            class="text-red-500 hover:text-red-600">Supprimer</button>
                    </div>
                </div>
            </div>
            
            {{-- Actions --}}
            <div class="flex gap-2 pt-1">
                <button type="button" @click="saveValueEdit({{ $attribute->id }}, {{ $val->id }})"
                    class="flex-1 h-7 px-3 bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-medium rounded">
                    Enregistrer
                </button>
                <button type="button" @click="editing = false"
                    class="h-7 px-3 border border-gray-200 text-[11px] text-gray-600 rounded hover:bg-gray-50">
                    Annuler
                </button>
            </div>
        </div>
    </span>
    @endforeach
</div>

@push('scripts')
<script>
async function saveValueEdit(attributeId, valueId) {
    const form = new FormData();
    form.append('_method', 'PATCH');
    
    // Color code
    const colorInput = document.querySelector(`[x-model="newColorCode"]`);
    if (colorInput) form.append('color_code', colorInput.value);
    
    // Image
    const imgInput = document.querySelector(`[x-ref="editImg${valueId}"]`);
    if (imgInput?.files[0]) {
        form.append('image', imgInput.files[0]);
    } else if (!imgPreview) {
        form.append('remove_image', '1');
    }
    
    try {
        const res = await fetch(`/admin/attributes/${attributeId}/values/${valueId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
            },
            body: form,
        });
        
        if (res.ok) {
            window.location.reload();
        } else {
            alert('Erreur lors de la sauvegarde');
        }
    } catch(e) {
        alert('Erreur réseau');
    }
}
</script>
@endpush
```

---

### 5. Générateur de variants

**`app/Http/Controllers/Admin/ProductController.php::bulkStoreVariants()`**

**Modifications :**

Après la ligne où vous créez la variante :

```php
// Ligne ~570-580 (dans la boucle foreach des rows)
$variant = ProductVariant::create([
    'product_id'     => $product->id,
    'sku'            => $sku,
    'stock_quantity' => $row['stock_quantity'] ?? 0,
    'sale_price'     => $row['sale_price'] ?? null,
    'purchase_price' => $row['purchase_price'] ?? null,
    'compare_price'  => $row['compare_price'] ?? null,
    'barcode'        => $row['barcode'] ?? null,
    'weight'         => $row['weight'] ?? null,
    'is_active'      => true,
]);

// ── NOUVEAU : Assigner l'image de la couleur ──
$pairs = [];
foreach ($row['attributes'] ?? [] as $attrId => $valueId) {
    if (!in_array($attrId, $validAttributeIds)) continue;
    $pairs[] = ['attribute_id' => $attrId, 'attribute_value_id' => $valueId];
    
    // Si c'est un attribut de type 'color', récupérer son image
    $attrValue = AttributeValue::find($valueId);
    if ($attrValue && $attrValue->attribute->type === 'color' && $attrValue->image) {
        $variant->update(['image' => $attrValue->image]);
    }
}

// Lier les attributs (existant)
foreach ($pairs as $pair) {
    DB::table('product_variant_values')->insert([
        'product_variant_id' => $variant->id,
        'attribute_id'       => $pair['attribute_id'],
        'attribute_value_id' => $pair['attribute_value_id'],
    ]);
}
```

**Raison :**
- On copie l'image de `attribute_values.image` → `product_variants.image`
- Les variants restent indépendants (peuvent override manuellement via drawer)
- Si la couleur n'a pas d'image, le variant reste avec `image = null` (fallback produit parent)

---

### 6. Front Boutique

**`resources/views/front/shop/product.blade.php`**

**Modifications dans le script Alpine.js (fin du fichier) :**

```javascript
// Ligne ~300+ (fonction productPage)
function productPage() {
    return {
        // ... variables existantes ...
        
        // MODIFIÉ : selectColor()
        selectColor(colorId, colorName, colorValue, variants) {
            this.selectedColorId = colorId;
            this.selectedColorName = colorName;
            this.availableSizes = variants.map(v => ({
                id: v.id,
                name: v.size_name ?? v.name,
                stock: v.stock,
                price: v.price,
            }));
            
            // ── NOUVEAU : Changer l'image principale si la couleur a une image ──
            const colorVariant = variants[0]; // Prendre la première variante de cette couleur
            if (colorVariant && colorVariant.image) {
                this.currentImage = colorVariant.image_url;
            }
            
            this.selectedSizeId = null;
            this.selectedSizeName = null;
            this.variantStock = null;
            this.currentVariantId = null;
        },
        
        // ... reste du code inchangé ...
    }
}
```

**Données passées depuis le controller :**

Dans `HomeController::product()`, modifier la structure `$variantsByColor` :

```php
// Ligne ~100+ (méthode product)
$variantsByColor = [];
foreach ($product->variants as $variant) {
    foreach ($variant->attributeValues as $attrValue) {
        if ($attrValue->attribute && $attrValue->attribute->type === 'color') {
            $variantsByColor[$attrValue->id][] = [
                'id'         => $variant->id,
                'name'       => $variant->name,
                'size_name'  => $variant->attributeValues->first(fn($v) => $v->attribute->slug !== 'couleur')?->value,
                'stock'      => $variant->stock_quantity,
                'price'      => $variant->sale_price,
                'image'      => $variant->image,           // ← NOUVEAU
                'image_url'  => $variant->image_url,      // ← NOUVEAU
            ];
        }
    }
}
```

---

## 🔍 Gestion des erreurs

### Backend

**Validations :**
- Image : `max:5120` (5 Mo) + `mimes:jpeg,png,jpg,webp`
- Si upload échoue → retour avec message d'erreur flash
- Si storage plein → exception Laravel standard (géré par handler)

**Suppression sécurisée :**
- Vérifier existence du fichier avant `delete()`
- Transaction DB si suppression valeur + image en cascade

### Frontend

**Alpine.js :**
- Preview immédiate (blob URL) avant upload
- Bouton "Annuler" reset l'état
- Message d'erreur si fetch échoue

---

## ✅ Tests manuels

Scénarios à valider :

1. **Admin : Ajouter couleur avec image**
   - Créer nouvelle valeur "Orange" + upload image
   - Vérifier : miniature visible dans la liste

2. **Admin : Éditer couleur existante**
   - Cliquer sur pastille → drawer s'ouvre
   - Changer `color_code` + uploader nouvelle image
   - Vérifier : changements sauvegardés

3. **Admin : Générer variants**
   - Créer produit avec 2 couleurs (Rouge + Bleu avec images) × 2 tailles
   - Générer 4 variantes
   - Vérifier : Rouge-S et Rouge-M ont l'image rouge

4. **Front : Sélection couleur**
   - Page produit avec variants
   - Cliquer couleur Rouge → image change
   - Cliquer couleur Bleu → image change
   - Changer taille → image reste sur la couleur sélectionnée

5. **Edge cases**
   - Couleur sans image → pas d'erreur, fallback produit parent
   - Fichier > 5Mo → message d'erreur
   - Format invalide (.pdf) → rejeté

---

## 📦 Livrables

1. **Migration** : `YYYY_MM_DD_add_image_to_attribute_values.php`
2. **Model** : `app/Models/AttributeValue.php` (fillable + accessors)
3. **Controller** : `app/Http/Controllers/Admin/AttributeController.php` (storeValue, updateValue)
4. **Routes** : `routes/web.php` (PATCH attributes/{id}/values/{id})
5. **View** : `resources/views/admin/attributes/index.blade.php` (formulaire + édition inline)
6. **Logic** : `app/Http/Controllers/Admin/ProductController.php::bulkStoreVariants()` (copie image)
7. **Front** : `resources/views/front/shop/product.blade.php` (switcher image Alpine.js)
8. **Front** : `app/Http/Controllers/Front/HomeController.php::product()` (passer image_url)

---

## 🚀 Évolutions futures (hors scope)

- **Galeries multiples** : 5-10 images par couleur (Approche 3)
- **Drag & drop** : réorganiser ordre des images
- **Optimisation** : génération thumbnails automatique
- **Bulk upload** : ZIP avec images nommées par couleur
- **CDN** : servir images via Cloudflare/S3

---

## 🎨 Conformité Chamse

✅ **UI** : Inter, #2563EB, Lucide icons, inspiré Shopify  
✅ **Code** : SOLID, conventions Laravel, pas de duplication  
✅ **UX** : Inline editing, Alpine.js, états clairs (loading, error, success)  
✅ **Sécurité** : Validation fichiers, CSRF, Storage isolé  
✅ **Performance** : Pas de N+1, eager loading existant suffit  

---

**Fin du design. Prêt pour implémentation.**
