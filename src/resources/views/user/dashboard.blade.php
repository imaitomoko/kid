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
        <a href="">予約する</a>
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