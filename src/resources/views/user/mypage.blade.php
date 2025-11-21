@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/mypage.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>マイページ</h2>
        <h3>{{ $child->child_name  }}さん</h3>
    </div>
    <div class="button">
        <div class="info">
            <a href="{{ route('user.profile') }}">登録情報</a>
        </div>
        <div class="history">
            <a href="{{ route('user.history', ['month' => now()->format('Y-m')]) }}">利用履歴</a>
        </div>
    </div>
    <div class="back__button">
        <a class="back" href="{{ route('user.dashboard') }}">back</a>
    </div>
</div>
@endsection