<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $q = trim((string)$request->query('q', ''));

        $users = User::query()
            ->with('roles')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', compact('users', 'q'));
    }

    public function create()
    {
        $this->authorize('create', User::class);

        $roles = Role::orderBy('name')->get();
        $defaultRole = config('pymecrm.default_role', 'Ejecutivo');

        return view('users.create', compact('roles', 'defaultRole'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:8','confirmed'],
            'roles' => ['required','array','min:1'],
            'roles.*' => ['string','exists:roles,name'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->syncRoles($data['roles'] ?? []);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('users.index')->with('success', 'Usuario creado.');
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $roles = Role::orderBy('name')->get();
        $user->load('roles');

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email,' . $user->id],
            'password' => ['nullable','string','min:8','confirmed'],
            'roles' => [$request->user()->can('roles.update') ? 'required' : 'sometimes', 'array', 'min:1'],
            'roles.*' => ['string','exists:roles,name'],
        ]);

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        if (!empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $user->update($payload);

        // Solo quien tenga permiso para administrar roles puede cambiarlos
        if ($request->user()->can('roles.update')) {
            $user->syncRoles($data['roles'] ?? []);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        return redirect()->route('users.index')->with('success', 'Usuario actualizado.');
    }

    public function destroy(Request $request, User $user)
    {
        $this->authorize('delete', $user);

        if ((int)$user->id === (int)$request->user()->id) {
            abort(403, 'No podés eliminar tu propio usuario.');
        }

        if ($user->hasRole('Admin')) {
            abort(403, 'No se puede eliminar un usuario Admin.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado.');
    }
}
