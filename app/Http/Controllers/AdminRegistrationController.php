<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminRegistrationController extends Controller
{
    private const ADMIN_ROLE_SLUG = 'admin_surat';

    public function create(): View
    {
        return view('auth.register-admin', [
            'registrationAvailable' => ! $this->adminExists(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($this->adminExists()) {
            return redirect()
                ->route('register-admin.create')
                ->with('status', 'Admin sudah tersedia.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $created = DB::transaction(function () use ($data): bool {
            $adminRole = Role::query()
                ->where('slug', self::ADMIN_ROLE_SLUG)
                ->lockForUpdate()
                ->first();

            if (! $adminRole) {
                throw ValidationException::withMessages([
                    'registration' => 'Role Admin belum tersedia. Jalankan seeder role terlebih dahulu.',
                ]);
            }

            if (User::withTrashed()->where('role_id', $adminRole->id)->lockForUpdate()->exists()) {
                return false;
            }

            User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role_id' => $adminRole->id,
                'division_id' => null,
                'is_active' => true,
            ]);

            return true;
        });

        if (! $created) {
            return redirect()
                ->route('register-admin.create')
                ->with('status', 'Admin sudah tersedia.');
        }

        return redirect()
            ->route('login')
            ->with('status', 'Admin berhasil dibuat. Silakan login.');
    }

    private function adminExists(): bool
    {
        $adminRoleId = Role::query()
            ->where('slug', self::ADMIN_ROLE_SLUG)
            ->value('id');

        return $adminRoleId !== null
            && User::withTrashed()->where('role_id', $adminRoleId)->exists();
    }
}
