<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class MypageController extends Controller
{
    public function index()
    {
        $user = Auth::user(); // ログイン中のユーザー情報を取得

        return view('user.dashboard', compact('user'));
    }
    //
}
