<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Reservation;
use App\Models\NonmemberReservation;
use App\Models\ReservationSlot;
use App\Models\DateValue;
use App\Models\Child;


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
            $start = $group->min(fn($r) => $r->slot->slot_time);
            $end   = $group->max(fn($r) => $r->slot->slot_time);
            $end   = \Carbon\Carbon::parse($end)->addMinutes(30)->format('H:i'); // 30分枠想定

            $reservations->push([
                'is_member'  => true,
                'child_name' => $group->first()->child->child_name ?? '',
                'time'       => \Carbon\Carbon::parse($start)->format('H:i') . ' ~ ' . $end,
                'id'         => $group->first()->id,
                'child_id'   => $childId,
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
                'child_id'   => null, // 会員とキーを揃える
                'start_time' => $reservation->start_time,
                'end_time'   => $reservation->end_time,
            ]);
        }

        $reservations = $reservations->sortBy('time')->values();


        return view('admin.admin_reservation_list', compact('date', 'reservations'));
    }

    public function cancel(Request $request)
    {
        $childId = $request->input('child_id');   // 会員予約キャンセル用
        $date    = $request->input('date');       // 会員・非会員共通
        $startTime = $request->input('start_time'); // 非会員キャンセル用
        $endTime   = $request->input('end_time');   // 非会員キャンセル用

        $deleted = false;

        // 会員予約キャンセル（子どもID＋日付でまとめて）
        if ($childId) {
            $memberReservations = Reservation::where('child_id', $childId)
                ->whereHas('slot.dateValue', fn($q) => $q->whereDate('date', $date))
                ->get();

            foreach ($memberReservations as $r) {
                if ($r->slot) {
                    $r->slot->increment('capacity', 1); // 予約枠を戻す
                }
                $r->delete();
            }
            $deleted = true;
        }

        // 非会員予約キャンセル（date_value_id + start/end_timeで絞る）
        if (!$childId && $startTime && $endTime) {
            $count = NonmemberReservation::whereHas('dateValue', fn($q) => $q->whereDate('date', $date))
                ->where('start_time', $startTime)
                ->where('end_time', $endTime)
                ->delete();

            if ($count > 0) $deleted = true;
        }

        return $deleted
            ? back()->with('success', '予約をキャンセルしました。')
            : back()->with('error', '該当する予約は見つかりませんでした。');

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
            'is_under_3' => 'required|in:0,1,2,3,4',
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

    public function createMemberProxy(Request $request, $date)
    {
        $children = collect();

        if ($request->filled('child_search')) {
            $search = $request->input('child_search');
            $children = Child::where('child_name', 'like', "%{$search}%")->get();
        }

        $dateValue = DateValue::firstOrCreate(['date' => $date]);
        $slots = ReservationSlot::where('date_value_id', $dateValue->id)
            ->withCount('reservations')
            ->orderBy('slot_time')
            ->get();

        return view('admin.member_book', compact('date', 'children', 'slots'));
    }

    public function storeMemberProxy(Request $request)
    {
        $validated = $request->validate([
            'child_id' => 'required|exists:children,id',
            'reservation_slot_ids' => 'required|array',
            'reservation_slot_ids.*' => 'exists:reservation_slots,id',
            'meal' => 'nullable|boolean',
            'snack' => 'nullable|boolean',
            'round_type' => 'required|string',
            'purpose' => 'required|string',
            'note' => 'nullable|string|max:500',
            'date' => 'required|date',
        ]);

        $childId = $validated['child_id'];

        foreach ($validated['reservation_slot_ids'] as $slotId) {
        // 対象スロットを取得
            $slot = \App\Models\ReservationSlot::find($slotId);

            // 空きがあるか確認
            if ($slot && $slot->capacity > 0) {

                // 二重予約防止チェック
                $already = \App\Models\Reservation::where('child_id', $childId)
                    ->where('reservation_slot_id', $slotId)
                    ->exists();

                if (!$already) {
                    // 予約登録
                    \App\Models\Reservation::create([
                        'child_id' => $childId,
                        'reservation_slot_id' => $slotId,
                        'meal' => $request->input('meal', 0),
                        'snack' => $request->input('snack', 0),
                        'round_type' => $validated['round_type'],
                        'purpose' => $validated['purpose'],
                        'note' => $validated['note'] ?? null,
                    ]);

                    // 空き枠を1減らす
                    $slot->decrement('capacity', 1);
                }
            }
        }

        return redirect()->route('admin.reservation.list', ['date' => $validated['date']])
            ->with('success', '会員予約を登録しました。');
    }


    //
}
