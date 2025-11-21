@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/history.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>マイページ</h2>
        <h3>{{ $child->child_name }}さん</h3>
    </div>

    <div class="mb-3">
        <a href="{{ route('user.history', ['month' => $prevMonth->format('Y-m')]) }}" class="before">&laquo; 前月</a>
        <span class="month">{{ $currentMonth->format('Y年m月') }}</span>
        <a href="{{ route('user.history', ['month' => $nextMonth->format('Y-m')]) }}" class="before">翌月 &raquo;</a>
    </div>

    <table class="custom_table">
        <thead>
            <tr>
                <th>日</th>
                <th>時間</th>
                <th>給食</th>
                <th>おやつ</th>
                <th>利用料</th>
            </tr>
        </thead>

        <tbody>
        @foreach ($usageDates as $date)
            @php
                $att = $attendances->firstWhere(
                    fn ($a) => $a->reservable->slot->dateValue->date === $date
                );        
            @endphp

            <tr>
                <td>{{ \Carbon\Carbon::parse($date)->format('d') }}</td>
                <td>
                    {{ \Carbon\Carbon::parse($att->actual_start_time)->format('H:i') }}~
                    <br>{{ \Carbon\Carbon::parse($att->actual_end_time)->format('H:i') }}</br>
                </td>
                <td>
                    {{ $att->meal_used === 'yes' ? '◯' : '-' }}
                </td>
                <td>{{ $att->snack_used === 'yes' ? '◯' : '-' }}</td>
                <td>{{ number_format($att->total_fee) }}円</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="total_fee">
        <p>月合計 {{ number_format($totalFee) }}円</p>
    </div>

    <div class="back__button">
        <a class="back" href="{{ route('user.mypage') }}">back</a>
    </div>
</div>
@endsection
