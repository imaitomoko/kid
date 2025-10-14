@extends('layouts.admin') 

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/fee.css') }}">
@endsection 

@section('content')
<div class="content">
    <div class="heading">
        <h2>料金登録</h2>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <div class="card">
        <div class="card-header">新規料金登録</div>
        <div class="card-body">
            <form action="{{ route('admin.fee.store') }}" method="POST">
                @csrf

                <div class="group">
                    <div class="mb-3">
                        <label class="form-label">利用名</label>
                        <input type="text" name="item_name" class="form-control" required>
                        @error('item_name') 
                            <div class="text-danger"> {{ $message}}</div>
                        @enderror 
                    </div>

                    <div class="mb-3">
                        <label class="form-label">区分</label>
                        <select name="category" class="form-select" required>
                            <option value="">選択してください</option>
                            <option value="未満児保育">未満児保育</option>
                            <option value="以上児保育">以上児保育</option>
                            <option value="給食">給食</option>
                            <option value="おやつ">おやつ</option>
                        </select>
                        @error('user_name') 
                            <div class="text-danger"> {{ $message}}</div>
                        @enderror 
                    </div>

                    <div class="mb-3">
                        <label class="form-label">単位</label>
                        <select name="unit" class="form-select" required>
                            <option value="">選択してください</option>
                            <option value="時間">時間</option>
                            <option value="1回">1回</option>
                        </select>
                        @error('unit') 
                            <div class="text-danger"> {{ $message}}</div>
                        @enderror 
                    </div>
                </div>
                <div class="group">
                    <div class="mb-3">
                        <label class="form-label">金額</label>
                        <input type="number" name="amount" class="form-control" required>
                        @error('amount') 
                            <div class="text-danger"> {{ $message}}</div>
                        @enderror 
                    </div>

                    <div class="mb-3">
                        <label class="form-label">適用開始日</label>
                        <input type="date" name="start_date" class="form-control" required>
                        @error('start_date') 
                            <div class="text-danger"> {{ $message}}</div>
                        @enderror 
                    </div>

                    <div class="mb-3">
                        <label class="form-label">適用終了日</label>
                        <input type="date" name="end_date" class="form-control">
                        @error('end_date') 
                            <div class="text-danger"> {{ $message}}</div>
                        @enderror 
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">登録する</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">登録済み料金一覧</div>
        <div class="card-body">
            @if($feeItems->isEmpty())
                <p>登録された料金はありません。</p>
            @else
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>利用名</th>
                            <th>区分</th>
                            <th>単位</th>
                            <th>金額</th>
                            <th>適用開始日</th>
                            <th>適用終了日</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($feeItems as $item)
                            <tr>
                                <td>
                                    <input type="text" name="item_name" value="{{ $item->item_name }}" class="form-control">
                                </td>
                                <td>
                                    <select name="category" class="form-select">
                                        <option value="未満児保育" {{ $item->category == '未満児保育' ? 'selected' : '' }}>未満児保育</option>
                                        <option value="以上児保育" {{ $item->category == '以上児保育' ? 'selected' : '' }}>以上児保育</option>
                                        <option value="給食" {{ $item->category == '給食' ? 'selected' : '' }}>給食</option>
                                        <option value="おやつ" {{ $item->category == 'おやつ' ? 'selected' : '' }}>おやつ</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="unit" class="form-select">
                                        <option value="時間" {{ $item->unit == '時間' ? 'selected' : '' }}>時間</option>
                                        <option value="1回" {{ $item->unit == '1回' ? 'selected' : '' }}>1回</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="amount" value="{{ $item->amount }}" class="form-control">
                                </td>
                                <td>
                                    <input type="date" name="start_date" value="{{ \Carbon\Carbon::parse($item->start_date)->format('Y-m-d') }}" class="form-control">
                                </td>
                                <td>
                                    <input type="date" name="end_date" value="{{ $item->end_date ? \Carbon\Carbon::parse($item->end_date)->format('Y-m-d') : '' }}" class="form-control">
                                </td>
                                <td>
                                    <div class="button-group">
                                        <form action="{{ route('admin.fee.update', $item->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-success btn-sm">更新</button>
                                        </form>
                                        <form action="{{ route('admin.fee.destroy', $item->id) }}" method="POST" onsubmit="return confirm('本当に削除しますか？');">
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
            @endif
        </div>
    </div>
    <div class="back__button">
        <a class="back" href="{{ route('admin') }}">back</a>
    </div>
</div>
@endsection

