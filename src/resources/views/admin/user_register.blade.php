@extends('layouts.admin') 

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/user_register.css') }}">
@endsection 

@section('content')
<div class="content">
    <div class="heading">
        <h2>ユーザー新規登録</h2>
    </div>
    <form class="form" action="" method="POST">
        @csrf 
        <div class="form-group">
            <label for="user_id">ユーザーID:</label>
            <input type="text" id="user_id" name="user_id" class="form-control" value="{{ old('user_id') }}" required>
            @error('user_id') 
            <div class="text-danger"> {{ $message}}</div>
            @enderror 
        </div>
        <div class="form-group">
            <label for="user_name">保護者名:</label>
            <input type="text" id="user_name" name="user_name" class="form-control" value="{{ old('user_name') }}" required>
            @error('user_name') 
            <div class="text-danger"> {{ $message }}</div>
            @enderror 
        </div>
        <div class="form-group">
            <label for="password">パスワード:</label>
            <input type="password" id="password" name="password" class="form-control" required>
            @error('password') 
            <div class="text-danger"> {{ $message }}</div>
            @enderror 
        </div>
        <div class="form-group">
            <label for="child_name">子ども名:</label>
            <input type="string" id="child_name" name="child_name" class="form-control" >
            @error('child_name') 
            <div class="text-danger"> {{ $message }}</div>
            @enderror 
        </div>
        <div class="form-group">
            <label for="birthday">生年月日:</label>
            <input type="date" id="birthday" name="birthday" class="form-control" value="{{ old('birthday') }}" required>
            @error('birthday') 
            <div class="text-danger"> {{ $message }}</div>
            @enderror 
        </div>
        <div class="form-group">
            <label for="address">住所:</label>
            <input type="text" id="address1" name="address" class="form-control" value="{{ old('address') }}"required>
            @error('address') 
            <div class="text-danger"> {{ $message }}<div>
            @enderror
        </div>
        <div class="form-group">
            <label for="contact_name_1">連絡先名前１:</label>
            <input type="text" id="contact_name_1" name="contact_name_1" class="form-control" value="{{ old('contact_name_1') }}" required>
            @error('address')
            <div class="text-danger"> {{ $message }}<div>
            @enderror
        </div>
        <div class="form-group">
            <label for="contact_tell_1">連絡先電話１:</label>
            <input type="text" id="contact_tell_1" name="contact_tell_1" class="form-control" value="{{ old('contact_tell_1') }}" required>
            @error('address') 
            <div class="text-danger"> {{ $message }}<div>
            @enderror 
        </div>
        <div class="form-group"><label for="contact_name_2">連絡先名前2:</label>
            <input type="text" id="contact_name_2" name="contact_name_2" class="form-control" value="{{ old('contact_name_2') }}" required>
            @error('address') 
            <div class="text-danger"> {{ $message }}<div>
            @enderror 
        </div>
        <div class="form-group"><label for="contact_tell_2">連絡先電話2:</label>
            <input type="text" id="contact_tell_2" name="contact_tell_2" class="form-control" value="{{ old('contact_tell_2') }}" required>
            @error('address') 
            <div class="text-danger"> {{ $message }}<div>
            @enderror 
        </div>
        <div class="form-group"><label for="other">アレルギーその他:</label>
            <input type="text" id="other" name="other" class="form-control" value="{{ old('other') }}">
        </div>
        <div class="form-group"><label for="brother">未就学の兄弟姉妹と生年月日:</label>
            <input type="text" id="brother" name="brother" class="form-control" value="{{ old('brother') }}" >
        </div>
        <button type="submit" class="btn btn-primary">登録</button>
    </form>
    <div class="back__button">
        <a class="back" href="{{ route('admin.user') }}">back</a>
    </div>
</div>
@endsection