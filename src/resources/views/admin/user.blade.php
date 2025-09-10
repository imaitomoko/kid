@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/user.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>ユーザー管理</h2>
    </div>
    <div class="menu-group">
        <div class="main-menu">
            <a href="{{ route('admin.create') }}" class="menu-button main-btn">ユーザー新規登録</a>
            <a href=" " class="menu-button main-btn">ユーザー編集・削除</a>
        </div>
    </div>
    <div class="back__button">
        <a class="back" href="{{ route('admin') }}">back</a>
    </div>

</div>
@endsection