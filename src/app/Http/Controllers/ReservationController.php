<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Reservation;
use App\Models\NonmemberReservation;
use App\Models\ReservationSlot;
use App\Models\DateValue;
use App\Models\Child;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        $displayMonth = Carbon::createFromDate($year, $month, 1);

        $today = now()->startOfDay();
        $startDate = $today->copy()->addDays(2);
        $endDate = $startDate->copy()->addDays(13);

        $daysInMonth = $displayMonth->daysInMonth;
        $dates = [];       

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $displayMonth->copy()->day($day);
            $dateString = $date->toDateString();
            $weekday = $date->dayOfWeek;

            if ($date->lt($startDate) || $date->gt($endDate) || $weekday === 0 || $weekday === 6) {
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

        return view('user.reservation', compact('year', 'month', 'displayMonth', 'dates', ));
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
            'meal' => 'nullable',
            'snack' => 'nullable',
        ]);

        $child_id = session('child_id');

        if (!$child_id) {
            return back()->with('error', '子どもが選択されていません');
        }

        $child = Auth::user()->children()
            ->where('id', $child_id)
            ->first();

        if (!$child) {
            return back()->with('error', '不正な子どもです');
        }

        // 時間帯を DB から取得
        $slots = \App\Models\ReservationSlot::whereIn('id', $validated['reservation_slot_ids'])
            ->orderBy('slot_time')
            ->get();

        $exists = \App\Models\Reservation::where('child_id', $child->id)
            ->whereIn('reservation_slot_id', $request->reservation_slot_ids)
            ->exists();

        if ($exists) {
            return back()->with('error', 'この時間帯はすでに予約済みです');
        }

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

        DB::beginTransaction();

        try {
            $child = Auth::user()->children()
                ->where('id', $request->child_id)
                ->first();

            if (!$child) {
                DB::rollBack();
                return back()->with('error', '子どもの情報がありません。');
            }

            foreach ($request->slot_ids as $slotId) {
                $slot = \App\Models\ReservationSlot::where('id', $slotId)->lockForUpdate()->first();

                if (!$slot) {
                    DB::rollBack();
                    return back()->with('error', 'スロットが存在しません');
                }

                if ($slot->capacity <= 0) {
                    DB::rollBack();
                    return back()->with('error', 'この時間帯は満席になりました。');
                }

                $slot->capacity -= 1;
                $slot->save();

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

            DB::commit();
            return redirect()->route('user.reservation')->with('success', '予約が完了しました');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '予約処理中にエラーが発生しました。');
        }

    }

    public function history()
    {

        $user = Auth::user(); 

        if (!session()->has('child_id')) {
            abort(404, 'Child not selected');
        }

        $child = $user->children()
            ->where('id', session('child_id'))
            ->firstOrFail();

        if (!$child) {
            return redirect()->route('user.reservation')->with('error', '子どもの情報がありません。');
        }

        $today = now()->toDateString();

        $reservations = Reservation::with(['slot.dateValue'])
            ->where('child_id', $child->id)
            ->get()
            ->filter(fn($r) => $r->slot->dateValue->date >= now()->toDateString()) 
            ->sortBy(fn($r) => $r->slot->dateValue->date);

        $grouped = [];
            foreach ($reservations as $res) {
                $date = $res->slot->dateValue->date;
                if (!isset($grouped[$date])) {
                    $grouped[$date] = [];
                }

            $grouped[$date][] = $res;
        }

        $summary = [];
        foreach ($grouped as $date => $dayReservations) {
            $daySummary = [];
            $temp = [];

            foreach ($dayReservations as $res) {
                $slotStart = \Carbon\Carbon::parse($res->slot->slot_time);

                if (empty($temp)) {
                    $temp = [
                        'start' => $slotStart,
                        'end' => $slotStart->copy()->addMinutes(30),
                        'meal' => $res->meal,
                        'snack' => $res->snack,
                        'note' => $res->note,
                        'ids' => [$res->id],                        
                    ];
                } else {
                // 直前のendと今回のstartが連続している場合は延長
                    if ($temp['end']->eq($slotStart)) {
                        $temp['end']->addMinutes(30);
                        $temp['ids'][] = $res->id;
                    } else {
                        $daySummary[] = $temp;
                        $temp = [
                            'start' => $slotStart,
                            'end' => $slotStart->copy()->addMinutes(30),
                            'meal' => $res->meal,
                            'snack' => $res->snack,
                            'note' => $res->note,
                            'ids' => [$res->id],
                        ];
                    }
                }
            }

            if (!empty($temp)) {
                $daySummary[] = $temp;
            }

            $summary[$date] = $daySummary;
        }

        return view('user.reservation_history', compact('child', 'summary'));
    }

    public function destroy(Request $request)
    {

        $reservationIds = $request->reservation_ids;

        if (!$reservationIds || !is_array($reservationIds)) {
            return back()->with('error', '削除対象の予約がありません。');
        }

        DB::beginTransaction();

        try {
            // ログインユーザーの子どもID一覧を取得
            $childIds = auth()->user()->children()->pluck('id');

            // 対象の予約を取得（自分の子どものものだけ）
            $reservations = \App\Models\Reservation::whereIn('id', $reservationIds)
                ->whereIn('child_id', $childIds)
                ->get();

            if ($reservations->isEmpty()) {
                DB::rollBack();
                return back()->with('error', '削除できる予約がありません。');
            }

            foreach ($reservations as $reservation) {

                // スロットの空き数を戻す
                $slot = \App\Models\ReservationSlot::where('id', $reservation->reservation_slot_id)
                    ->lockForUpdate()
                    ->first();

                if ($slot) {
                    $slot->capacity += 1;
                    $slot->save();
                }

                // 予約削除
                $reservation->delete();
            }

            DB::commit();

            return back()->with('success', '予約をキャンセルしました');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'キャンセル処理中にエラーが発生しました');
        }    
    }

    //
}
