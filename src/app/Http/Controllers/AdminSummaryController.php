<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\AttendanceFeeItem;

class AdminSummaryController extends Controller
{
    public function index(Request $request)
    {
        // 表示する月（指定なければ今月）
        $month = $request->input('month')
            ? Carbon::parse($request->input('month'))
            : Carbon::now();

        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        $attendances = Attendance::with([
            'feeItems.feeItem',
            'reservable' => function ($morph) {
                $morph->morphWith([
                    \App\Models\Reservation::class => ['dateValue'],
                    \App\Models\NonmemberReservation::class => ['dateValue'],
                ]);
            },
        ])
        ->whereHasMorph('reservable', [\App\Models\Reservation::class, \App\Models\NonmemberReservation::class], function ($q) use ($startOfMonth, $endOfMonth) {
            $q->whereHas('dateValue', function ($q2) use ($startOfMonth, $endOfMonth) {
                $q2->whereBetween('date', [$startOfMonth, $endOfMonth]);
            });
        })
        ->get();

        // 日ごとに集計
        $summary = [];
        foreach ($attendances as $attendance) {
            $dateValue = null;
            if ($attendance->reservable_type === 'App\Models\Reservation') {
                $dateValue = $attendance->reservable->reservationSlot->dateValue ?? null;
            } else {
                $dateValue = $attendance->reservable->dateValue ?? null;
            }

            if (!$dateValue || empty($dateValue->date)) continue;

            $useDate = Carbon::parse($dateValue->date);
            $dateStr = Carbon::parse($dateValue->date)->toDateString();

            if (!isset($summary[$dateStr])) {
                $summary[$dateStr] = [
                    'under4' => 0,
                    'over4'  => 0,
                    'careFee' => 0,
                    'mealFee' => 0,
                    'snackFee' => 0,
                ];
            }

            $start = $attendance->actual_start_time ? Carbon::parse($attendance->actual_start_time) : null;
            $end   = $attendance->actual_end_time ? Carbon::parse($attendance->actual_end_time) : null;

            $hours = 0;
            if ($start && $end && $end->gt($start)) {
                $minutes = $end->diffInMinutes($start);
                $hours = ceil($minutes / 60);
                if ($hours < 4) {
                    $summary[$dateStr]['under4']++;
                } else {
                    $summary[$dateStr]['over4']++;
                }
            }

            foreach ($attendance->feeItems as $afi) {
                $feeItem = $afi->feeItem;
                if (!$feeItem) continue;

                $category = $feeItem->category ?? '';
                $unit     = $feeItem->unit ?? 'once'; // デフォルト1回
                $amount   = $feeItem->amount ?? 0;

                // --- 利用日が適用期間内かチェック ---
                $startDate = $feeItem->start_date ? Carbon::parse($feeItem->start_date) : null;
                $endDate   = $feeItem->end_date ? Carbon::parse($feeItem->end_date) : null;

                $isApplicable = true;
                if ($startDate && $useDate->lt($startDate)) $isApplicable = false;
                if ($endDate && $useDate->gt($endDate)) $isApplicable = false;
 
                if (!$isApplicable) continue;

                $calcAmount = ($unit === 'hour') ? $amount * $hours : $amount;

                 // --- カテゴリ別集計 ---
                switch ($category) {
                    case '未満児保育':
                    case '以上児保育':
                        $summary[$dateStr]['careFee'] += $calcAmount;
                        break;
                    
                    case '給食':
                        $summary[$dateStr]['mealFee'] += $calcAmount;
                        break;

                    case 'おやつ':
                        $summary[$dateStr]['snackFee'] += $calcAmount;
                    break;
                }
            }
        }

        $totalDays = count($summary);
        $totalCare = array_sum(array_column($summary, 'careFee'));
        $totalMeal = array_sum(array_column($summary, 'mealFee'));
        $totalSnack = array_sum(array_column($summary, 'snackFee'));
        $totalAll = $totalCare + $totalMeal + $totalSnack;

        return view('admin.summary', compact('summary', 'month', 'totalDays', 'totalCare', 'totalMeal', 'totalSnack', 'totalAll'));
    }
    //
}
