<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index()
    {
        $admins = User::query()
            ->where('is_admin', true)
            ->orderByDesc('id')
            ->get(['id', 'name', 'email', 'is_admin', 'created_at']);

        return response()->json(['data' => $admins]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
                'regex:/^\S+$/',
            ],
        ], [
            'password.min' => 'Password must be at least 8 characters.',
            'password.regex' => 'Password must include uppercase, lowercase, number, special character, and no spaces.',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'is_admin' => true,
        ]);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
            ],
        ]);
    }

    public function destroy($id)
    {
        $adminCount = User::query()->where('is_admin', true)->count();
        if ($adminCount <= 1) {
            return response()->json(['message' => 'Cannot delete the last admin.'], 422);
        }

        $u = User::query()
            ->where('id', $id)
            ->where('is_admin', true)
            ->first();

        if (!$u) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $u->tokens()->delete();
        $u->delete();

        return response()->json(['ok' => true]);
    }
}
