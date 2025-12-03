@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/reservation_history.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>予約一覧</h2>
        <h3>{{ $child->child_name }}さん</h3>
    </div>
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="reservation-cards-wrapper">
        @forelse($summary as $date => $dayReservations)
        @php
        \Carbon\Carbon::setLocale('ja');
        @endphp
            @foreach($dayReservations as $res)
                <div class="reservation-card">
                    <div class="card-header">
                        <span class="date">{{ \Carbon\Carbon::parse($date)->format('Y/m/d') }}（{{ \Carbon\Carbon::parse($date)->isoFormat('ddd') }}）</span>
                        <span class="time">{{ $res['start']->format('H:i') }} ~ {{ $res['end']->format('H:i') }}</span>
                    </div>
                    <div class="card-body">
                        <div class="food">
                            <p>給食{{ $res['meal'] ? 'あり' : 'なし' }}</p>
                            <p>おやつ{{ $res['snack'] ? 'あり' : 'なし' }}</p>
                        </div>
                        <p>備考: {{ $res['note'] ?? 'なし' }}</p>
                    </div>
                    <div class="card-footer">
                        <form action="{{ route('user.reservation.cancel') }}" method="POST" onsubmit="return confirm('本当にキャンセルしますか？');">
                            @csrf
                            @method('DELETE')
                            @foreach($res['ids'] as $id)
                                <input type="hidden" name="reservation_ids[]" value="{{ $id }}">
                            @endforeach
                            <button type="submit" class="btn btn-danger btn-sm">キャンセル</button>
                        </form>
                    </div>
                </div>
            @endforeach
        @empty
            <p>予約はありません。</p>
        @endforelse  
    </div>

    <div class="back__button">
        <a class="back" href="{{ route('user.dashboard') }}">back</a>
    </div>
</div>
@endsection
