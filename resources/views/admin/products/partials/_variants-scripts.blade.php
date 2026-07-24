@php
$attributesJson = json_encode($attributes->map(function($a) {
    return [
        'id'     => $a->id,
        'name'   => $a->name,
        'type'   => $a->type,
        'values' => $a->values->map(function($v) {
            return [
                'id'         => $v->id,
                'value'      => $v->value,
                'color_code' => $v->color_code ?? null,
            ];
        })->values()->all(),
    ];
})->values()->all());
$productSkuJs = addslashes($product->sku);
$bulkRoute    = route('admin.products.variants.bulk', $product);
$variantsUrl  = url('admin/products/' . $product->id . '/variants');
@endphp
@push('styles')
<script>
function _registerVariantManager() {
    window.Alpine.data('variantManager', function() {
        return {
            /* ── gestion inline des variantes existantes ── */
            saving: {},
            deleting: {},
            editDrawer: {
                open: false,
                saving: false,
                data: {},
            },

            /* ── générateur de combinaisons ── */
            showGenerator: false,
            productSku: '{{ $productSkuJs }}',
            attributes: {!! $attributesJson !!},
            selectedAttrs: [],
            selectedValues: {},
            generatedRows: [],
            bulkSubmitting: false,
            bulkError: null,

            /* ── sélection attributs ── */
            toggleAttr(attrId) {
                const idx = this.selectedAttrs.indexOf(attrId);
                if (idx >= 0) {
                    this.selectedAttrs.splice(idx, 1);
                    delete this.selectedValues[attrId];
                } else {
                    this.selectedAttrs.push(attrId);
                    this.selectedValues[attrId] = [];
                }
                this.generatedRows = [];
            },
            isAttrSelected(attrId) {
                return this.selectedAttrs.includes(attrId);
            },
            toggleValue(attrId, valueId) {
                if (!this.selectedValues[attrId]) this.selectedValues[attrId] = [];
                const idx = this.selectedValues[attrId].indexOf(valueId);
                if (idx >= 0) this.selectedValues[attrId].splice(idx, 1);
                else this.selectedValues[attrId].push(valueId);
                this.generatedRows = [];
            },
            isValueSelected(attrId, valueId) {
                return (this.selectedValues[attrId] || []).includes(valueId);
            },
            attrById(id) {
                return this.attributes.find(a => a.id === id);
            },
            valueById(attrId, valueId) {
                const attr = this.attrById(attrId);
                return attr ? attr.values.find(v => v.id === valueId) : null;
            },

            /* ── génération du produit cartésien ── */
            canGenerate() {
                if (this.selectedAttrs.length === 0) return false;
                return this.selectedAttrs.every(aid => (this.selectedValues[aid] || []).length > 0);
            },
            generate() {
                const pools = this.selectedAttrs.map(aid => {
                    const vals = (this.selectedValues[aid] || []).map(vid => ({
                        attrId: aid,
                        valueId: vid,
                        label: (this.valueById(aid, vid) || {}).value || vid,
                        color: (this.valueById(aid, vid) || {}).color_code || null,
                    }));
                    return vals;
                });

                let combos = [[]];
                for (const pool of pools) {
                    combos = combos.flatMap(c => pool.map(v => [...c, v]));
                }

                const sku = this.productSku.toUpperCase().replace(/[^A-Z0-9]/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
                this.generatedRows = combos.map(combo => {
                    const suffix = combo.map(v => v.label.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 6)).join('-');
                    const attrs = {};
                    combo.forEach(v => { attrs[v.attrId] = v.valueId; });
                    return {
                        label: combo.map(v => v.label).join(' / '),
                        colors: combo.filter(v => v.color).map(v => v.color),
                        sku: sku + '-' + suffix,
                        stock: 0,
                        price: '',
                        purchase_price: '',
                        compare_price: '',
                        barcode: '',
                        weight: '',
                        attrs,
                        remove: false,
                    };
                });
            },
            removeRow(i) {
                this.generatedRows.splice(i, 1);
            },

            /* ── soumission bulk ── */
            async submitBulk() {
                const rows = this.generatedRows.filter(r => !r.remove && r.sku.trim());
                if (!rows.length) return;

                // Vérifier les SKU en double dans la grille elle-même
                const skus = rows.map(r => r.sku.trim().toUpperCase());
                const hasDuplicates = skus.length !== new Set(skus).size;
                if (hasDuplicates) {
                    this.showBulkError('Certaines lignes ont des SKU identiques. Veuillez les corriger avant de continuer.');
                    return;
                }

                this.bulkSubmitting = true;
                this.bulkError = null;
                const csrf = document.querySelector('meta[name=csrf-token]').content;
                const payload = {
                    rows: rows.map(r => ({
                        sku: r.sku,
                        stock_quantity: parseInt(r.stock) || 0,
                        sale_price: r.price !== '' ? parseFloat(r.price) : null,
                        purchase_price: r.purchase_price !== '' ? parseFloat(r.purchase_price) : null,
                        compare_price: r.compare_price !== '' ? parseFloat(r.compare_price) : null,
                        barcode: r.barcode || null,
                        weight: r.weight !== '' ? parseFloat(r.weight) : null,
                        attributes: r.attrs,
                    }))
                };
                try {
                    const res = await fetch('{{ $bulkRoute }}', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify(payload),
                    });
                    const data = await res.json();
                    if (data.success) {
                        window.location.reload();
                    } else {
                        this.showBulkError(data.message || 'Erreur lors de la création');
                    }
                } catch (e) {
                    this.showBulkError('Erreur réseau. Vérifiez votre connexion.');
                }
                this.bulkSubmitting = false;
            },
            showBulkError(msg) {
                this.bulkError = msg;
                setTimeout(() => { this.bulkError = null; }, 8000);
            },

            /* ── drawer d'édition complète ── */
            openEditDrawer(variantData) {
                this.editDrawer.data = Object.assign({}, variantData);
                this.editDrawer.open = true;
            },
            async saveEditDrawer() {
                const d = this.editDrawer.data;
                if (!d.id) return;
                this.editDrawer.saving = true;
                try {
                    // FormData pour supporter l'upload d'image
                    const form = new FormData();
                    form.append('_method', 'PATCH');
                    form.append('purchase_price',        d.purchase_price !== '' && d.purchase_price != null ? d.purchase_price : '');
                    form.append('sale_price',            d.sale_price !== '' && d.sale_price != null ? d.sale_price : '');
                    form.append('compare_price',         d.compare_price !== '' && d.compare_price != null ? d.compare_price : '');
                    form.append('stock_quantity',        d.stock_quantity ?? 0);
                    form.append('stock_alert_threshold', d.stock_alert_threshold !== '' && d.stock_alert_threshold != null ? d.stock_alert_threshold : '');
                    form.append('barcode',               d.barcode || '');
                    form.append('weight',                d.weight !== '' && d.weight != null ? d.weight : '');
                    form.append('is_active',             d.is_active ? '1' : '0');
                    // Image : fichier si nouveau choisi, 'remove' si supprimée
                    const imgInput = document.getElementById('drawer-img-input');
                    if (imgInput && imgInput.files[0]) {
                        form.append('image', imgInput.files[0]);
                    } else if (d.image === null) {
                        form.append('remove_image', '1');
                    }

                    const res = await fetch('{{ $variantsUrl }}/' + d.id, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json',
                        },
                        body: form,
                    });
                    const json = await res.json();
                    if (json.success) {
                        const vid = d.id;
                        // Mettre à jour image dans la ligne
                        const imgEl = document.getElementById('variant-img-' + vid);
                        if (imgEl) {
                            if (json.image) {
                                imgEl.src = '/storage/' + json.image;
                                imgEl.classList.remove('hidden');
                                imgEl.nextElementSibling?.classList.add('hidden');
                            } else {
                                imgEl.classList.add('hidden');
                                imgEl.nextElementSibling?.classList.remove('hidden');
                            }
                        }
                        const stockEl = document.getElementById('stock-badge-' + vid);
                        if (stockEl && json.stock_quantity !== undefined) {
                            const q = json.stock_quantity;
                            stockEl.textContent = q <= 0 ? 'Rupture' : q + ' pcs';
                            stockEl.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold '
                                + (q <= 0 ? 'bg-red-100 text-red-700' : q <= 5 ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700');
                        }
                        const priceEl = document.getElementById('price-badge-' + vid);
                        if (priceEl && json.sale_price !== undefined) {
                            priceEl.textContent = json.sale_price ? Number(json.sale_price).toLocaleString('fr-FR') + ' F CFA' : '— (produit)';
                        }
                        const activeEl = document.getElementById('active-badge-' + vid);
                        if (activeEl && json.is_active !== undefined) {
                            activeEl.textContent = json.is_active ? 'Active' : 'Inactive';
                            activeEl.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold '
                                + (json.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500');
                        }
                        this.editDrawer.open = false;
                    } else {
                        alert(json.message || 'Erreur lors de la sauvegarde');
                    }
                } catch(e) {
                    alert('Erreur réseau');
                } finally {
                    this.editDrawer.saving = false;
                }
            },

            /* ── patch / delete variantes existantes ── */
            async patchVariant(variantId, data) {
                this.saving[variantId] = true;
                try {
                    const res = await fetch('{{ $variantsUrl }}/' + variantId, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'X-HTTP-Method-Override': 'PATCH',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(data),
                    });
                    const json = await res.json();
                    if (json.success) {
                        const stockEl = document.getElementById('stock-badge-' + variantId);
                        if (stockEl && json.stock_quantity !== undefined) {
                            const q = json.stock_quantity;
                            stockEl.textContent = q <= 0 ? 'Rupture' : q + ' pcs';
                            stockEl.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold '
                                + (q <= 0 ? 'bg-red-100 text-red-700' : q <= 5 ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700');
                        }
                        const priceEl = document.getElementById('price-badge-' + variantId);
                        if (priceEl && json.sale_price !== undefined) {
                            priceEl.textContent = json.sale_price ? Number(json.sale_price).toLocaleString('fr-FR') + ' F CFA' : '— (produit)';
                        }
                        const activeEl = document.getElementById('active-badge-' + variantId);
                        if (activeEl && json.is_active !== undefined) {
                            activeEl.textContent = json.is_active ? 'Active' : 'Inactive';
                            activeEl.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold '
                                + (json.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500');
                        }
                    }
                } finally {
                    this.saving[variantId] = false;
                }
            },

            async deleteVariant(variantId, name) {
                if (!confirm('Supprimer la variante « ' + name + ' » ?')) return;
                this.deleting[variantId] = true;
                try {
                    const res = await fetch('{{ $variantsUrl }}/' + variantId, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'X-HTTP-Method-Override': 'DELETE',
                            'Accept': 'application/json',
                        },
                    });
                    if (res.ok) {
                        document.getElementById('variant-row-' + variantId)?.remove();
                        const counter = document.getElementById('variants-count');
                        if (counter) counter.textContent = Math.max(0, parseInt(counter.textContent) - 1) + ' variante(s)';
                    }
                } finally {
                    this.deleting[variantId] = false;
                }
            }
        };
    });
}
// Alpine peut être déjà initialisé (module ES dans <head>) ou pas encore
if (window.Alpine) {
    _registerVariantManager();
} else {
    document.addEventListener('alpine:init', _registerVariantManager);
}
</script>
@endpush
