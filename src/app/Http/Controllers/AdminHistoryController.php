<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\Reservation;
use App\Models\NonmemberReservation;
use App\Models\FeeItem;

class AdminHistoryController extends Controller
{
    public function childSearch(Request $request)
    {
        $term = $request->input('term');

        $memberNames = \App\Models\Child::where('child_name', 'like', "%{$term}%")->pluck('child_name');
        $nonMemberNames = \App\Models\NonmemberReservation::where('child_name', 'like', "%{$term}%")->pluck('child_name');

        $names = $memberNames->merge($nonMemberNames)->unique()->values();

        return response()->json($names);
    }

    public function history(Request $request)
    {
        // 月指定
        $month = $request->input('month') ? Carbon::parse($request->input('month')) : Carbon::now();

        // 月初と月末
        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        // 検索用子ども名
        $childName = $request->input('child_name');

        // Attendancesを取得
        $attendances = Attendance::with(['feeItems.feeItem', 'reservable'])
            ->where(function ($query) use ($startOfMonth, $endOfMonth) {
            $query
                // 会員予約
                ->whereHasMorph('reservable', [Reservation::class], function ($q) use ($startOfMonth, $endOfMonth) {
                    $q->whereHas('slot.dateValue', function ($q2) use ($startOfMonth, $endOfMonth) {
                        $q2->whereBetween('date', [$startOfMonth, $endOfMonth]);
                    });
                })
                         // 非会員の条件
                    ->orWhereHasMorph('reservable', [NonmemberReservation::class], function ($q) use ($startOfMonth, $endOfMonth) {
                    $q->whereHas('dateValue', function ($q2) use ($startOfMonth, $endOfMonth) {
                        $q2->whereBetween('date', [$startOfMonth, $endOfMonth]);
                    });
                });
            })
             ->when($childName, function ($query) use ($childName) {
            $query->where(function ($q) use ($childName) {
                // 会員
                $q->whereHasMorph('reservable', [Reservation::class], function ($q2) use ($childName) {
                    $q2->whereHas('child', function ($q3) use ($childName) {
                        $q3->where('child_name', 'like', "%{$childName}%");
                    });
                })
                // 非会員
                ->orWhereHasMorph('reservable', [NonmemberReservation::class], function ($q2) use ($childName) {
                    $q2->where('child_name', 'like', "%{$childName}%");
                });
            });
        })
            ->orderBy('actual_start_time')
            ->get();



        $rows = [];
        $totalDays = $attendances->count();
        $totalAll = 0;
        $totalNursery = 0;
        $totalMeal = 0;
        $totalSnack = 0;

        foreach ($attendances as $attendance) {
            $feeItems = $attendance->feeItems;

            $nurseryAmount = $feeItems
                ->filter(fn($f) => $f->feeItem && in_array($f->feeItem->category, ['未満児保育', '以上児保育']))
                ->sum('amount');

            $mealAmount = $feeItems
                ->filter(fn($f) => $f->feeItem && $f->feeItem->category === '給食')
                ->sum('amount');

            $snackAmount = $feeItems
                ->filter(fn($f) => $f->feeItem && $f->feeItem->category === 'おやつ')
                ->sum('amount');
    
            $subtotal = $nurseryAmount + $mealAmount + $snackAmount;

            $rows[] = [
                'date' => $attendance->reservable_type === Reservation::class 
                    ? optional($attendance->reservable->slot->dateValue)->date
                    : optional($attendance->reservable->dateValue)->date,
                'time' => Carbon::parse($attendance->actual_start_time)->format('H:i') . '～' . Carbon::parse($attendance->actual_end_time)->format('H:i'),
                'meal' => $mealAmount,
                'snack' => $snackAmount,
                'nursery' => $nurseryAmount,
                'subtotal' => $subtotal,
            ];
            


            $totalAll += $subtotal;
            $totalNursery += $nurseryAmount;
            $totalMeal += $mealAmount;
            $totalSnack += $snackAmount;
        }

        return view('admin.history', compact('month', 'rows', 'totalAll', 'totalDays', 'totalNursery', 'totalMeal', 'totalSnack', 'childName'));
    }
    //
}
