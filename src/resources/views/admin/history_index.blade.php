@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/history_index.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>履歴管理</h2>
    </div>
    <div class="menu">
            <a href="{{ route('admin.history.show') }}" class="menu-button sub-btn">個別利用履歴</a>
            <a href="{{ route('admin.anyone.history') }}" class="menu-button sub-btn">誰でも通園利用履歴</a>
    </div>

    <div class="back__button">
        <a class="back" href="{{ route('admin') }}">back</a>
    </div>

</div>
@endsection