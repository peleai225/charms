<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

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

        return view('admin.reviews.index', compact('reviews'));
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
