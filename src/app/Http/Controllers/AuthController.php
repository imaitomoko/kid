<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        $role = $request->route('role') ?? 'user';

        // ロールごとにビューを分ける
        return match ($role) {
            'admin' => view('admin.login'),
            'teacher' => view('teacher.login'),
            default => view('user.login'), // 通常ユーザー
        };
    }
    
    public function login(Request $request)
    {
        $credentials = $request->only('user_id', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // ロールに応じてリダイレクト
            return match ($user->role) {
                'admin' => redirect('/admin'),
                'teacher' => redirect('/teacher'),
                default => redirect('/dashboard'),
            };
        }

        return back()->withErrors([
            'user_id' => 'ユーザーIDまたはパスワードが間違っています',
        ]);
    }

    //
}
