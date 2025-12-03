@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/dashboard.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>一時預かり予約</h2>
        @if ($child)
            <h3>{{ $child->child_name }}さん</h3>
        @else
            <h3>お子様情報が登録されていません</h3>
        @endif
    </div>
    <div class="reservation">
        @if(!$user->isProfileComplete())
            <div class="alert alert-warning">
                <p>ユーザー情報を登録すると予約ボタンが表示されます</p>
            </div>
            <button class="btn btn-primary" disabled>予約する</button>
            <a href="{{ route('user.profile') }}" class="btn btn-secondary">ユーザー情報を登録する</a>
        @else
            <a href="{{ route('user.reservation') }}" class="btn btn-primary">予約する</a>
        @endif
    </div>
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="button">
        <div class="confirm">
            <a href="{{ route('user.reservation.history') }}">予約確認</a>
        </div>
        <div class="mypage">
            <a href="{{ route('user.mypage') }}">マイページ</a>
        </div>
    </div>
    
</div>
@endsection