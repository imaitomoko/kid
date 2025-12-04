@php
    $attendance = $reservation->attendance;
    $feeTotal = $attendance ? $attendance->feeItems->sum(fn($f) => $f->feeItem->amount ?? 0) : 0;
    $query = http_build_query([
                'isNonmember' => $isNonmember ? 1 : 0,
                'date' => $date->format('Y-m-d'),
            ]);
@endphp

<tr>
    <td>
        <a href="{{ route('teacher.attendance.detail', $reservation->id) . '?' . $query }}">
            {{ $isNonmember ? $reservation->child_name : $reservation->child->child_name }}
        </a>
    </td>

    <td>
        @if($isNonmember)
            {{ \Carbon\Carbon::parse($reservation->start_time)->format('H:i') }}
            -
            {{ \Carbon\Carbon::parse($reservation->end_time)->format('H:i') }}
        @else
            @foreach($reservation->merged_times as $time)
                <div>{{ $time['start'] }}-{{ $time['end'] }}</div>
            @endforeach
        @endif
    </td>

    <td class="text-center">
        @if(!$reservation->attendance || !$reservation->attendance->actual_start_time)
            <form action="{{ route('teacher.attendance.start', ['id' => $reservation->id]) }}" method="POST">
                @csrf
                <input type="hidden" name="nonmember" value="{{ $isNonmember ? '1' : '0' }}">
                <button type="submit" class="btn btn-success btn-sm">利用開始</button>
            </form>
        @else
            <div class="d-flex justify-content-center align-items-center gap-1">
                <form action="{{ route('teacher.attendance.updateStartTime', $reservation) }}" method="POST" class="d-flex align-items-center">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="nonmember" value="{{ $isNonmember ? '1' : '0' }}">
                    <input type="time" name="actual_start_time"
                        value="{{ \Carbon\Carbon::parse($attendance->actual_start_time)->format('H:i') }}"
                        class="form-control form-control-sm" style="width:100px;">
                    <button type="submit" class="btn btn-primary btn-sm ms-1" title="編集" ><i class="fa-solid fa-pen"></i></button>
                </form>
                <form action="{{ route('teacher.attendance.deleteStartTime', ['id' => $reservation->id]) }}" method="POST" onsubmit="return confirm('開始時刻を削除しますか？');">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="nonmember" value="{{ $isNonmember ? '1' : '0' }}">
                    <button type="submit" class="btn btn-danger btn-sm" title="削除"><i class="fa-solid fa-trash"></i></button>
                </form>
            </div>
        @endif
        @if(!$attendance || !$attendance->actual_end_time)
            <form action="{{ route('teacher.attendance.end',['id' => $reservation->id]) }}" method="POST">@csrf
                <input type="hidden" name="nonmember" value="{{ $isNonmember ? '1' : '0' }}">
                <button type="submit" class="btn btn-success btn-sm"{{ !$attendance || !$attendance->actual_start_time ? 'disabled' : '' }}>利用終了</button>
            </form>
        @else
            <div class="d-flex justify-content-center align-items-center gap-1">
                <form action="{{ route('teacher.attendance.updateEndTime', $reservation) }}" method="POST" class="d-flex align-items-center">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="nonmember" value="{{ $isNonmember ? '1' : '0' }}">
                    <input type="time" name="actual_end_time"
                        value="{{ \Carbon\Carbon::parse($attendance->actual_end_time)->format('H:i') }}"
                        class="form-control form-control-sm" style="width:100px;">
                    <button type="submit" class="btn btn-primary btn-sm ms-1" title="編集"><i class="fa-solid fa-pen"></i></button>
                </form>
                <form action="{{ route('teacher.attendance.deleteEndTime', ['id' => $reservation->id]) }}" method="POST" onsubmit="return confirm('利用終了を削除しますか？');">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="nonmember" value="{{ $isNonmember ? '1' : '0' }}">
                    <button type="submit" class="btn btn-danger btn-sm" title="削除"><i class="fa-solid fa-trash"></i></button>
                </form>
            </div>
        @endif
    </td>

    <td>
        @if($reservation->meal)
            <div class="d-flex align-items-center justify-content-center gap-1">
                <span>給食</span>
                <form action="{{ route('teacher.attendance.meal.delete', ['id' => $reservation->id]) }}" method="POST" onsubmit="return confirm('給食利用を削除しますか？');">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="nonmember" value="{{ $isNonmember ? '1' : '0' }}">
                    <button type="submit" class="btn btn-danger btn-sm" title="削除">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
            </div>
        @else
            <form action="{{ route('teacher.attendance.meal', ['id' => $reservation->id]) }}" method="POST">
                @csrf
                <input type="hidden" name="nonmember" value="{{ $isNonmember ? '1' : '0' }}">
                <button type="submit" class="btn btn-primary btn-sm">給食利用</button>
            </form>
        @endif

        @if($reservation->snack)
            <div class="d-flex align-items-center justify-content-center gap-1">
                <span>おやつ</span>
                <form action="{{ route('teacher.attendance.snack.delete', ['id' => $reservation->id]) }}" method="POST" onsubmit="return confirm('おやつ利用を削除しますか？');">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="nonmember" value="{{ $isNonmember ? '1' : '0' }}">
                    <button type="submit" class="btn btn-danger btn-sm" title="削除">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
            </div>
        @else
            <form action="{{ route('teacher.attendance.snack', ['id' => $reservation->id]) }}" method="POST">
                @csrf
                <input type="hidden" name="nonmember" value="{{ $isNonmember ? '1' : '0' }}">
                <button type="submit" class="btn btn-primary btn-sm">おやつ利用</button>
            </form>
        @endif
    </td>

    {{-- アレルギー --}}
    <td>{{ $isNonmember ? ($reservation->allergy ?? 'なし') : ($reservation->child->allergy ?? 'なし') }}</td>

    {{-- 利用料 --}}
    <td>¥{{ number_format($reservation->attendance->total_fee ?? 0) }}</td>

    
</tr>
