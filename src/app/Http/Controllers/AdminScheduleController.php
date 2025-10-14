<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\DateValue;
use App\Models\ReservationSlot;

class AdminScheduleController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);

        $current = Carbon::create($year, $month, 1);

        // 月の初日と末日
        $startOfMonth = $current->copy()->startOfMonth();
        $endOfMonth   = $current->copy()->endOfMonth();

        // カレンダー用データ（日ごと）
        $dates = [];
        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {

            $weekday = $date->dayOfWeek; // 0=日曜, 6=土曜
            $isHoliday = $weekday === 0 || $weekday === 6;

            // date_value取得（存在しない場合は null でも可）
            $dateValue = DateValue::where('date', $date->format('Y-m-d'))->first();

            // 予約スロット合計
            $totalCapacity = $dateValue ? $dateValue->reservationSlots->sum('capacity') : 0;

            // 表示ステータス
            if ($isHoliday) {
                $status = '受付不可';
            } else {
                $status = $totalCapacity > 0 ? '⚪︎' : '✕';
            }

            $dates[] = [
                'date' => $date->copy(),
                'status' => $status,
            ];
        }

        return view('admin.admin_schedule', [
            'dates' => $dates,
            'year' => $year,
            'month' => $month,
            'current' => $current,
        ]);
    }

    public function show($date)
    {
        $selectedDate = Carbon::parse($date);

        // その週の月～金の日付配列
        $weekStart = $selectedDate->copy()->startOfWeek(Carbon::MONDAY);
        $weekDates = [];
        for ($i = 0; $i < 5; $i++) {
            $weekDates[] = $weekStart->copy()->addDays($i);
        }

        // 時間帯（8:30～16:30、30分刻み）
        $timeSlots = [];
        $time = Carbon::createFromTime(8, 30);
        while ($time->lte(Carbon::createFromTime(16, 30))) {
            $timeSlots[] = $time->format('H:i');
            $time->addMinutes(30);
        }

        // 週内の reservation_slots を取得（なければ空）
        $slots = [];
        foreach ($weekDates as $d) {
            $dateStr = $d->format('Y-m-d');
            $dateValue = DateValue::firstOrCreate(['date' => $dateStr]);

            $existingSlots = ReservationSlot::where('date_value_id', $dateValue->id)
                ->get();

            foreach ($timeSlots as $t) {
                $slotTime = Carbon::parse($t)->format('H:i:s');
                $slot = $existingSlots->firstWhere('slot_time', $slotTime);

                // あればそれを、なければ空のオブジェクト
                if (!$slot) {
                    $slot = new ReservationSlot([
                        'date_value_id' => $dateValue->id,
                        'slot_time' => $slotTime,
                        'capacity' => 0,
                    ]);
                }

                $slots[$dateStr][$t] = $slot;
            }
        }

        return view('admin.admin_schedule_list', compact('selectedDate', 'weekDates', 'timeSlots', 'slots'));
    }

    // POSTで容量を保存
    public function update(Request $request, $date)
    {

        $weekStart = Carbon::parse($date)->startOfWeek(Carbon::MONDAY);
        $weekDates = [];
        for ($i = 0; $i < 5; $i++) {
            $weekDates[] = $weekStart->copy()->addDays($i);
        }

        $capacityData = $request->input('capacity', []);

        foreach ($weekDates as $d) {
            $dateStr = $d->format('Y-m-d');
            $dateValue = DateValue::firstOrCreate(['date' => $dateStr]);

            if (!empty($capacityData[$dateStr])) {
                foreach ($capacityData[$dateStr] as $time => $cap) {
                    $slotTime = Carbon::parse($time)->format('H:i:s');

                    $slot = ReservationSlot::where('date_value_id', $dateValue->id)
                                            ->where('slot_time', $slotTime)
                                            ->first();
                    if (!$slot) {
                        $slot = new ReservationSlot();
                        $slot->date_value_id = $dateValue->id;
                        $slot->slot_time = $slotTime;
                    }

                    $slot->capacity = max(0, intval($cap));
                    $slot->save();
                }
            }
        }

        return redirect()->route('admin.schedule.show', ['date' => reset($weekDates)->format('Y-m-d')])
        ->with('success', '予約枠を更新しました。');
    }
    //
}
