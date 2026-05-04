@php
    $attendance = $reservation->attendance;
    $isAccounted = $attendance && $attendance->accounted;
    $feeTotal = $attendance ? $attendance->feeItems->sum(fn($f) => $f->feeItem->amount ?? 0) : 0;
@endphp

<tr>
    <td>
        <a href="{{ route('admin.book_detail', ['id' => $reservation->id, 'isNonmember' => $isNonmember ? 1 : 0, 'date' => $date->format('Y-m-d')]) }}">
            @if($isNonmember && optional($attendance->reservable)->is_under_3 == 2)
                <span style="color:red; font-weight:bold;">(誰通)</span>
            @endif
            {{ $isNonmember ? $reservation->child_name : optional($reservation->child)->child_name }}
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
            <form action="{{ route('attendance.start', ['id' => $reservation->id]) }}" method="POST">
                @csrf
                <input type="hidden" name="nonmember" value="{{ $isNonmember ? '1' : '0' }}">
                <button type="submit" class="btn btn-success btn-sm">利用開始</button>
            </form>
        @else
            <div class="d-flex justify-content-center align-items-center gap-1">
                <form action="{{ route('attendance.updateStartTime', $reservation) }}" method="POST" class="d-flex align-items-center">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="nonmember" value="{{ $isNonmember ? '1' : '0' }}">
                    <div class="input-group input-group-sm border border-primary rounded {{ $isAccounted ? 'opacity-50' : '' }}">
                        <input type="time" name="actual_start_time" max="{{ $attendance->actual_end_time }}"
                        value="{{ \Carbon\Carbon::parse($attendance->actual_start_time)->format('H:i') }}"
                        class="form-control form-control-sm" style="width:100px;">
                        <button type="submit" class="btn btn-primary btn-sm ms-1" title="編集" {{ $isAccounted ? 'disabled' : '' }}><i class="fa-solid fa-pen"></i></button>
                    </div>
                </form>
                <form action="{{ route('attendance.deleteStartTime', ['id' => $reservation->id]) }}" method="POST" onsubmit="return confirm('開始時刻を削除しますか？');">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="nonmember" value="{{ $isNonmember ? '1' : '0' }}">
                    <button type="submit" class="btn btn-danger btn-sm" title="削除" {{ $isAccounted ? 'disabled' : '' }}><i class="fa-solid fa-trash"></i></button>
                </form>
            </div>
        @endif
        @if(!$attendance || !$attendance->actual_end_time)
            <form action="{{ route('attendance.end',['id' => $reservation->id]) }}" method="POST">@csrf
                <input type="hidden" name="nonmember" value="{{ $isNonmember ? '1' : '0' }}">
                <button type="submit" class="btn btn-success btn-sm"{{ !$attendance || !$attendance->actual_start_time ? 'disabled' : '' }}>利用終了</button>
            </form>
        @else
            <div class="d-flex justify-content-center align-items-center gap-1">
                <form action="{{ route('attendance.updateEndTime', $reservation) }}" method="POST" class="d-flex align-items-center">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="nonmember" value="{{ $isNonmember ? '1' : '0' }}">
                    <div class="input-group input-group-sm border border-primary rounded {{ $isAccounted ? 'opacity-50' : '' }}">
                        <input type="time" name="actual_end_time"
                        value="{{ \Carbon\Carbon::parse($attendance->actual_end_time)->format('H:i') }}"
                        class="form-control form-control-sm" style="width:100px;">
                        <button type="submit" class="btn btn-primary btn-sm ms-1" title="編集" {{ $isAccounted ? 'disabled' : '' }}><i class="fa-solid fa-pen"></i></button>
                    </div>
                    
                </form>
                <form action="{{ route('attendance.deleteEndTime', ['id' => $reservation->id]) }}" method="POST" onsubmit="return confirm('利用終了を削除しますか？');">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="nonmember" value="{{ $isNonmember ? '1' : '0' }}">
                    <button type="submit" class="btn btn-danger btn-sm" title="削除" {{ $isAccounted ? 'disabled' : '' }}><i class="fa-solid fa-trash"></i></button>
                </form>
            </div>
        @endif
    </td>

    <td>
        @if($reservation->meal)
            <div class="d-flex align-items-center justify-content-center gap-1">
                <span>給食</span>
                <form action="{{ route('attendance.meal.delete', ['id' => $reservation->id]) }}" method="POST" onsubmit="return confirm('給食利用を削除しますか？');">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="nonmember" value="{{ $isNonmember ? '1' : '0' }}">
                    <button type="submit" class="btn btn-danger btn-sm" title="削除" {{ $isAccounted ? 'disabled' : '' }}>
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
            </div>
        @else
            <form action="{{ route('attendance.meal', ['id' => $reservation->id]) }}" method="POST">
                @csrf
                <input type="hidden" name="nonmember" value="{{ $isNonmember ? '1' : '0' }}">
                <button type="submit" class="btn btn-primary btn-sm" {{ $isAccounted ? 'disabled' : '' }}>給食利用</button>
            </form>
        @endif

        @if($reservation->snack)
            <div class="d-flex align-items-center justify-content-center gap-1">
                <span>おやつ</span>
                <form action="{{ route('attendance.snack.delete', ['id' => $reservation->id]) }}" method="POST" onsubmit="return confirm('おやつ利用を削除しますか？');">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="nonmember" value="{{ $isNonmember ? '1' : '0' }}">
                    <button type="submit" class="btn btn-danger btn-sm" title="削除" {{ $isAccounted ? 'disabled' : '' }}>
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
            </div>
        @else
            <form action="{{ route('attendance.snack', ['id' => $reservation->id]) }}" method="POST">
                @csrf
                <input type="hidden" name="nonmember" value="{{ $isNonmember ? '1' : '0' }}">
                <button type="submit" class="btn btn-primary btn-sm" {{ $isAccounted ? 'disabled' : '' }}>おやつ利用</button>
            </form>
        @endif
    </td>

    {{-- アレルギー --}}
    <td>{{ $isNonmember ? ($reservation->allergy ?? 'なし') : ($reservation->child->allergy ?? 'なし') }}</td>

    {{-- 利用料 --}}
    <td>¥{{ number_format($reservation->attendance->total_fee ?? 0) }}</td>

    {{-- 会計チェック --}}
    <td>
        @if($attendance)
            <form action="{{ route('admin.book_list.accounted') }}" method="POST">@csrf
                <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
                <input type="checkbox" name="accounted" value="1"
                    onchange="this.form.submit()"
                    {{ $attendance->accounted ? 'checked' : '' }}>
            </form>
        @else
            ー
        @endif
    </td>
</tr>
