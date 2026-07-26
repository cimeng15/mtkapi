<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->paginate(20);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.form', ['user' => new User(['role' => 'operator', 'is_active' => true])]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(6)],
            'role' => 'required|in:superadmin,operator',
            'is_active' => 'nullable|boolean',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = $request->boolean('is_active');
        $user = User::create($data);
        ActivityLog::record('user.create', 'Menambah admin: '.$user->email);

        return redirect()->route('users.index')->with('success', 'Admin baru ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('users.form', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => ['nullable', 'confirmed', Password::min(6)],
            'role' => 'required|in:superadmin,operator',
            'is_active' => 'nullable|boolean',
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        $user->is_active = $request->boolean('is_active');
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();
        ActivityLog::record('user.update', 'Mengubah admin: '.$user->email);

        return redirect()->route('users.index')->with('success', 'Data admin diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }
        $email = $user->email;
        $user->delete();
        ActivityLog::record('user.delete', 'Menghapus admin: '.$email);

        return back()->with('success', 'Admin dihapus.');
    }
}
