<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Contact;
use App\Models\Child;
use App\Models\Sibling;
use App\Models\Attendance;
use App\Models\Reservation;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;


class MypageController extends Controller
{
    //ユーザートップページ
    public function index()
    {
        $user = Auth::user(); 
        $children = $user->children;

        return view('user.dashboard', compact('user','children'));
    }

    //初回子ども・ほごしゃ登録ページ表示
    public function create()
    {
        $user = Auth::user();$child = $user->children()
            ->where('child_name', '!=', '')
            ->whereNotNull('birthday')
            ->whereNotNull('gender')
            ->latest()
            ->first(); // 1人目の子ども
        $contacts = $user->contacts;         // 連絡先一覧
        $siblings = $child ? $child->siblings : collect(); // 兄弟姉妹

        return view('user.profile', compact('user', 'child', 'contacts', 'siblings'));
    }

    //子ども保護者登録
    public function register(Request $request)
    {
        $validated = $request->validate([
            'address'       => 'required|string|max:255',
            'child_name'    => 'required|string|max:100',
            'birthday'  => 'required|date',
            'gender'  => 'required|string|in:男,女',
            'relationship'   => 'required|array|min:2',
            'relationship.*'  => 'required|string|max:20',
            'phone_number'   => 'required|array|min:2',
            'phone_number.*'  => 'required|string|max:50',
            'contact_name'   => 'required|array|min:2',
            'contact_name.*'  => 'required|string|max:100',
            'allergy'  => 'nullable|string|max:250',
            'sibling_name.*'  => 'nullable|string|max:25',
        ],
        [
            'address.required' => '住所を入力してください。',
            'child_name.required' => 'お子様の名前を入力してください。',
            'birthday.required' => '生年月日を入力してください。',
            'gender.required' => '性別を選択してください。',
            'relationship.required' => '続柄を入力してください。',
            'relationship.*.required' => '続柄を入力してください。',
            'phone_number.required' => '電話番号を入力してください。',
            'phone_number.*.required' => '電話番号を入力してください。',
            'contact_name.required' => '連絡先名を入力してください。',
            'contact_name.*.required' => '連絡先名を入力してください。',
        ]
    );

        $user = Auth::user();

        $user->update([
            'address' => $validated['address'] ?? null,
        ]);

        $child = $user->children()->create([
            'child_name' => $validated['child_name'] ?? null,
            'birthday'   => $validated['birthday'] ?? null,
            'allergy'    => $validated['allergy'] ?? null,
            'gender'     => $validated['gender'] ?? null,
        ]);

        foreach ($validated['contact_name'] as $i => $name) {
            $user->contacts()->create([
                'contact_name' => $name,
                'relationship' => $validated['relationship'][$i],
                'phone_number' => $validated['phone_number'][$i],
            ]);
        }

        if ($request->has('sibling_name')) {
            foreach ($request->sibling_name as $name) {
                if ($name) {
                    $child->siblings()->create([
                        'sibling_name' => $name,
                    ]);
                }
            }
        }

        return redirect()->route('user.dashboard')->with('success', '初回登録が完了しました');
    }

    //子ども追加登録ページ表示
    public function show()
    {
        return view('user.child_create', [
            'child' => null
        ]);
    }

    //子ども情報登録
    public function store(Request $request)
    {
        $validated = $request->validate([
            'child_name'        => 'required|string|max:100',
            'birthday'          => 'required|date',
            'gender'            => 'required|string|in:男,女',
            'allergy'           => 'nullable|string|max:250',
            'sibling_name.*'    => 'nullable|string|max:25',
        ],
        [
            'child_name.required' => 'お子様の名前を入力してください。',
            'birthday.required' => '生年月日を入力してください。',
            'gender.required' => '性別を選択してください。',
        ]);

        $user = Auth::user();

        $child = $user->children()->create([
            'child_name' => $validated['child_name'],
            'birthday'   => $validated['birthday'],
            'gender'     => $validated['gender'],
            'allergy'    => $validated['allergy'] ?? null,
        ]);

        $user->update([
            'delete_at_target' => now()->addYears(5),
        ]);

        if ($request->has('sibling_name')) {
            foreach ($request->sibling_name as $name) {
                if (!empty($name)) {
                    $child->siblings()->create([
                        'sibling_name' => $name,
                    ]);
                }
            }
        }

        return redirect()
            ->route('user.dashboard')
            ->with('success', 'お子様情報を登録しました');
    }

    //保護者情報の編集ページ
    public function parentEdit()
    {
        $user = Auth::user();
        $contacts = $user->contacts;         // 連絡先一覧

        return view('user.parent_edit', compact('user', 'contacts'));
    }

