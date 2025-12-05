<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SalesTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

class AdminController extends Controller
{
    public function __construct()
    {
        // Hanya admin yang bisa akses
        $this->middleware('permission:settings.manage');
    }

    /**
     * List semua users (Admin & Kasir)
     */
    public function index(Request $request)
    {
        $users = User::with('roles')
            ->withCount('permissions')
            ->latest('id')
            ->paginate(20);
        
        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
        ]);
    }

    /**
     * Create user baru
     */
    public function create()
    {
        return Inertia::render('Admin/Users/Create');
    }

    /**
     * Store user baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:Admin,Kasir',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assign role
        $user->assignRole($request->role);

        return redirect()->route('admin.users.index')->with('message', 'User berhasil dibuat');
    }

    /**
     * Edit user
     */
    public function edit(User $user)
    {
        $user->load('roles');
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:Admin,Kasir',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        $user->syncRoles([$request->role]);

        return redirect()->route('admin.users.index')->with('message', 'User berhasil diupdate');
    }

    /**
     * Delete user
     */
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('message', 'User berhasil dihapus');
    }

    /**
     * Transaction history per user
     */
    public function userTransactions(Request $request, User $user)
    {
        $dateFrom = $request->query('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->query('date_to', now()->toDateString());

        $transactions = SalesTransaction::with(['member:id,full_name'])
            ->where('created_by', $user->id)
            ->whereBetween('date_time', [$dateFrom, $dateTo])
            ->orderByDesc('date_time')
            ->paginate(20);

        return Inertia::render('Admin/Users/Transactions', [
            'user' => $user->load('roles'),
            'transactions' => $transactions,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    /**
     * Show user permissions page
     */
    public function showPermissions(User $user)
    {
        $user->load('permissions');
        $allPermissions = Permission::orderBy('name')->get();
        
        return Inertia::render('Admin/Users/Permissions', [
            'user' => $user,
            'allPermissions' => $allPermissions,
        ]);
    }

    /**
     * Update user permissions
     */
    public function updatePermissions(Request $request, User $user)
    {
        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        // Sync permissions (replace all existing permissions)
        $user->syncPermissions($request->permissions ?? []);
        
        // Reload user with permissions
        $user->refresh();
        $user->load('permissions');

        return back()->with('success', 'Permissions berhasil diupdate untuk ' . $user->name);
    }
}


