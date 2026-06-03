<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\NonmemberReservation;
use App\Models\Attendance;
use App\Models\AttendanceFeeItem;
use App\Models\FeeItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AdminListController extends Controller
{
    public function index ()
    {
        return view('admin.dashboard');
    }

    private function decideBasicCategory(Attendance $attendance): ?string
    {
       // 非会員
        if ($attendance->reservable_type === NonmemberReservation::class) {
            return match ((int)$attendance->reservable->is_under_3) {
                4 => '誰でも通園無償',
                3 => '誰でも通園減免',
                2 => '誰でも通園',
                1 => '未満児保育',
                default => '以上児保育',
            };
        }

        // 会員
        $child = $attendance->reservable->child ?? null;
        if (!$child || !$child->birthday) return null;

        $attendanceDate = Carbon::parse($attendance->date ?? now());
        $year = $attendanceDate->month < 4
            ? $attendanceDate->year - 1
            : $attendanceDate->year;

        $referenceDate = Carbon::create($year, 4, 2);

        return Carbon::parse($child->birthday)->diffInYears($referenceDate) < 3
            ? '未満児保育'
            : '以上児保育';
    }

    private function recalculateFee(Attendance $attendance): void
    {
        // 一旦全削除
        $attendance->feeItems()->delete();
        $attendance->total_fee = 0;

        // 開始・終了が揃っていなければ料金なし
        if (!$attendance->actual_start_time || !$attendance->actual_end_time) {
            $attendance->save();
            return;
        }

        $start = Carbon::today()->setTimeFromTimeString($attendance->actual_start_time);
        $end   = Carbon::today()->setTimeFromTimeString($attendance->actual_end_time);

        if ($end->lte($start)) {
            $attendance->save();
            return;
        }

         /** ---- 保育料 ---- */
        $minutes = $start->diffInMinutes($end);
        $category = $this->decideBasicCategory($attendance);

        if ($category) {
            $item = FeeItem::where('category', $category)->first();
            if ($item) {
                $count = match ($item->unit) {
                    '30分単位' => ceil($minutes / 30),
                    '1時間単位' => ceil($minutes / 60),
                    '1回単位' => 1,
                    default => 1,
                };

                AttendanceFeeItem::create([
                    'attendance_id' => $attendance->id,
                    'fee_item_id'   => $item->id,
                    'amount'        => $item->amount * $count,
                ]);
            }
        }

        /** ---- 給食 ---- */
        if ($attendance->meal_used) {
            $meal = FeeItem::where('category', '給食')->first();
            if ($meal) {
                AttendanceFeeItem::create([
                    'attendance_id' => $attendance->id,
                    'fee_item_id'   => $meal->id,
                    'amount'        => $meal->amount,
                ]);
            }
        }

        /** ---- おやつ ---- */
        if ($attendance->snack_used) {
            $snack = FeeItem::where('category', 'おやつ')->first();
            if ($snack) {
                AttendanceFeeItem::create([
                    'attendance_id' => $attendance->id,
                    'fee_item_id'   => $snack->id,
                    'amount'        => $snack->amount,
                ]);
            }
        }

         // 合計
        $attendance->total_fee =
            $attendance->feeItems()->sum('amount');

        $attendance->save();
    }

    public function list(Request $request)
    {
        $date = $request->input('date')
            ? Carbon::parse($request->input('date'))
            : Carbon::today();

        $reservations = Reservation::with([
            'child',
            'slot',
            'slot.dateValue',
            'attendance.feeItems.feeItem'
        ])
        ->whereHas('slot.dateValue', fn($q) => $q->where('date', $date))
        ->get();

        $reservations = $reservations
            ->groupBy('child_id')
            ->map(function ($group) {
    
                $timeRanges = $group->map(function ($r) {
                    $slotTime = $r->slot->slot_time ?? null;
                    if (!$slotTime) {
                        \Log::debug('slot_timeが空', ['reservation_id' => $r->id]);
                        return null;
                    }

                 // slot_time は「H:i:s」形式（例：8:00:00）
                    try {
                        $start = \Carbon\Carbon::createFromFormat('H:i:s', $slotTime);
                    } catch (\Exception $e) {
                        \Log::warning('slot_time形式不正', ['slot_time' => $slotTime]);
                        return null;
                    }

                    $end = $start->copy()->addMinutes(30); // 30分スロット

                    return [
                        'start' => $start->format('H:i'),
                        'end' => $end->format('H:i'),
                    ];
                })->filter()->sortBy('start')->values();

                $merged = [];
                foreach ($timeRanges as $range) {
                    if (empty($merged)) {
                        $merged[] = $range;
                        continue;
                    }

                $last = &$merged[count($merged) - 1];
                // 直前の終了時刻と今回の開始時刻が同じなら結合
                if ($last['end'] === $range['start']) {
                    $last['end'] = $range['end'];
                } else {
                    $merged[] = $range;
                }
            }

            // 代表データを取得
            $first = $group->first();
            $first->merged_times = $merged;
            $first->combined_attendances = $group->pluck('attendance')->filter();

            return $first;
        })
        ->values();

        $nonmemberReservations = NonmemberReservation::with([
            'attendance.feeItems.feeItem'
        ])
        ->whereHas('dateValue', fn($q) => $q->where('date', $date))
        ->get();

        $individualFees = [];

        foreach ($reservations as $reservation) {
            $this->ensureAttendance($reservation);
            $individualFees[$reservation->id] = $reservation->attendance
                ? $reservation->attendance->total_fee
                : 0;
        }

        foreach ($nonmemberReservations as $reservation) {
            $this->ensureAttendance($reservation);
            $individualFees['nonmember_'.$reservation->id] = $reservation->attendance
                ? $reservation->attendance->total_fee
                : 0;
        }

        $totalFee = $reservations->sum(fn($r) => $r->attendance->total_fee ?? 0)
            + $nonmemberReservations->sum(fn($r) => $r->attendance->total_fee ?? 0);

        $accountedTotal = $reservations->sum(fn($r) =>
            ($r->attendance && $r->attendance->accounted) ? $r->attendance->total_fee : 0)
            + $nonmemberReservations->sum(fn($r) =>
            ($r->attendance && $r->attendance->accounted) ? $r->attendance->total_fee : 0);

        return view('admin.book_list', compact('reservations', 'nonmemberReservations', 'totalFee', 'accountedTotal', 'individualFees', 'date'));
    }

    private function ensureAttendance($reservation)
    {
        if (!$reservation->attendance) {
            $reservation->attendance()->create([
                'meal_used' => $reservation->meal ? 'yes' : null,
                'snack_used' => $reservation->snack ? 'yes' : null,
            ]);
        } else {
            $attendance = $reservation->attendance;
            $updated = false;

            if ($reservation->meal && !$attendance->meal_used) {
                $attendance->meal_used = 'yes';
                $updated = true;
            }
            if ($reservation->snack && !$attendance->snack_used) {
                $attendance->snack_used = 'yes';
                $updated = true;
            }
            if ($updated) {
                $attendance->save();
            }
        }
    }

    public function start(Request $request, $id)
    {
        $isNonmember = $request->input('nonmember') === '1';

        if ($isNonmember) {
            $reservable = NonmemberReservation::findOrFail($id);
            $reservableType = NonmemberReservation::class;

            $attendance = Attendance::firstOrNew([
                'reservable_id' => $reservable->id,
                'reservable_type' => $reservableType,
            ]);

            // 初回作成時のみ meal / snack をコピー
            if (!$attendance->exists) {
                $attendance->meal_used = $reservable->meal ? 'yes' : null;
                $attendance->snack_used = $reservable->snack ? 'yes' : null;
            }

            // actual_start_time は毎回上書き
            $attendance->actual_start_time = Carbon::now()->format('H:i');
            $attendance->save();

            return back();

        } else {
            $reservable = Reservation::findOrFail($id);
            $childId = $reservable->child_id;
            $date = $reservable->slot->dateValue->date;

            $reservations = Reservation::with('slot.dateValue')
                ->where('child_id', $childId)
                ->whereHas('slot.dateValue', fn($q) => $q->where('date', $date))
                ->get()
                ->sortBy(fn($r) => $r->slot->slot_time);

                // 連続ブロックに分ける
            $mergedBlocks = [];
            $currentBlock = [];

            foreach ($reservations as $r) {
                $start = Carbon::createFromFormat('H:i:s', $r->slot->slot_time);
                $end = $start->copy()->addMinutes(30);

                if (empty($currentBlock)) {
                    $currentBlock[] = ['reservation' => $r, 'start' => $start, 'end' => $end];
                    continue;
                }

                $last = end($currentBlock);
                if ($last['end']->eq($start)) {
                    $currentBlock[] = ['reservation' => $r, 'start' => $start, 'end' => $end];
                } else {
                    $mergedBlocks[] = $currentBlock;
                    $currentBlock = [['reservation' => $r, 'start' => $start, 'end' => $end]];
                }
            }
            if (!empty($currentBlock)) $mergedBlocks[] = $currentBlock;

             // 今クリックされた reservation が属するブロックを取得
            $targetBlock = collect($mergedBlocks)->first(function ($block) use ($reservable) {
                return collect($block)->contains(fn($b) => $b['reservation']->id === $reservable->id);
            });
     
            if (!$targetBlock) {
                return back()->with('error', '対象予約ブロックが見つかりません');
            }

            $mealUsed  = collect($targetBlock)->contains(fn($b) => $b['reservation']->meal ?? false);
            $snackUsed = collect($targetBlock)->contains(fn($b) => $b['reservation']->snack ?? false);


            $attendance = Attendance::firstOrNew([
                'reservable_id' => $targetBlock[0]['reservation']->id,
                'reservable_type' => Reservation::class,
            ]);

            if (!$attendance->exists) {
                $mealUsed  = collect($targetBlock)->contains(fn($b) => $b['reservation']->meal ?? false);
                $snackUsed = collect($targetBlock)->contains(fn($b) => $b['reservation']->snack ?? false);

                $attendance->meal_used  = $mealUsed ? 'yes' : null;
                $attendance->snack_used = $snackUsed ? 'yes' : null;
            }

            // actual_start_time は毎回上書き
            $attendance->actual_start_time = Carbon::now()->format('H:i:s');
            $attendance->save();
        }

        return back()->with('success', '利用を開始しました');
    }

    public function end(Request $request, $id)
    {
        $isNonmember = $request->input('nonmember') === '1';

        if ($isNonmember) {
            $reservable = NonmemberReservation::findOrFail($id);
            $reservableType = NonmemberReservation::class;
            $attendance = Attendance::where('reservable_id', $reservable->id)
                ->where('reservable_type', $reservableType)
                ->first();

            if (!$attendance || !$attendance->actual_start_time) {
                return back()->with('error', '利用開始されていません。');
            }

            if (!$attendance->actual_end_time) {
                $attendance->actual_end_time = Carbon::now()->format('H:i');
                $attendance->save();

                $this->recalculateFee($attendance);
            }
        
        } else {
            $reservable = Reservation::findOrFail($id);
            $reservableType = Reservation::class;
            $childId = $reservable->child_id;
            $date = $reservable->slot->dateValue->date;

            $reservationIds = Reservation::where('child_id', $childId)
                ->whereHas('slot.dateValue', function ($q) use ($date) {
                    $q->where('date', $date);
                })
                ->pluck('id');

            $attendance = Attendance::where('reservable_type', Reservation::class)
                ->whereIn('reservable_id', $reservationIds)
                ->first();

            if (!$attendance || !$attendance->actual_start_time) {
                return back()->with('error', '利用開始されていません。');
            }

            $attendance->actual_end_time = Carbon::now()->format('H:i');

            $attendance->save();

            $this->recalculateFee($attendance);
        }

        return back()->with('success', '利用終了しました');
    }

    public function updateStartTime(Request $request, $id)
    {
        $request->validate(['actual_start_time' => 'required']);
        $isNonmember = $request->input('nonmember') === '1';

        if ($isNonmember) {
            $reservable = NonmemberReservation::findOrFail($id);
        } else {
            $reservable = Reservation::findOrFail($id);
        }

        $attendance = $reservable->attendance;
        if (!$attendance) {
            return back()->with('error', 'データが存在しません。');
        }

        if ($attendance->accounted) {
            return back()->with('error', '会計済みのため編集できません。');
        }

        if ($attendance->actual_end_time) {
            if ($request->actual_start_time >= $attendance->actual_end_time) {
                return back()->with('error', '開始時刻は終了時刻より前である必要があります。');
            }
        }

        $attendance->actual_start_time = $request->actual_start_time;
        $attendance->save();

        $this->recalculateFee($attendance);

        return back();
    }

    public function deleteStartTime(Request $request, $id)
    {
        $isNonmember = $request->input('nonmember') === '1';

        $reservable = $isNonmember 
            ? NonmemberReservation::findOrFail($id)
            : Reservation::findOrFail($id);

        $attendance = $reservable->attendance;

        if ($attendance && $attendance->actual_end_time) {
            return back()->with('error', '終了時刻があるため開始時刻は削除できません。');
        }

        if ($attendance) {
            $attendance->actual_start_time = null;
            $attendance->total_fee = 0;

            $attendance->save();

            $this->recalculateFee($attendance);
        }
        
        return back();
    }

    public function updateEndTime(Request $request, $id)
    {
        $request->validate(['actual_end_time' => 'required']);
        $isNonmember = $request->input('nonmember') === '1';

        if ($isNonmember) {
            $reservable = NonmemberReservation::findOrFail($id);
        } else {
            $reservable = Reservation::findOrFail($id);
        }

        $attendance = $reservable->attendance;
        if (!$attendance) {
            return back()->with('error', 'データが存在しません。');
        }

        $attendance->actual_end_time = $request->actual_end_time;
        $attendance->save();

        $this->recalculateFee($attendance);

        return back();
    }

    public function deleteEndTime(Request $request, $id)
    {
        $isNonmember = $request->input('nonmember') === '1';

        $reservable = $isNonmember 
            ? NonmemberReservation::findOrFail($id)
            : Reservation::findOrFail($id);


        $attendance = $reservable->attendance;
        if ($attendance) {
            $attendance->actual_end_time = null;
            $attendance->total_fee = 0;
            $attendance->save();

            $this->recalculateFee($attendance);

        }

        return back();
    }

    public function meal(Request $request, $id)
    {
        $isNonmember = $request->input('nonmember') === '1';

        if ($isNonmember) {
            $reservable = NonmemberReservation::with('dateValue', 'attendance')->findOrFail($id);
            $date = optional($reservable->dateValue)->date;
        } else {
            $reservable = Reservation::with('slot.dateValue', 'attendance')->findOrFail($id);
            $date = optional($reservable->slot->dateValue)->date;
        }

        $attendance = $reservable->attendance ?? Attendance::create([
            'reservable_id' => $reservable->id,
            'reservable_type' => get_class($reservable),
            'actual_start_time' => null,
            'actual_end_time' => null,
        ]);

        $attendance->meal_used = 'yes';
        $attendance->save();

        $reservable->meal = true;
        $reservable->save();

        if ($attendance->actual_end_time) {
            $this->recalculateFee($attendance);
        }

        if (!empty($date)) {
            return redirect()->route('book.list', [
                'date' => Carbon::parse($date)->format('Y-m-d'),
            ]);
        }

        return back();
    }

    public function deleteMeal(Request $request, $id)
    {
        $isNonmember = $request->input('nonmember') === '1';

        if ($isNonmember) {
            $reservable = NonmemberReservation::with('dateValue', 'attendance')->findOrFail($id);
            $date = optional($reservable->dateValue)->date;
        } else {
            $reservable = Reservation::with('slot.dateValue', 'attendance')->findOrFail($id);
            $date = optional($reservable->slot->dateValue)->date;
        }

        $attendance = $reservable->attendance;
        if ($attendance) {
            $attendance->meal_used = null;
            $attendance->save();
            $this->recalculateFee($attendance);
        }

        $reservable->meal = false;
        $reservable->save();

        if (!empty($date)) {
            return redirect()->route('book.list', [
                'date' => Carbon::parse($date)->format('Y-m-d'),
            ]);
        }

        return back();
    }

    public function snack(Request $request, $id)
    {
        $isNonmember = $request->input('nonmember') === '1';

        if ($isNonmember) {
            $reservable = NonmemberReservation::with('dateValue')->findOrFail($id);
            $date = optional($reservable->dateValue)->date;
        } else {
            $reservable = Reservation::with('slot.dateValue')->findOrFail($id);
            $date = optional($reservable->slot->dateValue)->date;
        }

        $attendance = $reservable->attendance ?? Attendance::create([
            'reservable_id' => $reservable->id,
            'reservable_type' => get_class($reservable),
            'actual_start_time' => null,
            'actual_end_time' => null,
        ]);

        $attendance->snack_used = 'yes';
        $attendance->save();

        $reservable->snack = true;
        $reservable->save();

        if ($attendance->actual_end_time) {
            $this->recalculateFee($attendance);
        }


        if (!empty($date)) {
            return redirect()->route('book.list', [
                'date' => Carbon::parse($date)->format('Y-m-d'),
            ]);
        }

        return back();    }

    public function deleteSnack(Request $request, $id)
    {
        $isNonmember = $request->input('nonmember') === '1';

        if ($isNonmember) {
            $reservable = NonmemberReservation::with('dateValue')->findOrFail($id);
            $date = optional($reservable->dateValue)->date;
        } else {
            $reservable = Reservation::with('slot.dateValue')->findOrFail($id);
            $date = optional($reservable->slot->dateValue)->date;
        }

        $attendance = $reservable->attendance;
        if ($attendance) {
            $attendance->snack_used = null;
            $attendance->total_fee = 0;
            $attendance->save();
            $this->recalculateFee($attendance);
        }

        $reservable->snack = false;
        $reservable->save();

        if (!empty($date)) {
            return redirect()->route('book.list', [
                'date' => Carbon::parse($date)->format('Y-m-d'),
            ]);
        }

        return back();
    }

    public function updateAccounted(Request $request)
    {
        $attendance = Attendance::find($request->input('attendance_id'));
        if ($attendance) {
            $attendance->update(['accounted' => $request->boolean('accounted')]);
        }
        return back();
    }

    public function detail($id, Request $request)
    {
        $isNonmember = $request->input('isNonmember');
        $date = $request->input('date');

        if ($isNonmember) {
            $reservation = NonmemberReservation::findOrFail($id);
            $typeName = match ($reservation->is_under_3) {
                4 => '誰でも通園無償',
                3 => '誰でも通園減免',
                2 => '誰でも通園',
                1 => '３歳未満児',
                default => '3歳以上児',
            };
        } else {
            $reservation = Reservation::with([
                'child',
                'child.user',
                'child.user.contacts',
                'child.siblings',
            ])->findOrFail($id);
        }

        return view('admin.book_detail', compact('reservation', 'isNonmember', 'date','typeName'));
    }

    //
}
