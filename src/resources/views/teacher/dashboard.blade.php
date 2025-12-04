@extends('layouts.teacher')

@section('css')
<link rel="stylesheet" href="{{ asset('css/teacher/dashboard.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>職員TOP</h2>
    </div>
    <div class="main-menu">
        <a href="{{ route('teacher.reservation.list', date('Y-m-d')) }}" class="menu-button main-btn">予約者確認</a>
    </div>

</div>
@endsection