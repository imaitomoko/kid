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

    //private function calcFee($attendance)
    //{
      //  if (!$attendance) return 0;

        //return $attendance->feeItems
          //  ->sum(fn($afi) => $afi->feeItem->amount ?? 0);

    //}

    private function registerFeeItem(Attendance $attendance, string $category, bool $isEnd = false)
    {
        // 保育料は end 時に作成
        if ($isEnd && $category === 'basic' && $attendance->actual_start_time && $attendance->actual_end_time) {
            $start = \Carbon\Carbon::today()->setTimeFromTimeString($attendance->actual_start_time);
            $end = \Carbon\Carbon::today()->setTimeFromTimeString($attendance->actual_end_time);
            $minutes = $start->diffInMinutes($end);
            $hours = ceil($minutes / 60);

            // 保育料のカテゴリ決定
            if ($attendance->reservable_type === \App\Models\NonmemberReservation::class) {
                $type = (int)$attendance->reservable->is_under_3;

                if ($type === 2) {
                    $category = '誰でも通園';
                } else {
                    $category = $type === 1 ? '未満児保育' : '以上児保育';
                }
            } else {
                $child = $attendance->reservable->child ?? null;
                if ($child && $child->birthday) {
                    $birthday = \Carbon\Carbon::parse($child->birthday);
                    $attendanceDate = \Carbon\Carbon::parse($attendance->date ?? now());
                    $year = $attendanceDate->year;
                    if ($attendanceDate->month < 4) {
                        $year--;
                    }
                    $referenceDate = \Carbon\Carbon::create($year, 4, 2);
                    $ageAtReference = $birthday->diffInYears($referenceDate);

                    $category = $ageAtReference < 3 ? '未満児保育' : '以上児保育';
                } else {
                    $category = null;
                }
            }

            if ($category) {
                $basicFeeItem = FeeItem::where('category', $category)->first();
                if ($basicFeeItem) {
                    AttendanceFeeItem::updateOrCreate(
                        [
                            'attendance_id' => $attendance->id,
                            'fee_item_id'   => $basicFeeItem->id,
                        ],
                        ['amount' => $basicFeeItem->amount * $hours]
                    );
                }
            }
        }

    // meal/snack は start 時でも end 時でも作成
        if ($attendance->meal_used) {
            $mealItem = FeeItem::where('category', '給食')->first();
            if ($mealItem) {
                AttendanceFeeItem::updateOrCreate(
                    [
                        'attendance_id' => $attendance->id,
                        'fee_item_id'   => $mealItem->id,
                    ],
                    ['amount' => $mealItem->amount]
                );
            }
        }

        if ($attendance->snack_used) {
            $snackItem = FeeItem::where('category', 'おやつ')->first();
            if ($snackItem) {
                AttendanceFeeItem::updateOrCreate(
                    [
                        'attendance_id' => $attendance->id,
                        'fee_item_id'   => $snackItem->id,
                    ],
                    ['amount' => $snackItem->amount]
                );
            }
        }

    // total_fee 更新
        $total = AttendanceFeeItem::where('attendance_id', $attendance->id)->sum('amount');
        $attendance->update(['total_fee' => $total]);
    }

    private function removeFeeItem(Attendance $attendance, string $category)
    {
        $feeItem = \App\Models\FeeItem::where('category', $category)->first();

        if (!$feeItem) return;

        $attendance->feeItems()->where('fee_item_id', $feeItem->id)->delete();

        $attendance->load('feeItems');

        $attendance->update([
            'total_fee' => $attendance->feeItems->sum(fn($afi) => $afi->amount ?? 0)
        ]);
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

            $attendance = Attendance::updateOrCreate(
                [
                    'reservable_id' => $reservable->id,
                    'reservable_type' => $reservableType,
                ],
                [
                    'actual_start_time' => Carbon::now()->format('H:i'),
                    'meal_used' => $reservable->meal ? 'yes' : null,
                    'snack_used' => $reservable->snack ? 'yes' : null,
                    'total_fee' => 0,
                ]
            );
            $attendance->refresh();

            $this->registerFeeItem($attendance, 'basic', false);

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

            $mealUsed = collect($targetBlock)->contains(fn($b) => $b['reservation']->meal ?? false);
            $snackUsed = collect($targetBlock)->contains(fn($b) => $b['reservation']->snack ?? false);

            $attendance = Attendance::updateOrCreate(
                [
                    'reservable_id' => $targetBlock[0]['reservation']->id,
                    'reservable_type' => Reservation::class,
                ],
                [
                    'actual_start_time' => Carbon::now()->format('H:i:s'),
                    'actual_end_time' => null,
                    'meal_used' => $mealUsed ? 'yes' : null,
                    'snack_used' => $snackUsed ? 'yes' : null,
                    'total_fee' => 0,
                ]
            );

            $attendance->refresh();
            $this->registerFeeItem($attendance, 'basic', false);
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

            $attendance->actual_end_time = Carbon::now()->format('H:i');
            $attendance->meal_used = $reservable->meal ? 'yes' : null;
            $attendance->snack_used = $reservable->snack ? 'yes' : null;
            $attendance->save();

            $this->registerFeeItem($attendance, 'basic', true);
        
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

            // meal/snackも更新
            $reservations = Reservation::where('child_id', $childId)
                ->whereHas('slot.dateValue', fn($q) => $q->where('date', $date))
                ->get();

            $attendance->meal_used = $reservations->contains(fn($r) => $r->meal ?? false) ? 'yes' : null;
            $attendance->snack_used = $reservations->contains(fn($r) => $r->snack ?? false) ? 'yes' : null;

            $attendance->save();

            $attendance->refresh();
            $this->registerFeeItem($attendance, 'basic', true);
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

        $attendance->actual_start_time = $request->actual_start_time;
        $attendance->save();

        return back();
    }

    public function deleteStartTime(Request $request, $id)
    {
        $isNonmember = $request->input('nonmember') === '1';

        $reservable = $isNonmember 
            ? NonmemberReservation::findOrFail($id)
            : Reservation::findOrFail($id);

        $attendance = $reservable->attendance;
        if ($attendance) {
            $attendance->actual_start_time = null;
            $attendance->save();

            $this->removeFeeItem($attendance, '給食');
            $this->removeFeeItem($attendance, 'おやつ');
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

        $this->registerFeeItem($attendance, 'basic', true);

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
            $attendance->save();

            $this->removeFeeItem($attendance, '未満児保育');
            $this->removeFeeItem($attendance, '以上児保育');
        }

        return back();
    }

    public function meal(Request $request, $id)
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

        $attendance->meal_used = 'yes';
        $attendance->save();

        $reservable->meal = true;
        $reservable->save();

        $this->registerFeeItem($attendance, 'meal');

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
            $reservable = NonmemberReservation::with('dateValue')->findOrFail($id);
            $date = optional($reservable->dateValue)->date;
        } else {
            $reservable = Reservation::with('slot.dateValue')->findOrFail($id);
            $date = optional($reservable->slot->dateValue)->date;
        }

        $attendance = $reservable->attendance;
        if ($attendance) {
            $attendance->meal_used = null;
            $attendance->save();
            $this->removeFeeItem($attendance, '給食');
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

        $this->registerFeeItem($attendance, 'snack');

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
            $attendance->save();
            $this->removeFeeItem($attendance, 'おやつ');
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
        } else {
            $reservation = Reservation::with([
                'child',
                'child.user',
                'child.user.contacts',
                'child.siblings',
            ])->findOrFail($id);
        }

        return view('admin.book_detail', compact('reservation', 'isNonmember', 'date'));
    }

    //
}
