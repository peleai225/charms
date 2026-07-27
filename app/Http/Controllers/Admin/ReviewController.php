<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReviewController extends Controller
{
    /**
     * Liste des avis
     */
    public function index(Request $request)
    {
        $query = Review::with(['product', 'customer']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $reviews = $query->latest()->paginate(20)->withQueryString();

        $reviewsData = $reviews->through(fn ($review) => [
            'id'                   => $review->id,
            'product_id'           => $review->product_id,
            'product_name'         => $review->product?->name,
            'author_name'          => $review->author_name,
            'author_email'         => $review->author_email,
            'rating'               => $review->rating,
            'title'                => $review->title,
            'content'              => $review->content,
            'admin_response'       => $review->admin_response,
            'status'               => $review->status,
            'is_verified_purchase' => $review->is_verified_purchase,
            'created_at_fmt'       => $review->created_at->format('d/m/Y H:i'),
        ]);

        $products = Product::orderBy('name')->get(['id', 'name']);

        Inertia::setRootView('layouts.admin-inertia');
        return Inertia::render('Admin/Reviews/Index', [
            'reviews'  => $reviewsData,
            'products' => $products->map(fn ($p) => ['id' => $p->id, 'name' => $p->name]),
            'filters'  => $request->only(['status', 'product_id']),
        ]);
    }

    /**
     * Approuver un avis
     */
    public function approve(Review $review)
    {
        $review->approve();

        return back()->with('success', 'Avis approuvé.');
    }

    /**
     * Rejeter un avis
     */
    public function reject(Review $review)
    {
        $review->reject();

        return back()->with('success', 'Avis rejeté.');
    }

    /**
     * Répondre à un avis
     */
    public function respond(Request $request, Review $review)
    {
        $request->validate([
            'admin_response' => 'required|string|max:1000',
        ]);

        $review->respond($request->admin_response);

        return back()->with('success', 'Réponse enregistrée.');
    }
}
