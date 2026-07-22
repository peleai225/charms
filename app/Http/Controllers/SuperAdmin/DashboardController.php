<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord SuperAdmin
     */
    public function index()
    {
        return Inertia::render('SuperAdmin/Dashboard', [
            'stats' => [
                'users' => User::whereIn('role', ['admin', 'manager', 'staff', 'superadmin'])->count(),
                'admins' => User::where('role', 'admin')->count(),
                'superadmins' => User::where('role', 'superadmin')->count(),
                'customers' => Customer::count(),
                'orders' => Order::count(),
                'products' => Product::count(),
            ],
            'recentActivity' => ActivityLog::with('user')
                ->latest()
                ->take(10)
                ->get()
                ->map(fn($log) => [
                    'id' => $log->id,
                    'action' => $log->action,
                    'description' => $log->description,
                    'user' => $log->user ? [
                        'name' => $log->user->name,
                        'email' => $log->user->email,
                        'role' => $log->user->role,
                    ] : null,
                    'created_at' => $log->created_at->diffForHumans(),
                ]),
        ]);
    }
}
