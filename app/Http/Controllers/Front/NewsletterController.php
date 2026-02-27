<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    /**
     * Inscription à la newsletter
     */
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $key = 'newsletter_subscribers';
            $subscribers = json_decode(Setting::get($key, '[]'), true) ?? [];
            if (!in_array($validated['email'], $subscribers)) {
                $subscribers[] = $validated['email'];
                Setting::set($key, json_encode(array_unique($subscribers)));
            }
        } catch (\Throwable $e) {
            \Log::warning('Newsletter subscription failed', ['email' => $validated['email'], 'error' => $e->getMessage()]);
        }

        return back()->with('success', 'Merci ! Vous êtes inscrit à notre newsletter.');
    }
}
