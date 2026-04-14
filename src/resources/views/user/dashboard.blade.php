@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/dashboard.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>一時預かり予約</h2>
        <h3>{{ $user->name }}さん</h3>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="reservation">
        @if(!$user->isProfileComplete())
            <div class="alert alert-warning">
                <p>ユーザー情報を登録すると予約ボタンが表示されます</p>
            </div>
            <a href="{{ route('user.profile') }}" class="btn btn-secondary">ユーザー情報を登録する</a>
        @else
            <p>予約するお子様を選択してください</p>
            <div class="child-buttons">
                @foreach($children as $child)
                    <a href="{{ route('user.child.select', ['child_id' => $child->id]) }}" class="select-child">
                        {{ $child->child_name }}
                    </a>
                @endforeach
            </div>
            <div class="sub-actions">
                <a href="{{ route('user.child.create') }}" class="btn-outline">+ お子様を追加</a>
                <a href="{{ route('user.parent.edit') }}" class="btn-text">
                    ⚙ 保護者情報の編集
                </a>
            </div>

        @endif
    </div>
    

</div>
@endsection