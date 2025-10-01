@extends('layouts.admin')
@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/nonmember_book.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>非会員予約登録</h2>
    </div>
    <h3>{{ \Carbon\Carbon::parse($date)->format('Y年m月d日') }}</h3>

    <form action="{{ route('admin.nonmember.store') }}" method="POST">
        @csrf
        @php
            use Carbon\Carbon;

            $startTimes = [];
            $time = Carbon::createFromTime(8, 30);
            while ($time->lte(Carbon::createFromTime(16, 30))) {
                $startTimes[] = $time->format('H:i');
                $time->addMinutes(30);
            }

            $endTimes = [];
            $time = Carbon::createFromTime(9, 0);
            while ($time->lte(Carbon::createFromTime(17, 0))) {
                $endTimes[] = $time->format('H:i');
                $time->addMinutes(30);
            }
        @endphp

        <input type="hidden" name="date" value="{{ $date }}">

        <div class="mb-3">
            <label class="form-label">子ども名</label>
            <input type="text" name="child_name" class="form-control" value="{{ old('child_name') }}" required>
            @error('child_name')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">年齢区分</label>
            <select name="is_under_3" class="form-select" value="{{ old('is_under_3') }}" required>
                <option value="1">3歳未満児</option>
                <option value="0">3歳以上児</option>
            </select>
            @error('is_under_3')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">開始時間</label>
            <select name="start_time" class="form-select" value="{{ old('start_time') }}"  required>
                @foreach($startTimes as $t)
                    <option value="{{ $t }}">{{ \Carbon\Carbon::parse($t)->format('H:i') }}</option>
                    </option>
                @endforeach
            </select>
            @error('start_time')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror

        </div>

        <div class="mb-3">
            <label class="form-label">終了時間</label>
            <select name="end_time" class="form-select" value="{{ old('end_time') }}" required>
                @foreach($endTimes as $t)
                    <option value="{{ $t }}">{{ \Carbon\Carbon::parse($t)->format('H:i') }}
                    </option>
                @endforeach
            </select>
            @error('end_time')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <span class="form-label">給食</span>
            <div class="form-check d-inline-block ms-2">
                <label class="form-check-label" for="meal">利用する</label> 
                <input type="checkbox" class="form-check-input" id="meal" name="meal" value="1">
            </div>
        </div>

        <div class="mb-3">
            <span class="form-label">おやつ</span>
            <div class="form-check d-inline-block ms-2">
                <input type="checkbox" class="form-check-input" id="snack" name="snack" value="1">
                <label class="form-check-label" for="snack">利用する</label>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">回数</label>
            <select name="round_type" class="form-select" value="{{ old('round_type') }}" required>
                <option value="">選択してください</option>
                <option value="初回">初回</option>
                <option value="2回目">2回目</option>
                <option value="3回目以上">3回目以上</option>
            </select>
            @error('round_type')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror

        </div>

        <div class="mb-3">
            <label class="form-label">目的</label>
            <select name="purpose" class="form-select" value="{{ old('purpose') }}" required>
                <option value="">選択してください</option>
                <option value="一般">一般</option>
                <option value="行事">行事</option>
                <option value="ラビットクラブ">ラビットクラブ</option>
                <option value="ママ">ママ</option>
                <option value="誕生会">誕生会</option>
            </select>
            @error('purpose')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror

        </div>

        <div class="mb-3">
            <label class="form-label">アレルギー</label>
            <input type="text" name="allergy" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">兄弟のクラス</label>
            <input type="text" name="sibling_class" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">兄弟の名前</label>
            <input type="text" name="sibling_name" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">備考</label>
            <textarea name="note" class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-primary btn-submit">登録</button>
    </form>

    <div class="back__button">
        <a class="back" href="{{ route('admin.reservation.list', ['date' => $date]) }}">back</a>
    </div>
</div>

@endsection