    //保護者情報の編集登録
    public function parentUpdate(Request $request)
    {
        $validated = $request->validate([
            'address'       => 'required|string|max:255',
            'relationship'   => 'required|array|min:2',
            'relationship.*'  => 'required|string|max:20',
            'phone_number'   => 'required|array|min:2',
            'phone_number.*'  => 'required|string|max:50',
            'contact_name'   => 'required|array|min:2',
            'contact_name.*'  => 'required|string|max:100',
        ],
        [
            'address.required' => '住所を入力してください。',
            'relationship.required' => '続柄を入力してください。',
            'relationship.*.required' => '続柄を入力してください。',
            'phone_number.required' => '電話番号を入力してください。',
            'phone_number.*.required' => '電話番号を入力してください。',
            'contact_name.required' => '連絡先名を入力してください。',
            'contact_name.*.required' => '連絡先名を入力してください。',
        ]
    );

        $user = Auth::user();

        $user->update([
            'address' => $validated['address'] ?? null,
        ]);

        $contacts = $user->contacts;

        foreach ($request->contact_name as $i => $name) {
            // 何も入力されていない行はスキップ
            if (
                empty($name) &&
                empty($request->relationship[$i]) &&
                empty($request->phone_number[$i])
            ) {
                continue;
            }

            // 既存データがあれば更新
            if (isset($contacts[$i])) {
                $contacts[$i]->update([
                    'contact_name' => $name,
                    'relationship' => $request->relationship[$i] ?? null,
                    'phone_number' => $request->phone_number[$i] ?? null,
                ]);
            } else {
                // なければ新規作成
                $user->contacts()->create([
                    'contact_name' => $name,
                    'relationship' => $request->relationship[$i] ?? null,
                    'phone_number' => $request->phone_number[$i] ?? null,
                ]);
            }
        }

        return redirect()->route('user.dashboard')->with('success', '保護者情報を更新しました');
    }

    //子ども選択ページ
    public function search($child_id)
    {
        $user = Auth::user(); 
        $child = $user->children()->where('id', $child_id)->firstOrFail();

        session(['child_id' => $child->id]);

        return view('user.child_select', compact('child'));
    }

    public function mypage() 
    {
        $user = Auth::user(); 
        $child = null;
        if (session()->has('child_id')) {
            $child = $user->children()
                ->where('id', session('child_id'))
                ->first();
        }

        return view('user.mypage', compact('user','child'));

    }

    public function  usageHistory(Request $request, $month = null)
    {
        $user = Auth::user(); 

        if (!session()->has('child_id')) {
            abort(404, 'Child not selected');
        }

        $child = $user->children()
            ->where('id', session('child_id'))
            ->firstOrFail();

        $currentMonth = $month ? Carbon::parse($month . '-01') : now()->startOfMonth();

        $prevMonth = $currentMonth->copy()->subMonth();
        $nextMonth = $currentMonth->copy()->addMonth();

        $attendances = Attendance::whereHasMorph(
            'reservable',
            [Reservation::class], // ← これが重要！Reservation 以外を完全排除
            function ($q) use ($child, $currentMonth) {
                $q->where('child_id', $child->id)
                    ->whereHas('slot.dateValue', function ($q2) use ($currentMonth) {
                        $q2->whereBetween('date', [
                            $currentMonth->copy()->startOfMonth(),
                            $currentMonth->copy()->endOfMonth()
                        ]);
                    });
            }
        )
        ->with(['reservable.slot.dateValue'])
        ->get();

        $attendances = $attendances->filter(function ($a) {
            return $a->actual_start_time && $a->actual_end_time;
        });

        $histories = $attendances->map(function ($a) {
            return [
                'date' => $a->reservable->slot->dateValue->date,
                'start_time' => $a->actual_start_time,
                'end_time'   => $a->actual_end_time,
                'meal'       => $a->meal_used === 'yes',
                'snack'      => $a->snack_used === 'yes',
                'fee'        => $a->accounted == 0 ? null : $a->total_fee,
                'accounted'  => $a->accounted,
            ];
        });

        $totalFee = $attendances
            ->where('accounted', 1)
            ->sum('total_fee');

        return view('user.history', compact(
            'histories',
            'currentMonth',
            'prevMonth',
            'nextMonth',
            'child',
            'totalFee'
        ));
    }

    public function childEdit()
    {
        $user = Auth::user();

        if (!session()->has('child_id')) {
            abort(404);
        }

        $child = $user->children()
            ->where('id', session('child_id'))
            ->firstOrFail();

        return view('user.child_edit', compact('child'));
    }

    public function childUpdate(Request $request)
    {
        $user = Auth::user();

        if (!session()->has('child_id')) {
            abort(404);
        }

        $child = $user->children()
            ->where('id', session('child_id'))
            ->firstOrFail();

        $validated = $request->validate([
            'child_name'        => 'required|string|max:100',
            'birthday'          => 'required|date',
            'gender'            => 'required|string|in:男,女',
            'allergy'           => 'nullable|string|max:250',
            'sibling_name.*'    => 'nullable|string|max:25',
        ],
        [
            'child_name.required' => 'お子様の名前を入力してください。',
            'birthday.required' => '生年月日を入力してください。',
            'gender.required' => '性別を選択してください。',
        ]
    );

        $child->update($validated);

        return redirect()
            ->route('user.mypage')
            ->with('success', 'お子様情報を更新しました');
    }


    //
}
