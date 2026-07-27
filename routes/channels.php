<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal privé — seul le client propriétaire de la commande peut écouter
Broadcast::channel('order.{orderId}', function ($user, $orderId) {
    $customer = $user->customer;
    if (!$customer) return false;
    return \App\Models\Order::where('id', $orderId)
        ->where('customer_id', $customer->id)
        ->exists();
});
