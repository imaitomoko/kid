@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/dashboard.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>一時預かり予約</h2>
        <h3>{{ Auth::user()->name }}さん</h3>
    </div>
    <div class="reservation">
        @if(!$user->isProfileComplete())
            <div class="alert alert-warning">
                ユーザー情報を登録すると予約ボタンが表示されます
            </div>
            <button class="btn btn-primary" disabled>予約する</button>
            <a href="" class="btn btn-secondary">ユーザー情報を登録する</a>
        @else
            <a href="" class="btn btn-primary">予約する</a>
        @endif
    </div>
    <div class="button">
        <div class="confirm">
            <a href="">予約確認</a>

        </div>
        <div class="mypage">
            <a href="">マイページ</a>
        </div>
    </div>
    
</div>
@endsection