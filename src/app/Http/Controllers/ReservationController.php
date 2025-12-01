<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Reservation;
use App\Models\NonmemberReservation;
use App\Models\ReservationSlot;
use App\Models\DateValue;
use App\Models\Child;


class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        $displayMonth = Carbon::createFromDate($year, $month, 1);

        $today = now()->startOfDay();

        if ($today->day <= 15) {
            $maxDate = $today->copy()->endOfMonth();
        } else {
            $maxDate = $today->copy()->addMonthNoOverflow()->endOfMonth();
        }

        $daysInMonth = $displayMonth->daysInMonth;
        $dates = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $displayMonth->copy()->day($day);
            $dateString = $date->toDateString();
            $weekday = $date->dayOfWeek;

            if ($date->lt($today) || $date->gt($maxDate) || $weekday === 0 || $weekday === 6) {
                $dates[$dateString] = [
                    'day' => $day,
                    'canBook' => false,
                    'label' => '受付不可',
                ];
                continue;
            }

            $dateValue = \App\Models\DateValue::where('date', $dateString)->first();

            if (!$dateValue) {
                $dates[$dateString] = [
                    'day' => $day,
                    'canBook' => false,
                    'label' => '×',
                ];
            } else {
                $hasCapacity = \App\Models\ReservationSlot::where('date_value_id', $dateValue->id)
                    ->where('capacity', '>', 0)
                    ->exists();

                $dates[$dateString] = [
                    'day' => $day,
                    'canBook' => $hasCapacity,
                    'label' => $hasCapacity ? '⚪︎' : '×',
                ];
            }
        }

        return view('user.reservation', compact('year', 'month', 'displayMonth', 'dates'));
    }

    public function show($date)
    {

        if (!$date) {
            abort(404);
        }

        // その日の DateValue を取得
        $dateValue = \App\Models\DateValue::where('date', $date)->first();

        if (!$dateValue) {
            abort(404);
        }

        // 予約枠一覧取得
        $slots = \App\Models\ReservationSlot::where('date_value_id', $dateValue->id)
                ->orderBy('slot_time')
                ->get();

        return view('user.reservation_list', compact('date', 'slots'));
    }

    public function confirm(Request $request)
    {

        $validated = $request->validate([
            'reservation_slot_ids' => 'required|array',
            'date' => 'required|date',
            'round_type' => 'required|string',
            'purpose' => 'required|string',
            'note' => 'nullable|string',
        ]);

        // 時間帯を DB から取得
        $slots = \App\Models\ReservationSlot::whereIn('id', $validated['reservation_slot_ids'])
            ->orderBy('slot_time')
            ->get();

        return view('user.confirm', [
            'date' => $validated['date'],
            'slots' => $slots,
            'meal' => $request->meal ? 1 : 0,
            'snack' => $request->snack ? 1 : 0,
            'round_type' => $validated['round_type'],
            'purpose' => $validated['purpose'],
            'note' => $validated['note'],
            'slot_ids' => $validated['reservation_slot_ids'],
        ]);
    }

    public function store(Request $request)
    {

        $child = Child::where('user_id', auth()->user()->id)->first();

        if (!$child) {
            return back()->with('error', '子どもの情報がありません。');
        }

        foreach ($request->slot_ids as $slotId) {
            \App\Models\Reservation::create([
                'child_id' => $child->id,
                'reservation_slot_id' => $slotId,
                'meal' => $request->meal ? 1 : 0,
                'snack' => $request->snack ? 1 : 0,
                'round_type' => $request->round_type,
                'purpose' => $request->purpose,
                'note' => $request->note,
            ]);
        }

        return redirect()->route('user.reservation')->with('success', '予約が完了しました');
    }



    //
}
