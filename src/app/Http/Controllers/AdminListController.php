<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\NonmemberReservation;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminListController extends Controller
{
    public function index ()
    {
        return view('admin.dashboard');
    }

    private function calcFee($attendance)
    {
        return optional(optional($attendance)->feeItems)->sum(fn($f) => $f->feeItem->amount ?? 0);
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

        return back();
    }

    public function meal(Request $request, $id)
    {
        $isNonmember = $request->input('nonmember') === '1';

        if ($isNonmember) {
            $reservable = NonmemberReservation::findOrFail($id);
        } else {
            $reservable = Reservation::findOrFail($id);
        }

        $attendance = $reservable->attendance ?? Attendance::create([
            'reservable_id' => $reservable->id,
            'reservable_type' => get_class($reservable),
            'actual_start_time' => null,
            'actual_end_time' => null,
        ]);

        $attendance->meal_used = 'yes';
        $attendance->save();

        return back();
    }

    public function deleteMeal(Request $request, $id)
    {
        $isNonmember = $request->input('nonmember') === '1';

        if ($isNonmember) {
            $reservable = NonmemberReservation::findOrFail($id);
        } else {
            $reservable = Reservation::findOrFail($id);
        }

        $attendance = $reservable->attendance;
        if ($attendance) {
            $attendance->meal_used = null;
            $attendance->save();
        }

        return back();
    }

    public function snack(Request $request, $id)
    {
        $isNonmember = $request->input('nonmember') === '1';

        if ($isNonmember) {
            $reservable = NonmemberReservation::findOrFail($id);
        } else {
            $reservable = Reservation::findOrFail($id);
        }

        $attendance = $reservable->attendance ?? Attendance::create([
            'reservable_id' => $reservable->id,
            'reservable_type' => get_class($reservable),
            'actual_start_time' => null,
            'actual_end_time' => null,
        ]);

        $attendance->snack_used = 'yes';
        $attendance->save();

        return back();
    }

    public function deleteSnack(Request $request, $id)
    {
        $isNonmember = $request->input('nonmember') === '1';

        if ($isNonmember) {
            $reservable = NonmemberReservation::findOrFail($id);
        } else {
            $reservable = Reservation::findOrFail($id);
        }

        $attendance = $reservable->attendance;
        if ($attendance) {
            $attendance->snack_used = null;
            $attendance->save();
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
