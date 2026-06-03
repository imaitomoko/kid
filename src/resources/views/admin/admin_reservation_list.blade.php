@extends('layouts.admin')
@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/admin_reservation_list.css') }}">
@endsection 

@section('content')
<div class="content">
    <div class="heading">
        <h2>予約一覧</h2>
    </div>
    <h3>{{ \Carbon\Carbon::parse($date)->format('Y年m月d日') }}</h3>
    
    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>区分</th>
                <th>子ども名</th>
                <th>時間</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
        @forelse($reservations as $r)
            <tr>
                <td>{{ $r['is_member'] ? '会員' : '非会員' }}</td>
                <td>{{ $r['child_name'] }}</td>
                <td>{{ $r['time'] }}</td>
                <td>
                    @if($r['is_member'])
                    <!-- 会員キャンセルボタン -->
                    <form action="{{ route('admin.reservation.cancel') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="child_id" value="{{ $r['child_id'] }}">
                        <input type="hidden" name="date" value="{{ $date }}">
                        <button type="submit" class="btn btn-danger btn-sm">キャンセル</button>
                    </form>
                @else
                    <!-- 非会員キャンセルボタン -->
                    <form action="{{ route('admin.reservation.cancel') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="reservation_id" value="{{ $r['id'] }}">
                        <button type="submit" class="btn btn-danger btn-sm">キャンセル</button>
                    </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center">予約はありません</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div class="d-flex mt-4">
        <a href="{{ route('admin.nonmember.create', ['date' => $date]) }}" class="btn btn-outline-primary btn-lg">
            非会員予約登録
        </a>
        <a href="{{ route('admin.member_proxy.create', ['date' => $date]) }}" class="btn btn-outline-success btn-lg">
            会員予約代行
        </a>
    </div>

    <div class="back__button">
        <a class="back" href="{{ route('admin.reservation') }}">back</a>
    </div>
</div>

@endsection
