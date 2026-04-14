@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/child_edit.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>登録情報</h2>
    </div>

    <form class="form" action="{{ route('user.child.update') }}" method="POST">
        @csrf
        @method('PUT')
        @php
            $old_sibling_names = old('sibling_name', []);
        @endphp 
        <div class="form-group">
            <label for="child_name" class="form-label">子ども名:</label>
            <div class="form-input">
                <input type="text" id="child_name" name="child_name" autocomplete="name" class="form-control" value="{{ old('child_name', $child->child_name ?? '') }}">
            </div>
            @error('child_name') 
            <div class="text-danger"> {{ $message }}</div>
            @enderror 
        </div>
        <div class="form-group">
            <label for="birthday" class="form-label">生年月日:</label>
            <div class="form-input">
                <input type="date" id="birthday" name="birthday" autocomplete="bday" class="form-control" value="{{ old('birthday', isset($child) ? $child->birthday->format('Y-m-d') : '') }}">

            </div>
            @error('birthday') 
            <div class="text-danger"> {{ $message }}</div>
            @enderror 
        </div>
        <div class="form-group">
            <label for="gender" class="form-label">性別:</label>
            <div class="form-input">
                <select id="gender" name="gender" autocomplete="sex" class="form-control" >
                    <option value="">選択してください</option>
                    <option value="男" {{ old('gender', $child->gender ?? '') == '男' ? 'selected' : '' }}>男</option>
                    <option value="女" {{ old('gender', $child->gender ?? '') == '女' ? 'selected' : '' }}>女</option>
                </select>
            </div>
            @error('gender') 
            <div class="text-danger"> {{ $message }}</div>
            @enderror 
        </div>
        <div class="form-group">
            <label for="allergy" class="form-label">アレルギーその他:</label>
            <div class="form-input">
                <input type="text" id="allergy" name="allergy" class="form-control" value="{{ old('allergy', $child->allergy ?? '') }}">
            </div>
        </div>
        <div class="form-group">
            <label for="sibling_name_1" class="form-label">兄弟姉妹名(未就学)1:</label>
            <div class="form-input">
                <input type="text" id="sibling_name_1" name="sibling_name[]" autocomplete="name" class="form-control" value="{{ old('sibling_name.0', $siblings[0]->sibling_name ?? '') }}" >
            </div>
        </div>
        <div class="form-group">
            <label for="sibling_name_2" class="form-label">兄弟姉妹名(未就学)2:</label>
            <div class="form-input">
                <input type="text" id="sibling_name_2" name="sibling_name[]" autocomplete="name" class="form-control" value="{{ old('sibling_name.1', $siblings[1]->sibling_name ?? '') }}" >
            </div>
        </div>
        <div class="form-group">
            <label for="sibling_name_3" class="form-label">兄弟姉妹名(未就学)3:</label>
            <div class="form-input">
                <input type="text" id="sibling_name_3" name="sibling_name[]" autocomplete="name" class="form-control" value="{{ old('sibling_name.2', $siblings[2]->sibling_name ?? '') }}" >
            </div>
        </div>
        <button type="submit" class="btn">更新</button>
    </form>
    <div class="back__button">
        <a class="back" href="{{ url()->previous() }}">back</a>
    </div>
</div>

@endsection