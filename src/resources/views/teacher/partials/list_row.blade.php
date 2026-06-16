@php
    $attendance = $reservation->attendance;
    $isAccounted = $attendance && $attendance->accounted;
    $feeTotal = $attendance ? $attendance->feeItems->sum(fn($f) => $f->feeItem->amount ?? 0) : 0;
    $query = http_build_query([
                'isNonmember' => $isNonmember ? 1 : 0,
                'date' => $date->format('Y-m-d'),
            ]);
@endphp

<tr>
    <td>
        <a href="{{ route('teacher.attendance.detail', $reservation->id) . '?' . $query }}">
            @if($isNonmember && in_array(optional(optional($attendance)->reservable)->is_under_3, [2, 3, 4]))
                <span style="color:red; font-weight:bold;">(誰通)</span>
            @endif

            @if(optional($reservation)->purpose === 'ママ')
                <span style="color:hotpink; font-weight:bold;">(ママ)</span>
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
            <form action="{{ route('teacher.attendance.start', ['id' => $reservation->id]) }}" method="POST">
                @csrf
                <input type="hidden" name="nonmember" value="{{ $isNonmember ? '1' : '0' }}">
                <button type="submit" class="btn btn-success btn-sm">利用開始</button>
            </form>
        @else
            <div class="time-form-wrap">
                <form action="{{ route('teacher.attendance.updateStartTime', $reservation->id) }}" method="POST" class="time-form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="nonmember" value="{{ $isNonmember ? '1' : '0' }}">
                    <div class="input-group input-group-sm border border-primary rounded {{ $isAccounted ? 'opacity-50' : '' }}">
                        <input type="time" name="actual_start_time" max="{{ $attendance->actual_end_time }}"
                        value="{{ \Carbon\Carbon::parse($attendance->actual_start_time)->format('H:i') }}"
                        class="form-control form-control-sm">
                        <button type="submit" class="btn btn-primary btn-sm" title="編集" {{ $isAccounted ? 'disabled' : '' }}><i class="fa-solid fa-pen"></i></button>
                    </div>
                </form>
                <form action="{{ route('teacher.attendance.deleteStartTime', ['id' => $reservation->id]) }}" method="POST" onsubmit="return confirm('開始時刻を削除しますか？');">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="nonmember" value="{{ $isNonmember ? '1' : '0' }}">
                    <button type="submit" class="btn btn-danger btn-sm" title="削除" {{ ($attendance->actual_end_time || $isAccounted) ? 'disabled' : '' }}><i class="fa-solid fa-trash"></i></button>
                </form>
            </div>
        @endif
        @if(!$attendance || !$attendance->actual_end_time)
            <form action="{{ route('teacher.attendance.end',['id' => $reservation->id]) }}" method="POST">@csrf
                <input type="hidden" name="nonmember" value="{{ $isNonmember ? '1' : '0' }}">
                <button type="submit" class="btn btn-success btn-sm"{{ !$attendance || !$attendance->actual_start_time ? 'disabled' : '' }}>利用終了</button>
            </form>
        @else
            <div class="time-form-wrap">
                <form action="{{ route('teacher.attendance.updateEndTime', $reservation) }}" method="POST" class="time-form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="nonmember" value="{{ $isNonmember ? '1' : '0' }}">
                    <div class="input-group input-group-sm border border-primary rounded {{ $isAccounted ? 'opacity-50' : '' }}">
                        <input type="time" name="actual_end_time" min="{{ $attendance->actual_start_time }}"
                        value="{{ \Carbon\Carbon::parse($attendance->actual_end_time)->format('H:i') }}"
                        class="form-control form-control-sm">
                        <button type="submit" class="btn btn-primary btn-sm" title="編集" {{ $isAccounted ? 'disabled' : '' }}><i class="fa-solid fa-pen"></i></button>
                    </div>
                </form>
                <form action="{{ route('teacher.attendance.deleteEndTime', ['id' => $reservation->id]) }}" method="POST" onsubmit="return confirm('利用終了を削除しますか？');">
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
                <form action="{{ route('teacher.attendance.meal.delete', ['id' => $reservation->id]) }}" method="POST" onsubmit="return confirm('給食利用を削除しますか？');">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="nonmember" value="{{ $isNonmember ? '1' : '0' }}">
                    <button type="submit" class="btn btn-danger btn-sm" title="削除" {{ $isAccounted ? 'disabled' : '' }}>
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
            </div>
        @else
            <form action="{{ route('teacher.attendance.meal', ['id' => $reservation->id]) }}" method="POST">
                @csrf
                <input type="hidden" name="nonmember" value="{{ $isNonmember ? '1' : '0' }}">
                <button type="submit" class="btn btn-primary btn-sm" {{ $isAccounted ? 'disabled' : '' }}>給食利用</button>
            </form>
        @endif

        @if($reservation->snack)
            <div class="d-flex align-items-center justify-content-center gap-1">
                <span>おやつ</span>
                <form action="{{ route('teacher.attendance.snack.delete', ['id' => $reservation->id]) }}" method="POST" onsubmit="return confirm('おやつ利用を削除しますか？');">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="nonmember" value="{{ $isNonmember ? '1' : '0' }}">
                    <button type="submit" class="btn btn-danger btn-sm" title="削除" {{ $isAccounted ? 'disabled' : '' }}>
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
            </div>
        @else
            <form action="{{ route('teacher.attendance.snack', ['id' => $reservation->id]) }}" method="POST">
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

    {{-- 会計状態 --}}
    <td>
        @if($attendance)
            <span class="account-status {{ $attendance->accounted ? 'done' : 'not-yet' }}">
                {{ $attendance->accounted ? '済' : '未' }}
            </span>
        @else
            ー
        @endif
    </td>
</tr>
