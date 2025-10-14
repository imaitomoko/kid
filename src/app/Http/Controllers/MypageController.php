<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Contact;
use App\Models\Child;
use App\Models\Sibling;
use Illuminate\Support\Facades\Hash;


class MypageController extends Controller
{
    public function index()
    {
        $user = Auth::user(); // ログイン中のユーザー情報を取得

        return view('user.dashboard', compact('user'));
    }

    public function create()
    {
        $user = Auth::user();

        if (!$user->isProfileComplete()){
            return redirect()->route('user.dashboard')
                            ->with('error', '予約をする前にユーザー情報を入力してください');
        }

        return view('user.schedule');

    }

    public function update(Request $request)
    {
        $request->validate([
            'address' => 'request|string|max:255',
            'child_name' => 'request|string|max:100',
            'birthday' => 'request|date',
            'gender' => 'request|string|max:10',
            'address' => 'request|string|max:255',
            'contact_name' => 'request|string|max:255',
            'relationship' => 'request|string|max:10',
            'phone_number' => 'request|string|max:255',
        ]);
        return back();
    }
    //
}
