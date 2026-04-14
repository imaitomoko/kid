@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/mypage.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>マイページ</h2>
        <h3>{{ $child ? $child->child_name . 'さん' : 'お子様未選択' }}</h3>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    <div class="button">
        <div class="info">
            <a href="{{ route('user.child.edit') }}">登録情報</a>
        </div>
        <div class="history">
            <a href="{{ route('user.history', ['month' => now()->format('Y-m')]) }}">利用履歴</a>
        </div>
    </div>
    <div class="back__button">
        <a class="back" href="{{ route('user.child.select', ['child_id' => session('child_id')]) }}">back</a>
    </div>
</div>
@endsection