@extends('layouts.teacher')

@section('css')
<link rel="stylesheet" href="{{ asset('css/teacher/list.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>本日の予約一覧</h2>
    </div>

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    
    <div class="mb-3">
        <a href="{{ route('teacher.reservation.list', ['date' => $date->copy()->subDay()->toDateString()]) }}" class="before">&laquo; 前日</a>
        <span class="date">{{ $date->format('Y年m月d日') }}</span>
        <a href="{{ route('teacher.reservation.list', ['date' => $date->copy()->addDay()->toDateString()]) }}" class="before">翌日 &raquo;</a>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>子ども名</th>
                <th>予約時間</th>
                <th>利用時間</th>
                <th>食事</th>
                <th>アレルギー</th>
                <th>利用料</th>
                <th>会計</th>
            </tr>
        </thead>
        <tbody>
            @foreach($allReservations as $reservation)
                @include('teacher.partials.list_row', [
                    'reservation' => $reservation,
                    'isNonmember' => $reservation->isNonmember
                ])
            @endforeach
        </tbody>
    </table>

    <div class="back__button">
        <a class="back" href="{{ route('teacher.dashboard') }}">back</a>
    </div>
</div>
@endsection