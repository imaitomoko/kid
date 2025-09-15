<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Child;
use App\Models\Contact;
use App\Models\Sibling;

class AdminUserController extends Controller
{
    public function index ()
    {
        return view('admin.user');
    }

    public function create()
    {
        return view('admin.user_register');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|unique:users,user_id',
            'user_name' => 'required|string|max:25',
            'password' => 'required|string|min:5',
            'role'          => 'required|in:ユーザー,職員,管理者',
            'address'       => 'nullable|string|max:255',
            'child_name'    => 'nullable|string|max:100',
            'birthday'  => 'nullable|date',
            'gender'  => 'nullable|string|in:男,女',
            'relationship.*'  => 'nullable|string|max:20',
            'phone_number.*'  => 'nullable|string|max:50',
            'contact_name.*'  => 'nullable|string|max:100',
            'allergy'  => 'nullable|string|max:250',
            'sibling_name.*'  => 'nullable|string|max:25',
        ]);

        $user = User::create([
            'user_id' => $validated['user_id'],
            'name' => $validated['user_name'],
            'password' => Hash::make($validated['password']),
            'role'      => $validated['role'],
            'address'      => $validated['address'] ?? null,
        ]);

        if (!empty($validated['child_name'])) {
            $child = $user->children()->create([
                'child_name' => $validated['child_name'],
                'birthday' => $validated['birthday'] ?? null,
                'allergy' => $validated['allergy'] ?? null,
                'gender' => $validated['gender'] ?? null,
            ]);
        }

        if ($request->has('relationship')) {
            foreach ($request->relationship as $i => $relationship) {
                if (!empty($relationship) || !empty($request->phone_number[$i]) || !empty($request->contact_name[$i])) {
                    $user->contacts()->create([
                        'relationship' => $relationship,
                        'phone_number' => $request->phone_number[$i] ?? null,
                        'contact_name' => $request->contact_name[$i] ?? null,
                    ]);
                }
            }
        }

        if ($request->has('sibling_name')) {
            foreach ($request->sibling_name as $sibling) {
                if (!empty($sibling)) {
                    $child->siblings()->create([
                        'sibling_name' => $sibling,
                    ]);
                }
            }
        }

        return redirect()->route('admin.create')->with('success', 'ユーザーを登録しました');

    }
    //
}
