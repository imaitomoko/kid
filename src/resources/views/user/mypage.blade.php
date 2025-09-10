@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/mypage.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>マイページ</h2>
        <h3>{{ Auth::user()->name }}さん</h3>
    </div>
    <div class="button">
        <div class="info">
            <a href="">登録情報</a>
        </div>
        <div class="history">
            <a href="">利用履歴</a>
        </div>
    </div>
    <div>
        <a href="">戻る</a>
    </div>
</div>
@endsection