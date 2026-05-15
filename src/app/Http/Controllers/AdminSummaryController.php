<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\AttendanceFeeItem;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminSummaryController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month') ? Carbon::parse($request->input('month')) : Carbon::now();
        // getSummaryData を呼ぶ（配列で受け取る）
        [
            $summary,
            $totalDays,
            $totalCare,
            $totalMeal,
            $totalSnack,
            $totalAll,
            $totalUnder4,
            $totalOver4
        ] = $this->getSummaryData($month);

        return view('admin.summary', compact(
            'summary', 'month', 'totalDays',
            'totalCare', 'totalMeal', 'totalSnack', 'totalAll',
            'totalUnder4', 'totalOver4'
        ));
    }

    protected function getSummaryData(Carbon $month)
    {
        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        // Attendance を取得（必要なら morphWith を使ってネストリレーションをロード）
        $attendances = Attendance::with(['feeItems.feeItem', 'reservable'])
            ->where('accounted', 1)
            ->whereHasMorph('reservable', [
                \App\Models\Reservation::class,
                \App\Models\NonmemberReservation::class
            ], function ($q, $type) use ($startOfMonth, $endOfMonth) {
                if ($type === \App\Models\Reservation::class) {
                    $q->whereHas('slot.dateValue', function ($q2) use ($startOfMonth, $endOfMonth) {
                        $q2->whereBetween('date', [
                            $startOfMonth->toDateString(),
                            $endOfMonth->toDateString()
                        ]);
                    });
                }

                if ($type === \App\Models\NonmemberReservation::class) {
                    // 非会員予約は dateValue.date を直接
                    $q->where('purpose', '!=', 'ママ')
                        ->whereIn('is_under_3', [0, 1])
                        ->whereHas('dateValue', function ($q3) use ($startOfMonth, $endOfMonth) {
                            $q3->whereBetween('date', [
                                $startOfMonth->toDateString(),
                                $endOfMonth->toDateString()
                            ]);
                        });
                }
            })
            ->get();
            
        $summary = [];
        foreach ($attendances as $attendance) {
            
            // 利用日の取得（会員は reservationSlot 経由、非会員は直接 dateValue）
            $dateValue = null;
            if ($attendance->reservable_type === 'App\\Models\\Reservation') {
                $dateValue = $attendance->reservable->slot->dateValue ?? null;
            } else {
                $dateValue = $attendance->reservable->dateValue ?? null;
            }

            if (!$dateValue || empty($dateValue->date)) continue;
            $useDate = Carbon::parse($dateValue->date);
            $dateStr = $useDate->toDateString();

            if (!isset($summary[$dateStr])) {
                $summary[$dateStr] = [
                    'under4' => 0,
                    'over4'  => 0,
                    'careFee' => 0,
                    'mealFee' => 0,
                    'snackFee' => 0,
                ];
            }

            // 時間計算（1時間単位切り上げ）
            $start = $attendance->actual_start_time ? Carbon::parse($attendance->actual_start_time) : null;
            $end = $attendance->actual_end_time ? Carbon::parse($attendance->actual_end_time) : null;
            
            if (!$start || !$end) {
                continue;
            }

            if (!$end->gt($start)) {
                continue;
            }

            $minutes = $end->diffInMinutes($start);
            $hours = (int) ceil($minutes / 60);
            if ($hours < 4) {
                $summary[$dateStr]['under4']++;
            } else {
                $summary[$dateStr]['over4']++;
            }
            

            // feeItems を1つずつ評価。ただし保育料はその attendance ごとに一度だけ加算する（重複防止）
            $careAdded = false;
            foreach ($attendance->feeItems as $afi) {
                $feeItem = $afi->feeItem;
                if (!$feeItem) continue;

                // 適用期間チェック（start_date / end_date がある場合）
                $startDate = $feeItem->start_date ? Carbon::parse($feeItem->start_date) : null;
                $endDate = $feeItem->end_date ? Carbon::parse($feeItem->end_date) : null;
                $isApplicable = true;
                if ($startDate && $useDate->lt($startDate)) $isApplicable = false;
                if ($endDate && $useDate->gt($endDate)) $isApplicable = false;
                if (!$isApplicable) continue;

                $category = $feeItem->category ?? '';
                $unit = $feeItem->unit ?? '1回'; // 'hour' or 'once'
                $amount = $feeItem->amount ?? 0;

                $isCareCategory = in_array($category, ['未満児保育', '以上児保育']);

                if ($isCareCategory && $unit === '時間') {
                    // 保育料は時間 × 金額
                     $calcAmount = $amount * $hours;
                } else {
                     // 給食・おやつ 等は無条件で固定料金
                    $calcAmount = $amount;
                }

                switch ($category) {
                    case '未満児保育':
                    case '以上児保育':
                        if (!$careAdded) {
                            $summary[$dateStr]['careFee'] += $calcAmount;
                            $careAdded = true;
                        }
                        break;
                        
                    case '給食':
                        $summary[$dateStr]['mealFee'] += $calcAmount;
                        break;
                    case 'おやつ':
                        $summary[$dateStr]['snackFee'] += $calcAmount;
                        break;

                    default:
                        // 他カテゴリがあれば必要に応じて追加
                        break;
                }
            }
        }

        // 月合計を計算
        $totalDays = count($summary);
        $totalCare = array_sum(array_column($summary, 'careFee'));
        $totalMeal = array_sum(array_column($summary, 'mealFee'));
        $totalSnack = array_sum(array_column($summary, 'snackFee'));
        $totalAll = $totalCare + $totalMeal + $totalSnack;

        // 人数合計（4時間未満/以上）
        $totalUnder4 = array_sum(array_column($summary, 'under4'));
        $totalOver4 = array_sum(array_column($summary, 'over4'));

        return [
            $summary,
            $totalDays,
            $totalCare,
            $totalMeal,
            $totalSnack,
            $totalAll,
            $totalUnder4,
            $totalOver4
        ];
    }

    public function downloadPdf(Request $request)
    {

        $month = $request->input('month') ? Carbon::parse($request->input('month')) : Carbon::now();

        [
            $summary,
            $totalDays,
            $totalCare,
            $totalMeal,
            $totalSnack,
            $totalAll,
            $totalUnder4,
            $totalOver4
        ] = $this->getSummaryData($month);

        $pdf = Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ])->loadView('admin.summary_pdf', compact(
            'summary', 'month', 'totalDays',
            'totalCare', 'totalMeal', 'totalSnack', 'totalAll',
            'totalUnder4', 'totalOver4'
        ))->setPaper('A4', 'portrait');

        return $pdf->stream();
    }

    public function indexAnyone(Request $request)
    {
        $month = $request->input('month') ? Carbon::parse($request->input('month')) : Carbon::now();
        // getSummaryData を呼ぶ（配列で受け取る）
        [
            $summary,
            $totalDays,
            $totalCare,
            $totalMeal,
            $totalSnack,
            $totalAll,
            $totalUnder4,
            $totalOver4
        ] = $this->getSummaryAnyoneData($month);

        return view('admin.summary_anyone', compact(
            'summary', 'month', 'totalDays',
            'totalCare', 'totalMeal', 'totalSnack', 'totalAll',
            'totalUnder4', 'totalOver4'
        ));
    }

    protected function getSummaryAnyoneData(Carbon $month)
    {
        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        // Attendance を取得（必要なら morphWith を使ってネストリレーションをロード）
        $attendances = Attendance::with(['feeItems.feeItem', 'reservable.dateValue'])
            ->where('accounted', 1)
            ->whereHasMorph('reservable', [\App\Models\NonmemberReservation::class
                ], 
                function ($q) use ($startOfMonth, $endOfMonth) {
                    $q->whereIn('is_under_3', [2, 3, 4]);
                
                    $q->whereHas('dateValue', function ($q2) use ($startOfMonth, $endOfMonth) {
                        $q2->whereBetween('date', [
                            $startOfMonth->toDateString(),
                            $endOfMonth->toDateString()
                        ]);
                    });
                }

            )
            ->get();
            
        $summary = [];
        foreach ($attendances as $attendance) {

            $dateValue = $attendance->reservable->dateValue ?? null;
            

            if (!$dateValue || empty($dateValue->date)) continue;
            $useDate = Carbon::parse($dateValue->date);
            $dateStr = $useDate->toDateString();

            if (!isset($summary[$dateStr])) {
                $summary[$dateStr] = [
                    'under4' => 0,
                    'over4'  => 0,
                    'careFee' => 0,
                    'mealFee' => 0,
                    'snackFee' => 0,
                ];
            }

            // 時間計算（1時間単位切り上げ）
            $start = $attendance->actual_start_time ? Carbon::parse($attendance->actual_start_time) : null;
            $end = $attendance->actual_end_time ? Carbon::parse($attendance->actual_end_time) : null;
            
            if (!$start || !$end) {
                continue;
            }

            if (!$end->gt($start)) {
                continue;
            }

            $minutes = $end->diffInMinutes($start);
            $units30 = (int) ceil($minutes / 30);
            if ($minutes < 240) {
                $summary[$dateStr]['under4']++;
            } else {
                $summary[$dateStr]['over4']++;
            }
            

            // feeItems を1つずつ評価。ただし保育料はその attendance ごとに一度だけ加算する（重複防止）
            $careAdded = false;
            foreach ($attendance->feeItems as $afi) {
                $feeItem = $afi->feeItem;
                if (!$feeItem) continue;

                // 適用期間チェック（start_date / end_date がある場合）
                $startDate = $feeItem->start_date ? Carbon::parse($feeItem->start_date) : null;
                $endDate = $feeItem->end_date ? Carbon::parse($feeItem->end_date) : null;
                $isApplicable = true;
                if ($startDate && $useDate->lt($startDate)) $isApplicable = false;
                if ($endDate && $useDate->gt($endDate)) $isApplicable = false;
                if (!$isApplicable) continue;

                $category = $feeItem->category ?? '';
                $unit = $feeItem->unit ?? '1回'; // 'hour' or 'once'
                $amount = $feeItem->amount ?? 0;

                $isCareCategory = in_array($category, [
                    '未満児保育', 
                    '以上児保育',
                    '誰でも通園',
                    '誰でも通園減免',
                    '誰でも通園無償',
                ]);

                if ($isCareCategory) {

                    $calcAmount = $amount * $units30;

                } else {

                    $calcAmount = $amount;
                }

                switch ($category) {
                    case '未満児保育':
                    case '以上児保育':
                    case '誰でも通園':
                    case '誰でも通園減免':
                    case '誰でも通園無償':
                        if (!$careAdded) {
                            $summary[$dateStr]['careFee'] += $calcAmount;
                            $careAdded = true;
                        }
                        break;
                        
                    case '給食':
                        $summary[$dateStr]['mealFee'] += $calcAmount;
                        break;
                    case 'おやつ':
                        $summary[$dateStr]['snackFee'] += $calcAmount;
                        break;

                    default:
                        // 他カテゴリがあれば必要に応じて追加
                        break;
                }
            }
        }

        // 月合計を計算
        $totalDays = count($summary);
        $totalCare = array_sum(array_column($summary, 'careFee'));
        $totalMeal = array_sum(array_column($summary, 'mealFee'));
        $totalSnack = array_sum(array_column($summary, 'snackFee'));
        $totalAll = $totalCare + $totalMeal + $totalSnack;

        // 人数合計（4時間未満/以上）
        $totalUnder4 = array_sum(array_column($summary, 'under4'));
        $totalOver4 = array_sum(array_column($summary, 'over4'));

        return [
            $summary,
            $totalDays,
            $totalCare,
            $totalMeal,
            $totalSnack,
            $totalAll,
            $totalUnder4,
            $totalOver4
        ];
    }

    public function downloadAnyonePdf(Request $request)
    {

        $month = $request->input('month') ? Carbon::parse($request->input('month')) : Carbon::now();

        [
            $summary,
            $totalDays,
            $totalCare,
            $totalMeal,
            $totalSnack,
            $totalAll,
            $totalUnder4,
            $totalOver4
        ] = $this->getSummaryAnyoneData($month);

        $pdf = Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ])->loadView('admin.summary_pdf', compact(
            'summary', 'month', 'totalDays',
            'totalCare', 'totalMeal', 'totalSnack', 'totalAll',
            'totalUnder4', 'totalOver4'
        ))->setPaper('A4', 'portrait');

        return $pdf->stream();
    }



    //
}
