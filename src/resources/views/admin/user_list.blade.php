@extends('layouts.admin') 

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/user_list.css') }}">
@endsection 

@section('content')
<div class="content">
    <div class="heading">
        <h2>ユーザー一覧・編集</h2>
    </div>
    
    <div class="search">
        <form action="{{ route('admin.show') }}" method="GET" class="mb-4">
            <div class="form-group">
                <label for="role">役割:</label>
                <select id="role" name="role" class="form-control">
                    <option value="">選択してください</option>
                    @foreach($roles as $role)
                        <option value="{{ $role }}" {{ request('role') == $role ? 'selected' : '' }}>
                            {{ $role }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="name">ユーザー名:</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ request('name') }}" placeholder="ユーザー名">
            </div>
            <button type="submit" class="btn btn-primary">検索</button>
        </form>
    </div>

    @if(isset($users) && $users->isNotEmpty())
    
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ユーザーID</th>
                    <th>ユーザー名</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->user_id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.edit', $user->id) }}" class="btn btn-warning btn-sm">編集</a>
                                <form action="{{ route('admin.destroy', $user->id) }}" method="POST" onsubmit="return confirm('本当に削除しますか？');">
                                @csrf
                                @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">削除</button>
                                </form>  
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">
            {{ $users->appends(request()->query())->links() }}
        </div>
    @else
    <p>該当するデータがありません。</p>
　　 @endif
    <div class="back__button">
        <a class="back" href="{{ route('admin.user') }}">back</a>
    </div>
</div>
@endsection
