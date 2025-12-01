@extends('layouts.app') 

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/confirm.css') }}">
@endsection 

@section('content')
<div class="content">
    <div class="heading">
        <h2>予約の確認</h2>
    </div>

    <div class="reservation_card">
        <p class="date">{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}</p>
        <p class="time">
        @php
            $start = \Carbon\Carbon::parse($slots->first()->slot_time)->format('H:i');
            $end = \Carbon\Carbon::parse($slots->last()->slot_time)->addMinutes(30)->format('H:i');
        @endphp
            {{ $start }} ~ {{ $end }}
        </p>

        <p>給食{{ $meal ? 'あり' : 'なし' }}</p>
        <p>おやつ{{ $snack ? 'あり' : 'なし' }}</p>

        <p>{{ $round_type }}</p>
        <p>{{ $purpose }}</p>

        <p>備考：{{ $note }}</p>
    </div>
    <div>
        <form action="{{ route('user.reservation.store') }}" method="POST">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">
            <input type="hidden" name="meal" value="{{ $meal }}">
            <input type="hidden" name="snack" value="{{ $snack }}">
            <input type="hidden" name="round_type" value="{{ $round_type }}">
            <input type="hidden" name="purpose" value="{{ $purpose }}">
            <input type="hidden" name="note" value="{{ $note }}">

            @foreach($slot_ids as $id)
                <input type="hidden" name="slot_ids[]" value="{{ $id }}">
            @endforeach

            <button type="submit" class="btn btn-primary">確定する</button>
        </form>
    </div>

    <div class="back__button">
        <a class="back" href="{{ route('user.reservation.list', ['date' => $date]) }}">back</a>
    </div>

</div>

@endsection