<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    // Types de mouvements
    const TYPE_IN = 'in';           // Entrée de stock
    const TYPE_OUT = 'out';         // Sortie de stock
    const TYPE_SALE = 'sale';       // Vente
    const TYPE_RETURN = 'return';   // Retour
    const TYPE_ADJUSTMENT = 'adjustment';  // Ajustement inventaire
    const TYPE_TRANSFER = 'transfer';      // Transfert

    protected $fillable = [
        'product_id',
        'product_variant_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'unit_price',
        'total_price',
        'reference_type',
        'reference_id',
        'batch_number',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope par type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope entrées
     */
    public function scopeEntries($query)
    {
        return $query->whereIn('type', ['in', 'return']);
    }

    /**
     * Scope sorties
     */
    public function scopeExits($query)
    {
        return $query->whereIn('type', ['out', 'sale']);
    }

    /**
     * Créer un mouvement de stock
     */
    public static function createMovement(
        Product $product,
        string $type,
        int $quantity,
        ?ProductVariant $variant = null,
        ?float $unitPrice = null,
        $reference = null,
        ?string $notes = null
    ): self {
        // Récupérer le stock actuel
        if ($variant) {
            $currentStock = $variant->stock_quantity ?? 0;
        } else {
            $currentStock = $product->stock_quantity ?? 0;
        }

        // Calculer le nouveau stock
        $newStock = $currentStock + $quantity;

        // Déterminer le type de référence
        $referenceType = null;
        $referenceId = null;
        if ($reference instanceof Order) {
            $referenceType = Order::class;
            $referenceId = $reference->id;
        } elseif ($reference instanceof Supplier) {
            $referenceType = Supplier::class;
            $referenceId = $reference->id;
        }

        // Créer le mouvement
        $movement = self::create([
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'type' => $type,
            'quantity' => $quantity,
            'stock_before' => $currentStock,
            'stock_after' => $newStock,
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice ? abs($quantity) * $unitPrice : null,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
            'user_id' => auth()->id(),
        ]);

        // Mettre à jour le stock du produit/variant
        if ($variant) {
            $variant->update(['stock_quantity' => $newStock]);
        } else {
            $product->update(['stock_quantity' => $newStock]);
        }

        return $movement;
    }

    /**
     * Obtenir tous les types de mouvements
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_IN => 'Entrée',
            self::TYPE_OUT => 'Sortie',
            self::TYPE_SALE => 'Vente',
            self::TYPE_RETURN => 'Retour',
            self::TYPE_ADJUSTMENT => 'Ajustement',
            self::TYPE_TRANSFER => 'Transfert',
        ];
    }

    /**
     * Obtenir le libellé du type
     */
    public function getTypeLabelAttribute(): string
    {
        return self::getTypes()[$this->type] ?? $this->type;
    }

    /**
     * Est-ce une entrée de stock ?
     */
    public function isEntry(): bool
    {
        return in_array($this->type, [self::TYPE_IN, self::TYPE_RETURN]);
    }

    /**
     * Est-ce une sortie de stock ?
     */
    public function isExit(): bool
    {
        return in_array($this->type, [self::TYPE_OUT, self::TYPE_SALE]);
    }
}
