@extends('layouts.admin') 

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/admin_reservation.css') }}">
@endsection 

@section('content')
<div class="content">
    <div class="heading">
        <h2>予約管理</h2>
    </div>
    <div class="month-navigation">
        <a href="{{ route('admin.reservation', ['year' => $current->copy()->subMonth()->year, 'month' => $current->copy()->subMonth()->month]) }}"><< 前月</a>
        <span class="month">{{ $year }}年 {{ $month }}月</span>
        <a href="{{ route('admin.reservation', ['year' => $current->copy()->addMonth()->year, 'month' => $current->copy()->addMonth()->month]) }}">翌月 >></a>
    </div>

    <table class="calendar table table-bordered text-center">
        <thead>
            <tr>
                <th class="text-danger">日</th>
                <th>月</th>
                <th>火</th>
                <th>水</th>
                <th>木</th>
                <th>金</th>
                <th class="text-primary">土</th>
            </tr>
        </thead>
        <tbody>
            @php
                $startDayOfWeek = $current->copy()->startOfMonth()->dayOfWeek;
                $daysInMonth = $current->daysInMonth;
            @endphp
            <tr>
                @for($i = 0; $i < $startDayOfWeek; $i++)
                    <td></td>
                @endfor

                @for($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $date = $current->copy()->day($day)->toDateString();
                        $weekday = $current->copy()->day($day)->dayOfWeek;
                        $isHoliday = $weekday === 0 || $weekday === 6;
                    @endphp
                    <td>
                        <div>{{ $day }}</div>
                        <div class="mt-1">
                            @if($isHoliday)
                                <span class="badge bg-secondary">受付不可</span>
                            @else
                                <form action="{{ route('admin.reservation.list', ['date' => $date]) }}" method="GET">
                                <button type="submit" class="btn btn-sm btn-success">⚪︎</button>
                            </form>
                            @endif
                        </div>
                    </td>

                    @if(($startDayOfWeek + $day) % 7 == 0)
                        </tr><tr>
                    @endif
                @endfor

                @for($i = ($startDayOfWeek + $daysInMonth) % 7; $i < 7 && $i != 0; $i++)
                    <td></td>
                @endfor
            </tr>
        </tbody>
    </table>

    <div class="back__button">
        <a class="back" href="{{ route('admin') }}">back</a>
    </div>
</div>
@endsection


