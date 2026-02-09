<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required','email'],
            'password' => ['required','string'],
        ], [
            'email.required' => 'กรุณากรอกอีเมล',
            'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
            'password.required' => 'กรุณากรอกรหัสผ่าน',
        ]);

        $email = strtolower(trim($data['email']));

        $user = User::where('email', $email)->first();

        // ✅ Email ไม่ถูก (ไม่พบ user หรือไม่ใช่ admin)
        if (!$user || !$user->is_admin) {
            return response()->json([
                'field' => 'email',
                'message' => 'อีเมลผิด',
            ], 401);
        }

        // ✅ Password ไม่ถูก
        if (!Hash::check($data['password'], $user->password)) {
            return response()->json([
                'field' => 'password',
                'message' => 'รหัสผ่านผิด',
            ], 401);
        }

        $token = $user->createToken('admin')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
            ],
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();
        return response()->json(['ok' => true]);
    }
}
