<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a searchable, filterable list of users.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'integer', 'exists:roles,id'],
            'division' => ['nullable', 'integer', 'exists:divisions,id'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $users = User::query()
            ->with(['role', 'division'])
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($filters['role'] ?? null, fn ($query, int $roleId) => $query->where('role_id', $roleId))
            ->when($filters['division'] ?? null, fn ($query, int $divisionId) => $query->where('division_id', $divisionId))
            ->when(
                $filters['status'] ?? null,
                fn ($query, string $status) => $query->where('is_active', $status === 'active'),
            )
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'roles' => Role::query()->orderBy('name')->get(),
            'divisions' => Division::query()->orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }
}
