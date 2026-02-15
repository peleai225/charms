<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    // Types de mouvements (doivent correspondre à l'ENUM de la migration)
    const TYPE_PURCHASE = 'purchase';         // Achat fournisseur (entrée)
    const TYPE_SALE = 'sale';                // Vente (sortie)
    const TYPE_RETURN_IN = 'return_in';      // Retour client (entrée) — annulation, etc.
    const TYPE_RETURN_OUT = 'return_out';    // Retour fournisseur (sortie)
    const TYPE_ADJUSTMENT_IN = 'adjustment_in';
    const TYPE_ADJUSTMENT_OUT = 'adjustment_out';
    const TYPE_TRANSFER_IN = 'transfer_in';
    const TYPE_TRANSFER_OUT = 'transfer_out';
    const TYPE_LOSS = 'loss';
    const TYPE_INVENTORY = 'inventory';
    // Alias pour compatibilité
    const TYPE_RETURN = 'return_in';

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
        return $query->whereIn('type', ['purchase', 'return_in', 'adjustment_in', 'transfer_in']);
    }

    /**
     * Scope sorties
     */
    public function scopeExits($query)
    {
        return $query->whereIn('type', ['sale', 'return_out', 'adjustment_out', 'transfer_out', 'loss']);
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
            self::TYPE_PURCHASE => 'Achat',
            self::TYPE_SALE => 'Vente',
            self::TYPE_RETURN_IN => 'Retour client',
            self::TYPE_RETURN_OUT => 'Retour fournisseur',
            self::TYPE_ADJUSTMENT_IN => 'Ajustement +',
            self::TYPE_ADJUSTMENT_OUT => 'Ajustement -',
            self::TYPE_TRANSFER_IN => 'Transfert entrant',
            self::TYPE_TRANSFER_OUT => 'Transfert sortant',
            self::TYPE_LOSS => 'Perte',
            self::TYPE_INVENTORY => 'Inventaire',
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
        return in_array($this->type, [self::TYPE_PURCHASE, self::TYPE_RETURN_IN, self::TYPE_ADJUSTMENT_IN, self::TYPE_TRANSFER_IN]);
    }

    /**
     * Est-ce une sortie de stock ?
     */
    public function isExit(): bool
    {
        return in_array($this->type, [self::TYPE_SALE, self::TYPE_RETURN_OUT, self::TYPE_ADJUSTMENT_OUT, self::TYPE_TRANSFER_OUT, self::TYPE_LOSS]);
    }
}
