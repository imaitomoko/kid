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
    public function index(){
        return view('admin.history_index');
    }
    
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

        $childName = $request->input('child_name');

        if (empty($childName)) {
            return view('admin.history', [
                'month' => $month,
                'rows' => [],
                'totalAll' => 0,
                'totalDays' => 0,
                'totalNursery' => 0,
                'totalMeal' => 0,
                'totalSnack' => 0,
                'childName' => null,
            ]);
        }
        // 月初と月末
        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        // Attendancesを取得
        $attendances = Attendance::with([
            'feeItems.feeItem', 
            'reservable' => function ($q) {
                $q->morphWith([
                    Reservation::class => ['child', 'slot.dateValue'],
                    NonmemberReservation::class => ['dateValue'],
                ]);
            }
        ])
        ->where('accounted', 1)
        ->whereDoesntHaveMorph(
            'reservable',
            [NonmemberReservation::class],
            fn($q) => $q->where('is_under_3', 2)
        )

        ->where(function ($query) use ($startOfMonth, $endOfMonth) {
            $query
            // 会員予約
                ->whereHasMorph('reservable', [Reservation::class], fn($q) =>
                    $q->whereHas('slot.dateValue', fn($q2) =>
                        $q2->whereBetween('date', [$startOfMonth, $endOfMonth])
                    )
                )
                    // 非会員予約
                ->orWhereHasMorph('reservable', [NonmemberReservation::class], fn($q) =>
                    $q->where('is_under_3', '!=', 2)
                        ->whereHas('dateValue', fn($q2) =>
                            $q2->whereBetween('date', [$startOfMonth, $endOfMonth])
                        )
                    );
        })

        ->when($childName, fn($query) =>
            $query->where(function ($q) use ($childName) {
                $q->whereHasMorph('reservable', [Reservation::class], fn($q2) =>
                    $q2->whereHas('child', fn($q3) =>
                        $q3->where('child_name', 'like', "%{$childName}%")
                    )
                )
                ->orWhereHasMorph('reservable', [NonmemberReservation::class], fn($q2) =>
                    $q2->where('is_under_3', '!=', 2)
                        ->where('child_name', 'like', "%{$childName}%")
                );
            })
        )
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
                ->filter(fn($f) => $f->feeItem && in_array($f->feeItem->category, ['未満児保育', '以上児保育', '誰でも通園']))
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

    public function anyoneChildSearch(Request $request)
    {
        $term = $request->input('term');

        // 誰でも通園のみ取得
        $names = \App\Models\NonmemberReservation::where('is_under_3', 2)
            ->where('child_name', 'like', "%{$term}%")
            ->pluck('child_name');

        return response()->json($names);
    }

    public function anyoneHistory(Request $request)
    {
        $month = $request->input('month') ? Carbon::parse($request->input('month')) : Carbon::now();
        $childName = $request->input('child_name');

        if (empty($childName)) {
            return view('admin.anyone_history', [
                'month' => $month,
                'rows' => [],
                'totalAll' => 0,
                'totalDays' => 0,
                'totalNursery' => 0,
                'totalMeal' => 0,
                'totalSnack' => 0,
                'childName' => null,
            ]);
        }

        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        $attendances = Attendance::with([
            'feeItems.feeItem',
            'reservable' => function ($q) {
                $q->morphWith([
                    NonmemberReservation::class => ['dateValue'],
                ]);
            }
        ])
        ->where('accounted', 1)
         // 誰でも通園のみ取得
        ->whereHasMorph('reservable', [NonmemberReservation::class], function ($q) use ($startOfMonth, $endOfMonth, $childName) {
        // 月条件（必須）
        $q->whereHas('dateValue', fn($q2) =>
            $q2->whereBetween('date', [$startOfMonth, $endOfMonth])
        );
        // 誰でも通園
        $q->where('is_under_3', 2);
        // 子ども名（入力時のみ）
        if ($childName) {
            $q->where('child_name', 'like', "%{$childName}%");
        }
    })
    ->get();


        $rows = [];
        $totalDays = $attendances->count();
        $totalAll = $totalNursery = $totalMeal = $totalSnack = 0;

        foreach ($attendances as $attendance) {
            $feeItems = $attendance->feeItems;

            $nurseryAmount = $feeItems
                ->filter(fn($f) => $f->feeItem && in_array($f->feeItem->category, ['未満児 保育', '以上児保育', '誰でも通園']))
                ->sum('amount');

            $mealAmount = $feeItems
                ->filter(fn($f) => $f->feeItem && $f->feeItem->category === '給食')
                ->sum('amount');

            $snackAmount = $feeItems
                ->filter(fn($f) => $f->feeItem && $f->feeItem->category === 'おやつ')
                ->sum('amount');

            $subtotal = $nurseryAmount + $mealAmount + $snackAmount;

            $rows[] = [
                'date' => optional($attendance->reservable->dateValue)->date,
                'time' => Carbon::parse($attendance->actual_start_time)->format('H:i') 
                        . '～' . Carbon::parse($attendance->actual_end_time)->format('H:i'),
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

        return view('admin.anyone_history', compact(
            'month', 'rows', 'totalAll', 'totalDays', 'totalNursery', 'totalMeal', 'totalSnack', 'childName'
        ));
    }

    //
}
