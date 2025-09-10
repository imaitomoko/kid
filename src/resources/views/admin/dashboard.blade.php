@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>管理者TOP</h2>
    </div>
    <div class="menu-group">
        <div class="main-menu">
            <a href="  " class="menu-button main-btn">当日予約一覧</a>
            <a href=" " class="menu-button main-btn">予約管理</a>
            <a href="" class="menu-button main-btn">スケジュール管理</a>
            <a href=" " class="menu-button main-btn">集計</a>
            <a href=" " class="menu-button main-btn">履歴管理</a>
        </div>
        <div class="sub-menu">
            <a href="{{ route('admin.user') }}" class="menu-button sub-btn">ユーザー管理</a>
            <a href="" class="menu-button sub-btn">職員管理</a>
            <a href="" class="menu-button sub-btn">料金管理</a>
        </div>
    </div>

</div>
@endsection