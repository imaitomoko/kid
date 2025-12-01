@extends('layouts.app') 

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/reservation.css') }}">
@endsection 

@section('content')
<div class="content">
    <div class="heading">
        <h2>予約日選択</h2>
    </div>
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="month-navigation">
        <a href="{{ route('user.reservation', ['year' => $displayMonth->copy()->subMonth()->year, 'month' => $displayMonth->copy()->subMonth()->month]) }}"><< 前月</a>
        <span class="month">{{ $year }}年 {{ $month }}月</span>
        <a href="{{ route('user.reservation', ['year' => $displayMonth->copy()->addMonth()->year, 'month' => $displayMonth->copy()->addMonth()->month]) }}">翌月 >></a>
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
                $startDayOfWeek = $displayMonth->copy()->startOfMonth()->dayOfWeek;
                $daysInMonth = $displayMonth->daysInMonth;
            @endphp
            <tr>
                @for($i = 0; $i < $startDayOfWeek; $i++)
                    <td></td>
                @endfor

                @for($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $date = $displayMonth->copy()->day($day)->toDateString();
                        $info = $dates[$date];
                    @endphp
                    <td>
                        <div>{{ $day }}</div>
                        <div class="mt-1">
                            @if(!$info['canBook'])
                                {{-- 受付不可 or × --}}
                                <span class="badge bg-secondary">{{ $info['label'] }}</span>
                            @else
                                {{-- 予約可能：⚪︎ボタン --}}
                                <form action="{{ route('user.reservation.list', $date) }}" method="GET">
                                    <button type="submit" class="btn btn-sm btn-success">{{ $info['label'] }}</button>
                                </form>
                            @endif
                        </div>
                    </td>

                    @if(($startDayOfWeek + $day) % 7 == 0)
                        </tr><tr>
                    @endif
                @endfor

            </tr>
        </tbody>
    </table>

    <div class="back__button">
        <a class="back" href="{{ route('user.dashboard') }}">back</a>
    </div>
</div>
@endsection


