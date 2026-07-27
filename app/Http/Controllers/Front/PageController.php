<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Setting;
use Inertia\Inertia;

class PageController extends Controller
{
    /**
     * Afficher la page À propos
     */
    public function about()
    {
        $stats = [
            'products' => [
                'value' => Product::active()->count() . '+',
                'label' => 'Produits',
            ],
            'customers' => [
                'value' => Customer::count() . '+',
                'label' => 'Clients',
            ],
            'orders' => [
                'value' => Order::where('status', 'delivered')->count() . '+',
                'label' => 'Commandes livrées',
            ],
            'support' => [
                'value' => '7j/7',
                'label' => 'Support client',
            ],
        ];

        return Inertia::render('About', [
            'about_text' => Setting::get('about_text'),
            'stats' => $stats,
            'whatsapp_number' => config('app.whatsapp_number', '2250506805382'),
        ]);
    }
}
