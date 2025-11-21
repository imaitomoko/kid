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
    public function index()
    {
        $user = Auth::user(); 
        $child = $user->child;

        return view('user.dashboard', compact('user','child'));
    }

    public function create()
    {
        $user = Auth::user();
        $child = $user->child()->first(); // 1人目の子ども
        $contacts = $user->contacts;         // 連絡先一覧
        $siblings = $child ? $child->siblings : collect(); // 兄弟姉妹

        return view('user.profile', compact('user', 'child', 'contacts', 'siblings'));
    }


    public function update(Request $request)
    {
        $validated = $request->validate([
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

        $user = Auth::user();

        $user->update([
            'address' => $validated['address'] ?? null,
        ]);

        $child = $user->child()->first();

        if ($child) {
            $child->update([
                'child_name' => $validated['child_name'],
                'birthday'   => $validated['birthday'] ?? null,
                'allergy'    => $validated['allergy'] ?? null,
                'gender'     => $validated['gender'] ?? null,
            ]);
        }

        $user->contacts()->delete();

        if ($request->has('contact_name')) {
            foreach ($request->contact_name as $i => $name) {
                if ($name || $request->relationship[$i] || $request->phone_number[$i]) {
                    $user->contacts()->create([
                        'contact_name'  => $name,
                        'relationship'  => $request->relationship[$i] ?? null,
                        'phone_number'  => $request->phone_number[$i] ?? null,
                    ]);
                }
            }
        }

         // --- Siblings 更新（全削除→再登録）---
        if ($child) {
            $child->siblings()->delete();

            foreach ($request->sibling_name as $name) {
                if ($name) {
                    $child->siblings()->create([
                        'sibling_name' => $name,
                    ]);
                }
            }
        }

        return redirect()->route('user.dashboard')->with('success', 'プロフィールを更新しました');
    }

    public function mypage() 
    {
        $user = Auth::user(); 
        $child = $user->child;

        return view('user.mypage', compact('user','child'));

    }

    public function  usageHistory(Request $request, $month = null)
    {
        $user = Auth::user(); 
        $child = $user->child;

        if (!$child) {
            abort(404, 'Child not found');
        }


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

        $usageDates = $attendances->map(function ($a) {
            return $a->reservable->slot->dateValue->date;  // 実際の利用日
        })->unique()->sort();

        $totalFee = $attendances->sum('total_fee');

        return view('user.history', compact(
            'attendances',
            'usageDates',
            'currentMonth',
            'prevMonth',
            'nextMonth',
            'child',
            'totalFee'
        ));


    }
    //
}
