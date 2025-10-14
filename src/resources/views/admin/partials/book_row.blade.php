@php
    $attendance = $reservation->attendance ?? null;
    $feeItems = $attendance->feeItems ?? collect();
    $feeTotal = $feeItems->sum(fn($f) => $f->feeItem->amount ?? 0);
@endphp

<tr>
    <td>{{ $isNonmember ? $reservation->child_name : $reservation->child->child_name }}</td>

    <td>
        @if($isNonmember)
            {{ \Carbon\Carbon::parse($reservation->start_time)->format('H:i') }}
            -
            {{ \Carbon\Carbon::parse($reservation->end_time)->format('H:i') }}
        @else
            {{ $reservation->slot->slot_time }} - <br>{{ $attendance?->actual_end_time ?? '--:--' }}
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
                    <input type="time" name="actual_start_time"
                        value="{{ \Carbon\Carbon::parse($attendance->actual_start_time)->format('H:i') }}"
                        class="form-control form-control-sm" style="width:100px;">
                    <button type="submit" class="btn btn-primary btn-sm ms-1" title="編集" ><i class="fa-solid fa-pen"></i></button>
                </form>
                <form action="{{ route('attendance.deleteStartTime', ['id' => $reservation->id]) }}" method="POST" onsubmit="return confirm('開始時刻を削除しますか？');">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="nonmember" value="{{ $isNonmember ? '1' : '0' }}">
                    <button type="submit" class="btn btn-danger btn-sm" title="削除"><i class="fa-solid fa-trash"></i></button>
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
                    <input type="time" name="actual_end_time"
                        value="{{ \Carbon\Carbon::parse($attendance->actual_end_time)->format('H:i') }}"
                        class="form-control form-control-sm" style="width:100px;">
                    <button type="submit" class="btn btn-primary btn-sm ms-1" title="編集"><i class="fa-solid fa-pen"></i></button>
                </form>
                <form action="{{ route('attendance.deleteEndTime', ['id' => $reservation->id]) }}" method="POST" onsubmit="return confirm('開始時刻を削除しますか？');">
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
                <form action="{{ route('attendance.meal.delete', ['id' => $reservation->id]) }}" method="POST" onsubmit="return confirm('給食利用を削除しますか？');">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="nonmember" value="{{ $isNonmember ? '1' : '0' }}">
                    <button type="submit" class="btn btn-danger btn-sm" title="削除">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
            </div>
        @else
            <form action="{{ route('attendance.meal', ['id' => $reservation->id]) }}" method="POST">
                @csrf
                <input type="hidden" name="nonmember" value="{{ $isNonmember ? '1' : '0' }}">
                <button type="submit" class="btn btn-primary btn-sm">給食利用</button>                
            </form>
        @endif

        <br>

        @if($reservation->snack)
            <div class="d-flex align-items-center justify-content-center gap-1">
                <span>おやつ</span>
                <form action="{{ route('attendance.snack.delete', ['id' => $reservation->id]) }}" method="POST" onsubmit="return confirm('おやつ利用を削除しますか？');">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="nonmember" value="{{ $isNonmember ? '1' : '0' }}">
                    <button type="submit" class="btn btn-danger btn-sm" title="削除">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
            </div>
        @else
            <form action="{{ route('attendance.snack', ['id' => $reservation->id]) }}" method="POST">
                @csrf
                <input type="hidden" name="nonmember" value="{{ $isNonmember ? '1' : '0' }}">
                <button type="submit" class="btn btn-primary btn-sm">おやつ 利用</button>
            </form>
        @endif
    </td>

    {{-- アレルギー --}}
    <td>{{ $isNonmember ? ($reservation->allergy ?? 'なし') : ($reservation->child->allergy ?? 'なし') }}</td>

    {{-- 利用料 --}}
    <td>¥{{ number_format($feeTotal) }}</td>

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
