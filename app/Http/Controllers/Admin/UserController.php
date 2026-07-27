<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        Inertia::setRootView('layouts.admin-inertia');

        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $currentUserId = auth()->id();

        $users = $query->latest()->paginate(20)->withQueryString();

        // Ajouter le flag is_current_user dans chaque item
        $users->getCollection()->transform(function (User $u) use ($currentUserId) {
            return [
                'id'              => $u->id,
                'name'            => $u->name,
                'email'           => $u->email,
                'role'            => $u->role,
                'is_active'       => (bool) $u->is_active,
                'created_at'      => $u->created_at->format('d/m/Y'),
                'is_current_user' => $u->id === $currentUserId,
            ];
        });

        return Inertia::render('Admin/Users/Index', [
            'users'   => $users,
            'filters' => $request->only('search', 'role'),
        ]);
    }

    public function create()
    {
        Inertia::setRootView('layouts.admin-inertia');

        return Inertia::render('Admin/Users/Create');
    }

    public function show(User $user)
    {
        Inertia::setRootView('layouts.admin-inertia');

        $userData = [
            'id'              => $user->id,
            'name'            => $user->name,
            'email'           => $user->email,
            'role'            => $user->role,
            'is_active'       => (bool) $user->is_active,
            'created_at'      => $user->created_at->format('d/m/Y à H:i'),
            'updated_at'      => $user->updated_at->format('d/m/Y à H:i'),
            'is_current_user' => $user->id === auth()->id(),
        ];

        return Inertia::render('Admin/Users/Show', ['user' => $userData]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => 'required|in:admin,manager,staff',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);

        User::create($validated);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Utilisateur créé.');
    }

    public function edit(User $user)
    {
        Inertia::setRootView('layouts.admin-inertia');

        $userData = [
            'id'              => $user->id,
            'name'            => $user->name,
            'email'           => $user->email,
            'role'            => $user->role,
            'is_active'       => (bool) $user->is_active,
            'created_at'      => $user->created_at->format('d/m/Y'),
            'is_current_user' => $user->id === auth()->id(),
        ];

        return Inertia::render('Admin/Users/Edit', ['user' => $userData]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,manager,staff,customer',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Password::min(8)],
            ]);
            $validated['password'] = Hash::make($request->password);
        }

        $user->update($validated);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Utilisateur mis à jour.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete();

        return back()->with('success', 'Utilisateur supprimé.');
    }
}

