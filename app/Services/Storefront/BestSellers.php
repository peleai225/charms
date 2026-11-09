<?php

namespace App\Services\Storefront;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Meilleures ventes : produits actifs classés par quantité réellement vendue
 * (commandes payées ou livrées, ni annulées ni remboursées, lignes remboursées déduites).
 */
class BestSellers
{
    public const DAYS = 90;

    /**
     * @return Collection<int, Product> du plus vendu au moins vendu
     */
    public function top(int $limit = 24, int $days = self::DAYS): Collection
    {
        $ids = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereNull('orders.deleted_at')
            ->where('orders.created_at', '>=', now()->subDays($days))
            ->where(fn ($q) => Order::whereCountsAsSale($q, 'orders'))
            ->where('products.status', 'active')
            ->whereNull('products.deleted_at')
            ->groupBy('order_items.product_id')
            ->selectRaw('order_items.product_id, SUM(order_items.quantity - COALESCE(order_items.quantity_refunded, 0)) as sold')
            ->havingRaw('SUM(order_items.quantity - COALESCE(order_items.quantity_refunded, 0)) > 0')
            ->orderByDesc('sold')
            ->orderBy('order_items.product_id')
            ->limit($limit)
            ->pluck('order_items.product_id')
            ->all();

        if ($ids === []) {
            return collect();
        }

        $rank = array_flip($ids);

        return Product::with(['images', 'category', 'variants'])
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn (Product $p) => $rank[$p->id])
            ->values();
    }
}
