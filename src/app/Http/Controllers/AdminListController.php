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

    private function calcFee($attendance)
    {
        return optional($attendance->feeItems)->sum(fn($f) => $f->feeItem->amount ?? 0);
    }

    private function registerFeeItem(Attendance $attendance, string $category)
    {
    // 対応する料金マスタを取得
        $feeItem = \App\Models\FeeItem::where('category', $category)->first();

        if (!$feeItem) {
            return; // 該当する料金設定がなければスキップ
        }

        if ($attendance->actual_start_time && $attendance->actual_end_time) {
            $start = \Carbon\Carbon::today()->setTimeFromTimeString($attendance->actual_start_time);
            $end = \Carbon\Carbon::today()->setTimeFromTimeString($attendance->actual_end_time);
            $minutes = $start->diffInMinutes($end);
            $hours = ceil($minutes / 60); // 60分未満でも1時間、端数は切り上げ
        } else {
            $hours = 0;
        }

        if ($category === 'basic') {
            if ($attendance->reservable_type === \App\Models\NonmemberReservation::class) {
                $isUnder3 = (bool)$attendance->reservable->is_under_3;
                $category = $isUnder3 ? '未満児保育' : '以上児保育';

            } else {
             // 会員: childの誕生日から判定
                $child = $attendance->reservable->child ?? null;
                if ($child && $child->birthday) {
                    $age = \Carbon\Carbon::parse($child->birthday)->age;
                    $category = $age < 3 ? '未満児保育' : '以上児保育';

                    \Log::info('会員の年齢区分判定', [
                        'child_id' => $child->id ?? null,
                        'birthday' => $child->birthday,
                        'age' => $age,
                        'category' => $category,
                    ]);
                } else {
                   $category = '以上児保育'; // デフォルト3歳以上
                }
            }

            $feeItem = \App\Models\FeeItem::where('category', $category)->first();
            if (!$feeItem) return;
        }

        $attendanceFeeItem = $attendance->items()->where('fee_item_id', $feeItem->id)     ->first();
        $amount = $feeItem->amount * $hours;

        if ($attendanceFeeItem) {
            $attendanceFeeItem->update(['amount' => $amount]);
        } else {
            $attendance->items()->create([
                'fee_item_id' => $feeItem->id,
                'amount' => $amount,
            ]);
        }

        if ($attendance->meal_used) {
            $mealItem = \App\Models\FeeItem::where('category', '給食')->first();
            if ($mealItem) {
                $attendance->items()->updateOrCreate(
                    ['fee_item_id' => $mealItem->id],
                    ['amount' => $mealItem->amount]
                );
            }
        }

        if ($attendance->snack_used) {
            $snackItem = \App\Models\FeeItem::where('category', 'おやつ')->first();
            if ($snackItem) {
                $attendance->items()->updateOrCreate(
                    ['fee_item_id' => $snackItem->id],
                    ['amount' => $snackItem->amount]
                );
            }
        }

        $attendance->load('items');

        $total = $attendance->items->sum(fn($item) => $item->amount ?? 0);
        $attendance->total_fee = $total;
        $attendance->save();
    }

    private function removeFeeItem(Attendance $attendance, string $category)
    {
        $feeItem = \App\Models\FeeItem::where('category', $category)->first();

        if ($feeItem) {
            $attendance->items()->where('fee_item_id', $feeItem->id)->delete();
        }

        $attendance->total_fee = $attendance->feeItems->sum(fn($f) => $f->feeItem->amount ?? 0);
        $attendance->save();
    }

    public function list(Request $request)
    {
        $date = $request->input('date')
            ? Carbon::parse($request->input('date'))
            : Carbon::today();

        $reservations = Reservation::with([
            'child',
            'slot.dateValue',
            'attendance.feeItems.feeItem'
        ])
        ->whereHas('slot.dateValue', fn($q) => $q->where('date', $date))
        ->get();

        $nonmemberReservations = NonmemberReservation::with([
            'attendance.feeItems.feeItem'
        ])
        ->whereHas('dateValue', fn($q) => $q->where('date', $date))
        ->get();

        foreach ($reservations as $reservation) {
            $this->ensureAttendance($reservation);
        }
        foreach ($nonmemberReservations as $reservation) {
            $this->ensureAttendance($reservation);
        }

        $totalFee = $reservations->sum(fn($r) => $this->calcFee($r->attendance))
            + $nonmemberReservations->sum(fn($r) => $this->calcFee($r->attendance));

        $accountedTotal = $reservations->sum(fn($r) =>
            ($r->attendance && $r->attendance->accounted) ? $this->calcFee($r->attendance) : 0
        ) + $nonmemberReservations->sum(fn($r) =>
            ($r->attendance && $r->attendance->accounted) ? $this->calcFee($r->attendance) : 0
        );


        return view('admin.book_list', compact('reservations', 'nonmemberReservations', 'totalFee', 'accountedTotal', 'date'));
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
        } else {
            $reservable = Reservation::findOrFail($id);
            $reservableType = Reservation::class;
        }

        $attendance = Attendance::firstOrNew([
            'reservable_id' => $reservable->id,
            'reservable_type' => $reservableType,
        ]);

        $attendance->actual_start_time = Carbon::now()->format('H:i');
        $attendance->save();

        return back();
    }

    public function end(Request $request, $id)
    {
        $isNonmember = $request->input('nonmember') === '1';

        if ($isNonmember) {
            $reservable = NonmemberReservation::findOrFail($id);
            $reservableType = NonmemberReservation::class;
        } else {
            $reservable = Reservation::findOrFail($id);
            $reservableType = Reservation::class;
        }

        $attendance = Attendance::where('reservable_id', $reservable->id)
            ->where('reservable_type', $reservableType)
            ->first();

        if (!$attendance || !$attendance->actual_start_time) {
            return back()->with('error', '利用開始されていません。');
        }

        $attendance->actual_end_time = Carbon::now()->format('H:i');
        $attendance->save();

        $this->registerFeeItem($attendance, 'basic');

        return back();
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

        $attendance->actual_start_time = $request->actual_start_time;
        $attendance->save();

        return back();
    }

    public function deleteStartTime(Request $request, $id)
    {
        $isNonmember = $request->input('nonmember') === '1';

        if ($isNonmember) {
            $reservable = NonmemberReservation::findOrFail($id);
        } else {
            $reservable = Reservation::findOrFail($id);
        }

        $attendance = $reservable->attendance;
        if ($attendance) {
            $attendance->actual_start_time = null;
            $attendance->save();
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

        $this->registerFeeItem($attendance, 'basic');

        return back();
    }

    public function deleteEndTime(Request $request, $id)
    {
        $isNonmember = $request->input('nonmember') === '1';

        if ($isNonmember) {
            $reservable = NonmemberReservation::findOrFail($id);
        } else {
            $reservable = Reservation::findOrFail($id);
        }

        $attendance = $reservable->attendance;
        if ($attendance) {
            $attendance->actual_end_time = null;
            $attendance->save();
        }

        $this->removeFeeItem($attendance, 'basic');

        return back();
    }

    public function meal(Request $request, $id)
    {
        $isNonmember = $request->input('nonmember') === '1';

        if ($isNonmember) {
            $reservable = NonmemberReservation::with('dateValue')->findOrFail($id);
            $date = optional($reservable->dateValue)->date;
        } else {
            $reservable = Reservation::with('reservationSlot.dateValue')->findOrFail($id);
            $date = optional($reservable->reservationSlot->dateValue)->date;
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
            $reservable = Reservation::with('reservationSlot.dateValue')->findOrFail($id);
            $date = optional($reservable->reservationSlot->dateValue)->date;
        }

        $attendance = $reservable->attendance;
        if ($attendance) {
            $attendance->meal_used = null;
            $attendance->save();
            $this->removeFeeItem($attendance, 'meal');
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
            $reservable = Reservation::with('reservationSlot.dateValue')->findOrFail($id);
            $date = optional($reservable->reservationSlot->dateValue)->date;
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
            $reservable = Reservation::with('reservationSlot.dateValue')->findOrFail($id);
            $date = optional($reservable->reservationSlot->dateValue)->date;
        }

        $attendance = $reservable->attendance;
        if ($attendance) {
            $attendance->snack_used = null;
            $attendance->save();
            $this->removeFeeItem($attendance, 'snack');
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

    //
}
