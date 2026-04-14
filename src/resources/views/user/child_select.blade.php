@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/child_select.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>一時預かり予約</h2>
        <h3>{{ $child->child_name }}さん</h3>
    </div>
    <div class="reservation">
        <a href="{{ route('user.reservation') }}" class="btn btn-primary">予約する</a>
    </div>
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="button">
        <div class="confirm">
            <a href="{{ route('user.reservation.history') }}" class="btn btn-primary">予約確認</a>
        </div>
        <div class="mypage">
            <a href="{{ route('user.mypage') }}" class="btn btn-secondary">マイページ</a>
        </div>
    </div>

    <div class="back__button">
        <a class="back" href="{{ route('user.dashboard') }}">back</a>
    </div>
    
</div>
@endsection