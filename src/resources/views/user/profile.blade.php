@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/profile.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>お子様と保護者情報の登録</h2>
    </div>

    <form class="form" action="{{ route('user.profile.register') }}" method="POST">
        @csrf
        @php
        $old_names = old('contact_name', []);
        $old_relationships = old('relationship', []);
        $old_phones = old('contact_phone', []);
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
            <label for="address" class="form-label">住所:</label>
            <div class="form-input">
                <input type="text" id="address" name="address" autocomplete="street-address" class="form-control" value="{{ old('address', $user->address ?? '') }}">
            </div>
            @error('address') 
            <div class="text-danger"> {{ $message }}<div>
            @enderror
        </div>
        <div class="form-group">
            <label for="contact_name_1" class="form-label">連絡先名前１:</label>
            <div class="form-input">
                <input type="text" id="contact_name_1" name="contact_name[]" autocomplete="name" class="form-control"
                value="{{ old('contact_name.0', $contacts[0]->contact_name ?? '') }}">
            </div>
            @error('address')
            <div class="text-danger"> {{ $message }}<div>
            @enderror
        </div>
        <div class="form-group">
            <label for="relationship_1" class="form-label">連絡先続柄１:</label>
            <div class="form-input">
                <input type="text" id="relationship_1" name="relationship[]" class="form-control"
                value="{{ old('relationship.0', $contacts[0]->relationship ?? '') }}">
            </div>
            @error('relationship')
            <div class="text-danger"> {{ $message }}<div>
            @enderror
        </div>
        <div class="form-group">
            <label for="phone_number_1" class="form-label">連絡先電話１:</label>
            <div class="form-input">
                <input type="text" id="phone_number_1" name="phone_number[]" autocomplete="tel" class="form-control"
                value="{{ old('phone_number.0', $contacts[0]->phone_number ?? '') }}">
            </div>
            @error('phone_number') 
            <div class="text-danger"> {{ $message }}<div>
            @enderror 
        </div>
        <div class="form-group">
            <label for="contact_name_2" class="form-label">連絡先名前2:</label>
            <div class="form-input">
                <input type="text" id="contact_name_2" name="contact_name[]" autocomplete="name" class="form-control"
                value="{{ old('contact_name.1', $contacts[1]->contact_name ?? '') }}">
            </div>
            @error('contact_name') 
            <div class="text-danger"> {{ $message }}<div>
            @enderror 
        </div>
        <div class="form-group">
            <label for="relationship_2" class="form-label">連絡先続柄2:</label>
            <div class="form-input">
                <input type="text" id="relationship_2" name="relationship[]" class="form-control"
                value="{{ old('relationship.1', $contacts[1]->relationship ?? '') }}">
            </div>
            @error('relationship')
            <div class="text-danger"> {{ $message }}<div>
            @enderror
        </div>
        <div class="form-group">
            <label for="phone_number_2" class="form-label">連絡先電話２:</label>
            <div class="form-input">
                <input type="text" id="phone_number_2" name="phone_number[]" autocomplete="tel" class="form-control"
                value="{{ old('phone_number.1', $contacts[1]->phone_number ?? '') }}">
            </div>
            @error('phone_number') 
            <div class="text-danger"> {{ $message }}<div>
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
        <button type="submit" class="btn">登録</button>
    </form>
    <div class="back__button">
        <a class="back" href="{{ url()->previous() }}">back</a>
    </div>
</div>

@endsection