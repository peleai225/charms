<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class OnboardingController extends Controller
{
    /** Liste blanche des visites connues. */
    public const TOURS = [
        'menus',
        'promotions.index',
        'products.index',
        'orders.index',
    ];

    public function markSeen(Request $request): Response
    {
        $validated = $request->validate([
            'tour' => ['required', 'string', Rule::in(self::TOURS)],
        ]);

        $user = $request->user();
        $seen = $user->tours_seen ?? [];

        if (! in_array($validated['tour'], $seen, true)) {
            $seen[] = $validated['tour'];
            $user->tours_seen = $seen;
            $user->save();
        }

        return response()->noContent();
    }
}
