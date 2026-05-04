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
            'name' => 'required|string|max:25',
            'password' => 'required|string|min:5',
            'role'          => 'required|in:user,teacher',
            'address'       => 'nullable|string|max:255',
            'child_name.*'    => 'nullable|string|max:100',
            'birthday.*'  => 'nullable|date',
            'gender.*'  => 'nullable|string|in:男,女',
            'allergy.*'  => 'nullable|string|max:250',
            'relationship.*'  => 'nullable|string|max:20',
            'phone_number.*'  => 'nullable|string|max:50',
            'contact_name.*'  => 'nullable|string|max:100',
            'sibling_name.*'  => 'nullable|string|max:25',
        ]);

        $user = User::create([
            'user_id' => $validated['user_id'],
            'name' => $validated['name'],
            'password' => Hash::make($validated['password']),
            'role'      => $validated['role'],
            'address'      => $validated['address'] ?? null,
        ]);

        if (!empty($validated['child_name'])) {
            foreach ($validated['child_name'] as $i => $name) {
                if (!empty($name)) {
                    $child = $user->children()->create([
                        'child_name' => $name,
                        'birthday' => $validated['birthday'][$i] ?? null,
                        'gender' => $validated['gender'][$i] ?? null,
                        'allergy' => $validated['allergy'][$i] ?? null,
                    ]);
                }
            }
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

        if (isset($child) && $request->has('sibling_name')) {
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

    public function search(Request $request)
    {
        $roles = ['teacher', 'user'];

        return view('admin.user_list', compact('roles'));
    }

    public function show(Request $request)
    {
        $roles = ['teacher', 'user'];

        $request->validate([
            'role' => 'required|string', // 役割は必須
            'name' => 'nullable|string'
        ]);

        $query = User::query();

        // 役割で絞り込み
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // ユーザー名で部分一致（漢字含む）
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        $users = $query->paginate(8)->appends($request->all());

        return view('admin.user_list', compact('roles', 'users'));
    }

    public function edit($id)
    {
        $user = User::with(['children.siblings', 'contacts'])->findOrFail($id);

        return view('admin.user_edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::with(['children', 'contacts'])->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:25',
            'password' => 'nullable|string|min:5',
            'address'       => 'nullable|string|max:255',
            'children' => 'nullable|array',
            'children.*.id' => 'nullable|integer|exists:children,id',

            'children.*.child_name'    => 'nullable|string|max:100',
            'children.*.birthday'  => 'nullable|date',
            'children.*.gender'  => 'nullable|string|in:男,女',
            'children.*.allergy'  => 'nullable|string|max:250',
            'relationship.*'  => 'nullable|string|max:20',
            'phone_number.*'  => 'nullable|string|max:50',
            'contact_name.*'  => 'nullable|string|max:100',
        ]);

        $user->update([
            'name'    => $validated['name'],
            'address' => $validated['address'] ?? null,
        ]);

        if (!empty($validated['password'])) {
            $user->update([
                'password' => bcrypt($validated['password']),
            ]);
        }

        if (!empty($validated['children'])) {

            foreach ($validated['children'] as $childData) {

                $updated = false;

                if (!empty($childData['id'])) {
                    $updated = Child::where('id', $childData['id'])
                        ->where('user_id', $user->id)
                        ->update([
                            'child_name' => $childData['child_name'],
                            'birthday'   => $childData['birthday'] ?? null,
                            'allergy'    => $childData['allergy'] ?? null,
                            'gender'     => $childData['gender'] ?? null,
                        ]);
                }

                if (!$updated) {
                    Child::create([
                        'user_id'    => $user->id,
                        'child_name' => $childData['child_name'],
                        'birthday'   => $childData['birthday'] ?? null,
                        'allergy'    => $childData['allergy'] ?? null,
                        'gender'     => $childData['gender'] ?? null,
                    ]);
                }
            }
        }

        if ($request->has('relationship')) {
            foreach ($request->relationship as $i => $relationship) {
                if (!empty($relationship) || !empty($request->phone_number[$i]) || !empty($request->contact_name[$i])) {
                    $user->contacts()->updateOrCreate(
                        ['id' => $user->contacts[$i]->id ?? null],
                        [
                            'relationship' => $relationship,
                            'phone_number' => $request->phone_number[$i] ?? null,
                            'contact_name' => $request->contact_name[$i] ?? null,
                        ]
                    );
                }
            }
        }

        return redirect()->route('admin.edit', $user->id)->with('success', 'ユーザー情報を更新しました。');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.show')->with('success', 'ユーザーを削除しました。');
    }
    //
}
