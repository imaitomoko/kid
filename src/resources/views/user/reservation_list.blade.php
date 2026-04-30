@extends('layouts.app') 

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/reservation_list.css') }}">
@endsection 

@section('content')
<div class="content">
    <div class="heading">
        <h2>{{ $date }} の予約枠</h2>
    </div>

    @if(session('error'))
        <div class="alert alert-success">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('user.reservation.confirm') }}" method="POST">
        @csrf
        <input type="hidden" name="date" value="{{ $date }}">

        <table class="custom-table">
            <thead>
                <tr>
                    <th>時間帯</th>
                    <th>空き人数</th>
                    <th>選択</th>
                </tr>
            </thead>
            <tbody>
                @foreach($slots as $slot)
                    @php
                        $available = $slot->capacity;
                    @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($slot->slot_time)->format('H:i') }}  ~ 
                        {{ \Carbon\Carbon::parse($slot->slot_time)->addMinutes(30)->format('H:i') }}</td>
                        <td>{{ $available }}</td>
                        <td>
                            @if($available > 0)
                                <input type="checkbox" name="reservation_slot_ids[]" value="{{ $slot->id }}" data-time="{{ \Carbon\Carbon::parse($slot->slot_time)->format('H:i') }}">
                            @else
                                <input type="checkbox" disabled>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="form-wrapper">
            <div class="form-check-group">
                <div class="form-check form-check-inline">
                    <input type="checkbox" class="form-check-input" id="meal" name="meal" value="1" {{ old('meal') ? 'checked' : '' }}>
                    <label class="form-check-label" for="meal">給食</label>
                </div>
                <div class="form-check form-check-inline">
                    <input type="checkbox" class="form-check-input" id="snack" name="snack" value="1" {{ old('snack') ? 'checked' : '' }}>
                    <label class="form-check-label" for="snack">おやつ</label>
                </div>
            </div>
            <div class="form-select-group">
                <div class="form-select-type">
                    <label for="round_type" class="form-label me-2 mb-0">回数</label>
                    <select name="round_type" id="round_type" class="form-select select-equal">
                        <option value="初回">初回</option>
                        <option value="2回目">2回目</option>
                        <option value="3回目以上">3回目以上</option>
                    </select>
                </div>
                <div class="form-select-type">
                    <label for="purpose" class="form-label me-2 mb-0">目的</label>
                    <select name="purpose" id="purpose" class="form-select select-equal" required>
                        <option value="一般">一般</option>
                        <option value="行事">行事</option>
                        <option value="ラビットクラブ">ラビットクラブ</option>
                        <option value="ママ">ママ</option>
                        <option value="誕生会">誕生会</option>
                    </select>
                </div>
            </div>
            
            <div class="note">
                <label class="form-label">備考</label>
                <textarea name="note" placeholder="予約時刻が30分単位でない場合（9:45など）ご記入ください" class="form-control">{{ old('note') }}</textarea>
            </div>
        </div>

        <div class="text-center mt-4">
            <button type="submit" class="btn btn-primary btn-lg" style="width: 200px;">確認</button>
        </div>
    </form>

    <div class="back__button">
        <a class="back" href="{{ route('user.reservation') }}">back</a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkboxes = document.querySelectorAll('input[name="reservation_slot_ids[]"]');

    // スロットの時間を Date として保持
    const slotTimes = [];
    checkboxes.forEach((cb, index) => {
        slotTimes[index] = {
            checkbox: cb,
            time: new Date("2000-01-01 " + cb.dataset.time) // 後で使う
        };
    });

    checkboxes.forEach((checkbox, index) => {
        checkbox.addEventListener('change', function () {
            // チェックされたスロットのインデックスを取得
            const checkedIndexes = [];
            checkboxes.forEach((cb, i) => {
                if (cb.checked) checkedIndexes.push(i);
            });

            if (checkedIndexes.length < 2) return;

            // 開始と終了を決定
            const start = Math.min(...checkedIndexes);
            const end = Math.max(...checkedIndexes);

            // 間のチェックをすべて ON にする
            for (let i = start; i <= end; i++) {
                if (!checkboxes[i].disabled) {
                    checkboxes[i].checked = true;
                }
            }
        });
    });
});
</script>

@endsection
