<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\PushSubscription;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|url',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
        ]);

        $customerId = null;
        if (auth()->check()) {
            $customer = Customer::where('user_id', auth()->id())->first();
            $customerId = $customer?->id;
        }

        $endpointHash = hash('sha256', $validated['endpoint']);

        PushSubscription::updateOrCreate(
            ['endpoint_hash' => $endpointHash],
            [
                'customer_id' => $customerId,
                'endpoint' => $validated['endpoint'],
                'public_key' => $validated['keys']['p256dh'],
                'auth_token' => $validated['keys']['auth'],
            ]
        );

        return response()->json(['success' => true, 'message' => 'Abonnement enregistre.']);
    }

    public function unsubscribe(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|url',
        ]);

        $endpointHash = hash('sha256', $validated['endpoint']);
        PushSubscription::where('endpoint_hash', $endpointHash)->delete();

        return response()->json(['success' => true, 'message' => 'Desabonnement effectue.']);
    }
}
