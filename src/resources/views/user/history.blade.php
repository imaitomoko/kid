@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/history.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>利用履歴</h2>
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
        @foreach($histories as $history)
            <tr class="usage-row">
                <td>{{ \Carbon\Carbon::parse($history['date'])->format('d') }}</td>

                <td>
                    {{ \Carbon\Carbon::parse($history['start_time'])->format('H:i') }}
                    〜
                    {{ \Carbon\Carbon::parse($history['end_time'])->format('H:i') }} 
                </td>

                <td> {{ $history['meal'] ? '○' : '－' }}</td>
                <td> {{ $history['snack'] ? '○' : '－' }}</td>

                <td>
                    @if($history['accounted'] == 0)
                        <span class="unaccounted">未会計</span>
                    @else
                        {{ number_format($history['fee']) }} 円
                    @endif
                </td>
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
