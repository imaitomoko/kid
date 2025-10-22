@extends('layouts.admin')
@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/member_book.css') }}">
@endsection 

@section('content')
<div class="content">
    <div class="heading">
        <h2>会員代行予約</h2>
    </div>
    <h3>{{ \Carbon\Carbon::parse($date)->format('Y年m月d日') }}</h3>
    
    @if(session('success'))
        <div class="alert alert-success mt-3">
            {{ session('success') }}
        </div>
    @endif

    <form method="GET" action="{{ route('admin.member_proxy.create', ['date' => $date]) }}" class="mb-4 d-flex align-items-center">
        @csrf
        <input type="text" name="child_search" placeholder="子ども名を入力" class="form-control me-2 search-input" value="{{ request('child_search') }}">
        <button type="submit"class="btn btn-primary search-btn" >検索</button>
    </form>

    <form method="POST" action="{{ route('admin.member_proxy.store') }}">
        @csrf
        <input type="hidden" name="date" value="{{ $date }}">

        <div class="mb-2">
            <label class="form-label">子ども名を選択</label>
            @if($children->isEmpty())
                <p class="text-muted">一致する子どもがいません。</p>
            @else
                @foreach($children as $child)
                    <div class="form-check">
                        <input type="radio" name="child_id" value="{{ $child->id }}" id="child_{{ $child->id }}" class="form-check-input" required>
                        <label for="child_{{ $child->id }}" class="form-check-label">{{ $child->child_name }}</label>
                    </div>
                @endforeach
            @endif
            @error('child_id')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

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
                        <td>{{ \Carbon\Carbon::parse($slot->slot_time)->format('H:i') }} ~ 
                        {{ \Carbon\Carbon::parse($slot->slot_time)->addMinutes(30)->format('H:i') }}</td>
                        <td>{{ $available }}</td>
                        <td>
                            @if($available > 0)
                                <input type="checkbox" name="reservation_slot_ids[]" value="{{ $slot->id }}">
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
            <div class="form-check-group">
                <div class="form-select-group">
                    <label for="round_type" class="form-label me-2 mb-0">回数</label>
                    <select name="round_type" id="round_type" class="form-select w-auto">
                        <option value="初回">初回</option>
                        <option value="2回目">2回目</option>
                        <option value="3回目以上">3回目以上</option>
                    </select>
                </div>
                <div class="form-select-group">
                    <label for="purpose" class="form-label me-2 mb-0">目的</label>
                    <select name="purpose" id="purpose" class="form-select w-auto" required>
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
                <textarea name="note" class="form-control">{{ old('note') }}</textarea>
            </div>
        </div>

        <div class="text-center mt-4">
            <button type="submit" class="btn btn-primary btn-lg" style="width: 200px;">登録</button>
        </div>
    </form>

    <div class="back__button">
        <a class="back" href="{{ route('admin.reservation.list', ['date' => $date]) }}">back</a>
    </div>
</div>
@endsection

