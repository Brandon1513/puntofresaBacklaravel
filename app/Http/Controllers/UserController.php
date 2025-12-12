<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $r)
    {
        $users = \App\Models\User::with('roles')
            ->when($r->filled('nombre'), fn($q)=>$q->nombre($r->nombre))
            ->when($r->filled('estado'), fn($q)=>$q->estado($r->estado))
            ->when($r->filled('rol'),    fn($q)=>$q->conRol($r->rol))
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    public function toggle(\App\Models\User $user)
    {
        if ($user->hasRole('superadmin')) abort(403);
        $user->activo = !$user->activo;
        $user->save();
        return back()->with('success', $user->activo ? 'Usuario activado.' : 'Usuario inactivado.');
    }
    public function create()
    {
        // objetos con id y name
        $roles = Role::select('id','name')->orderBy('name')->get();
        return view('users.create', [
            'roles' => $roles,
            'selected' => []
        ]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'roles' => 'required|array',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'activo'   => true, // siempre activos al crear
        ]);

        $roleNames = Role::whereIn('id', $data['roles'])->pluck('name')->all();
        $user->syncRoles($roleNames);

        return to_route('users.index')->with('ok','Usuario creado.');
    }

    public function edit(User $user)
    {
       $roles = Role::select('id','name')->orderBy('name')->get();
        $selected = $user->roles()->pluck('id')->all(); // IDs seleccionados
        return view('users.edit', compact('user','roles','selected'));
    }

    public function update(Request $r, User $user)
    {
        $data = $r->validate([
            'name' => 'required|string|max:120',
            'email' => ['required','email', Rule::unique('users','email')->ignore($user->id)],
            'password' => 'nullable|min:8|confirmed',
            'roles' => 'required|array',
        ]);

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);
        if (!empty($data['password'])) {
            $user->password = bcrypt($data['password']);
        }
        $user->save();

        $roleNames = Role::whereIn('id', $data['roles'])->pluck('name')->all();
        $user->syncRoles($roleNames);

        return to_route('users.index')->with('ok','Usuario actualizado.');
    }

    public function destroy(User $user)
    {
        if ($user->hasRole('superadmin')) abort(403);
        $user->delete();
        return back()->with('ok','Usuario eliminado.');
    }
}
