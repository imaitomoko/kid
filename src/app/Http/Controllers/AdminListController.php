<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminListController extends Controller
{
    public function index ()
    {
        return view('admin.dashboard');
    }

    public function list()
    {
        $today = \Carbon\Carbon::today();

        $reservations = Reservation::with([
            'child',
            'slot.dateValue',
            'attendance.feeItems.feeItem'
        ])
        ->whereHas('slot.dateValue', fn($q) => $q->where('date', $today))
        ->get();

        // 合計金額計算
        $totalFee = $reservations->sum(function ($r) {
            return optional($r->attendance)->feeItems->sum(fn($f) => $f->feeItem->amount ?? 0);
        });

        return view('admin.book_list', compact('reservations', 'totalFee', 'today'));
    }

    public function start(Reservation $reservation)
    {
        $reservation->attendance()->create([
            'actual_start_time' => now(),
            'actual_end_time' => null
        ]);
        return back();
    }

    public function end(Reservation $reservation)
    {
        $attendance = $reservation->attendance;
        $attendance->update(['actual_end_time' => now()]);
        return back();
    }

    public function mealUsed(Request $request, Attendance $attendance)
    {
        $attendance->update([
            'meal_used' => $request->has('meal_used') ? 'yes' : null
        ]);
        return back();
    }

    public function snackUsed(Request $request, Attendance $attendance)
    {
        $attendance->update([
            'snack_used' => $request->has('snack_used') ? 'yes' : null
        ]);
        return back();
    }

    //
}
