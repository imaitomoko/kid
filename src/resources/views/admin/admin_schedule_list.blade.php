@extends('layouts.admin') 

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/admin_schedule_list.css') }}">
@endsection 

@section('content')
<div class="content">
    <div class="heading">
        <h2>スケジュール管理</h2>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <div class="mb-3">
        <a href="{{ route('admin.schedule.show', ['date' => $selectedDate->copy()->subWeek()->format('Y-m-d')]) }}"><< 前週</a>
        <span class="mx-3">{{ $selectedDate->format('Y年m月d日') }} の週の予約枠</span>
        <a href="{{ route('admin.schedule.show', ['date' => $selectedDate->copy()->addWeek()->format('Y-m-d')]) }}">翌週 >></a>
    </div>

    <form action="{{ route('admin.schedule.update', ['date' => $selectedDate->format('Y-m-d')]) }}" method="POST">
        @csrf
        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th>時間</th>
                    @foreach($weekDates as $d)
                        <th>{{ $d->format('m/d') }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($timeSlots as $t)
                    <tr>
                        <td>{{ $t }}</td>
                        @foreach($weekDates as $d)
                            @php $dateStr = $d->format('Y-m-d'); @endphp
                            <td>
                                <input type="number" min="0" name="capacity[{{ $dateStr }}][{{ $t }}]" 
                                    value="{{ old('capacity.' . $dateStr . '.' . $t, $slots[$dateStr][$t]->capacity ?? 0) }}"
                                    style="width:50px;">
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="btn-container">
            <button type="submit" class="btn btn-primary">確定</button>
        </div>   
    </form>

    <div class="back__button">
        <a class="back" href="{{ route('admin.schedule') }}">back</a>
    </div>
</div>
@endsection

