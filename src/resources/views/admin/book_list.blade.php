@extends('layouts.admin') 

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/book_list.css') }}">
@endsection 

@section('content')
<div class="content">
    <div class="heading">
        <h2>本日の予約一覧</h2>
    </div>
    <div class="mb-3">
        <a href="{{ route('book.list', ['date' => $date->copy()->subDay()->toDateString()]) }}" class="before">&laquo; 前日</a>
        <span class="date">{{ $date->format('Y年m月d日') }}</span>
        <a href="{{ route('book.list', ['date' => $date->copy()->addDay()->toDateString()]) }}" class="before">翌日 &raquo;</a>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>子ども名</th>
                <th>予約時間</th>
                <th>操作</th>
                <th>食事</th>
                <th>アレルギー</th>
                <th>利用料</th>
                <th>会計</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reservations as $reservation)
                @include('admin.partials.book_row', ['reservation' => $reservation, 'isNonmember' => false])
            @endforeach

            @foreach($nonmemberReservations as $reservation)
                @include('admin.partials.book_row', ['reservation' => $reservation, 'isNonmember' => true])
            @endforeach
        </tbody>
    </table>

    <div class="text-end mt-4">
        <p>全体の合計：<strong>¥{{ number_format($totalFee) }}</strong></p>
        <p>会計済みの合計：<strong class="text-success">¥{{ number_format($accountedTotal) }}</strong></p>
    </div>

    <div class="back__button">
        <a class="back" href="{{ route('admin.user') }}">back</a>
    </div>
</div>
@endsection