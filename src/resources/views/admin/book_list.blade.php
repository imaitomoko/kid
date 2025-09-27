@extends('layouts.admin') 

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/book_list.css') }}">
@endsection 

@section('content')
<div class="content">
    <div class="heading">
        <h2>本日の予約一覧</h2>
    </div>
    <p class="mb-3">{{ $today->format('Y年m月d日') }}</p>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>子ども名</th>
                <th>予約時間</th>
                <th>給食</th>
                <th>おやつ</th>
                <th>アレルギー</th>
                <th>操作</th>
                <th>利用料</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reservations as $reservation)
                @php
                    $baseFee = $attendance ? $attendance->feeItems->sum(fn($f) => $f->feeItem->amount ?? 0) : 0;
                    $mealFee = ($attendance && $attendance->meal_used === 'yes')
                    ? optional($attendance->feeItems->firstWhere('feeItem.category', 'meal'))->feeItem->amount ?? 0
                    : 0;
                    $totalFee = $baseFee + $mealFee;
                @endphp
                <tr>
                    <td>{{ $reservation->child->child_name }}</td>
                    <td>{{ $reservation->slot->slot_time }} - {{ $attendance?->actual_end_time ?? '--:--' }}</td>
                    <td>
    @if($reservation->meal)
        @if($attendance)
            <form action="{{ route('attendance.meal', $attendance) }}" method="POST" style="display:inline">
                @csrf
                <label style="cursor:pointer;">
                    <input type="checkbox" name="meal_used" value="yes" onchange="this.form.submit()" {{ $attendance->meal_used === 'yes' ? 'checked' : '' }}>
                    給食
                </label>
            </form>
        @else
            給食 ☐（利用開始後にチェック可能）
        @endif
    @else
        -
    @endif
</td>
                    <td>
    @if($reservation->snack)
        @if($attendance)
            <form action="{{ route('attendance.snack', $attendance) }}" method="POST" style="display:inline">
                @csrf
                <label style="cursor:pointer;">
                    <input type="checkbox" name="snack_used" value="yes" onchange="this.form.submit()" {{ $attendance->snack_used === 'yes' ? 'checked' : '' }}>
                    おやつ
                </label>
            </form>
        @else
            おやつ ☐（利用開始後にチェック可能）
        @endif
    @else
        -
    @endif
</td>
<td>{{ $reservation->child->allergy ?? 'なし' }}</td>
                    <td>
                        @if(!$attendance)
                            <form action="{{ route('attendance.start', $reservation) }}" method="POST">@csrf
                                <button class="btn btn-success btn-sm">利用開始</button>
                            </form>
                        @elseif(!$attendance->actual_end_time)
                            <form action="{{ route('attendance.end', $reservation) }}" method="POST">@csrf
                                <button class="btn btn-danger btn-sm">利用終了</button>
                            </form>
                        @endif
                    </td>
                    <td>¥{{ number_format($feeTotal) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="text-end mt-4">
        <h4>本日の合計： ¥{{ number_format($totalFee) }}</h4>
    </div>
    <div class="back__button">
        <a class="back" href="{{ route('admin.user') }}">back</a>
    </div>
</div>
@endsection