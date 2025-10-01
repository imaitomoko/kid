<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Reservation;
use App\Models\NonmemberReservation;
use App\Models\ReservationSlot;
use App\Models\DateValue;



class AdminReservationController extends Controller
{
    public function calendar(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        $current = Carbon::createFromDate($year, $month, 1);

        // 日付ステータスは不要（常に⚪︎表示）
        $daysInMonth = $current->daysInMonth;
        $dates = collect(range(1, $daysInMonth))->map(fn() => ['status' => '⚪︎']);

        return view('admin.admin_reservation', compact('year', 'month', 'current', 'dates'));
    }

    public function list($date)
    {
        // 会員予約を取得
        $memberReservations = Reservation::with(['child', 'slot.dateValue'])
            ->whereHas('slot.dateValue', function ($query) use ($date) {
                $query->whereDate('date', $date);
            })
            ->get();
        // 非会員予約を取得
        $nonmemberReservations = NonmemberReservation::with(['dateValue'])
            ->whereHas('dateValue', function ($query) use ($date) {
                $query->whereDate('date', $date);
            })
            ->get();

        $reservations = collect();

        foreach ($memberReservations->groupBy('child_id') as $childId => $group) {
            $start = $group->min(fn($r) => $r->reservationSlot->slot_time);
            $end   = $group->max(fn($r) => $r->reservationSlot->slot_time);
            $end   = \Carbon\Carbon::parse($end)->addMinutes(30)->format('H:i'); // 30分枠想定

            $reservations->push([
                'is_member'  => true,
                'child_name' => $group->first()->child->child_name ?? '',
                'time'       => \Carbon\Carbon::parse($start)->format('H:i') . ' ~ ' . $end,
                'id'         => $group->first()->id,
            ]);
        }

        foreach ($nonmemberReservations as $reservation) {
            $reservations->push([
                'is_member'  => false,
                'child_name' => $reservation->child_name,
                'time'       => \Carbon\Carbon::parse($reservation->start_time)->format('H:i') 
                            . ' ~ ' 
                            . \Carbon\Carbon::parse($reservation->end_time)->format('H:i'),
                'id'         => $reservation->id,
            ]);
        }

        $reservations = $reservations->sortBy('time')->values();


        return view('admin.admin_reservation_list', compact('date', 'reservations'));
    }

    public function cancel($id)
    {
    // 会員 or 非会員どちらの予約かを判定して削除
        $reservation = Reservation::find($id);
        if ($reservation) {
            $reservation->delete();
            return back()->with('success', '会員予約をキャンセルしました。');
        }

        $nonmember = NonmemberReservation::find($id);
        if ($nonmember) {
            $nonmember->delete();
            return back()->with('success', '非会員予約をキャンセルしました。');
        }

        return back()->with('error', '該当する予約が見つかりませんでした。');
    }

    public function createNonMember($date)
    {
        // 選択された日付に紐づく時間帯（reservation_slots）を取得
        $dateValue = DateValue::firstOrCreate(
            ['date' => $date], // 条件
            ['some_default_column' => 'default_value'] // 必要に応じて初期値を設定
        );
        $slots = ReservationSlot::where('date_value_id', $dateValue->id)->orderBy('slot_time')->get();

        return view('admin.nonmember_book', compact('date', 'slots'));
    }

    public function storeNonMember(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'child_name' => 'required|string|max:255',
            'is_under_3' => 'required|boolean',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'meal' => 'nullable|boolean',
            'snack' => 'nullable|boolean',
            'round_type' => 'required|string',
            'purpose' => 'required|string',
            'allergy' => 'nullable|string|max:255',
            'sibling_class' => 'nullable|string|max:255',
            'sibling_name' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:500',
        ]);
        
        $startTime = $request->input('start_time');
        $endTime = $request->input('end_time');
        $date      = $request->input('date');

        $dateValue = DateValue::firstOrCreate(['date' => $date]);

        NonmemberReservation::create([
            'child_name'          => $request->input('child_name'),
            'is_under_3'          => $request->input('is_under_3'),
            'date_value_id'       => $dateValue->id,
            'start_time'          => $startTime,
            'end_time'           => $endTime,
            'meal'               => $request->boolean('meal'),
            'snack'              => $request->boolean('snack'),
            'round_type'         => $request->input('round_type'),
            'purpose'            => $request->input('purpose'),
            'allergy'            => $request->input('allergy'),
            'sibling_class'      => $request->input('sibling_class'),
            'sibling_name'       => $request->input('sibling_name'),
            'note'               => $request->input('note'),
        ]);

        return redirect()->route('admin.reservation.list', ['date' => $request->input('date')])
            ->with('success', '非会員予約を登録しました');
    }


    //
}
