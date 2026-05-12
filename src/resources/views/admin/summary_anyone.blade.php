@extends('layouts.admin') 

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/summary_anyone.css') }}">
@endsection 

@section('content')
<div class="content">
    <div class="heading">
        <h2>誰でも通園集計表</h2>
    </div>
    <div class="mb-3">
        <a href="{{ route('admin.summary.anyone', ['month' => $month->copy()->subMonth()->toDateString()]) }}" class="before">&laquo; 前月</a>
        <span class="month">{{ $month->format('Y年m月') }}</span>
        <a href="{{ route('admin.summary.anyone', ['month' => $month->copy()->addMonth()->toDateString()]) }}" class="before">翌月 &raquo;</a>
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
        @endphp
        <tbody>
            @foreach($summary as $date => $s)
            @php
                $careFee  = $s['careFee']  ?? 0;
                $mealFee  = $s['mealFee']  ?? 0;
                $snackFee = $s['snackFee'] ?? 0;

                $subtotal = $careFee + $mealFee + $snackFee;

                $under4 = $s['under4'] ?? 0;
                $over4  = $s['over4']  ?? 0;

                $totalUnder4 += $under4;
                $totalOver4 += $over4;
            @endphp
    
            <tr>
                <td>{{ \Carbon\Carbon::parse($date)->format('d') }}</td>
                <td>{{ $under4 }}</td>
                <td>{{ $over4 }}</td>
                <td>{{ number_format($careFee) }}</td>
                <td>{{ number_format($mealFee) }}</td>
                <td>{{ number_format($snackFee) }}</td>
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
        <a class="btn-pdf" href="{{ url('admin/summary/anyone/pdf') }}?month={{ $month->toDateString() }}">PDF印刷</a>
    </div>
        

    

    <div class="back__button">
        <a class="back" href="{{ route('admin.summary') }}">back</a>
    </div>
</div>
@endsection
