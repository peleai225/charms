<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Soumettre un avis sur un produit
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'author_name' => 'required|string|max:100',
            'author_email' => 'required|email',
            'content' => 'required|string|min:10|max:2000',
            'title' => 'nullable|string|max:200',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // Vérifier si l'utilisateur a déjà laissé un avis
        $existing = Review::where('product_id', $product->id)
            ->where('author_email', $validated['author_email'])
            ->first();

        if ($existing) {
            return back()->with('error', 'Vous avez déjà laissé un avis sur ce produit.');
        }

        // Vérifier achat vérifié si client connecté
        $customerId = null;
        $orderId = null;
        $isVerified = false;

        if (auth()->check()) {
            $customer = auth()->user()->customer ?? null;
            if ($customer) {
                $customerId = $customer->id;
                $hasPurchased = $customer->orders()
                    ->where('payment_status', 'paid')
                    ->whereHas('items', fn($q) => $q->where('product_id', $product->id))
                    ->exists();
                $isVerified = $hasPurchased;
            }
        }

        Review::create([
            'product_id' => $product->id,
            'customer_id' => $customerId,
            'order_id' => $orderId,
            'author_name' => $validated['author_name'],
            'author_email' => $validated['author_email'],
            'rating' => $validated['rating'],
            'title' => $validated['title'] ?? null,
            'content' => $validated['content'],
            'status' => Review::STATUS_PENDING,
            'is_verified_purchase' => $isVerified,
        ]);

        return back()->with('success', 'Votre avis a été envoyé et sera publié après modération.');
    }
}
