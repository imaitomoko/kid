@extends('layouts.admin') 

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/summary.css') }}">
@endsection 

@section('content')
<div class="content">
    <div class="heading">
        <h2>集計表</h2>
    </div>
    <div class="mb-3">
        <a href="{{ route('admin.summary', ['month' => $month->copy()->subMonth()->toDateString()]) }}" class="before">&laquo; 前月</a>
        <span class="month">{{ $month->format('Y年m月') }}</span>
        <a href="{{ route('admin.summary', ['month' => $month->copy()->addMonth()->toDateString()]) }}" class="before">翌月 &raquo;</a>
    </div>

    <table class="custom_table">
        <thead>
            <tr>
                <th>日付</th>
                <th>4時間未満</th>
                <th>4時間以上</th>
                <th>保育利用料</th>
                <th>給食</th>
                <th>おやつ</th>
                <th>小計</th>
            </tr>
        </thead>
        @php
            $totalUnder4 = 0;
            $totalOver4 = 0;
            $totalCare = 0;
            $totalMeal = 0;
            $totalSnack = 0;
            $totalAll = 0;
        @endphp
        <tbody>
            @foreach($summary as $date => $s)
            @php 
                $subtotal = $s['careFee'] + $s['mealFee'] + $s['snackFee'];
                $totalUnder4 += $s['under4'];
                $totalOver4 += $s['over4'];
            @endphp
    
            <tr>
                <td>{{ \Carbon\Carbon::parse($date)->format('d') }}</td>
                <td>{{ $s['under4'] }}</td>
                <td>{{ $s['over4'] }}</td>
                <td>{{ number_format($s['careFee']) }}</td>
                <td>{{ number_format($s['mealFee']) }}</td>
                <td>{{ number_format($s['snackFee']) }}</td>
                <td>{{ number_format($subtotal) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="table-total">
                <td class="total-label">月合計</td>
                <td class="fw-bold">{{ $totalUnder4 }}</td>
                <td class="fw-bold">{{ $totalOver4 }}</td>
                <td>{{ number_format($totalCare) }}</td>
                <td>{{ number_format($totalMeal) }}</td>
                <td>{{ number_format($totalSnack) }}</td>
                <td>{{ number_format($totalAll) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="text-end mt-3">
        <strong>開所日数：{{ $totalDays }} 日</strong>
    </div>

    <div class="pdf">
        <a class="btn-pdf" href="">PDF印刷</a>
    </div>

    <div class="back__button">
        <a class="back" href="{{ route('admin.user') }}">back</a>
    </div>
</div>
