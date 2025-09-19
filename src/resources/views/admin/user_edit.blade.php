@extends('layouts.admin') 

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/user_edit.css') }}">
@endsection 

@section('content')
<div class="content">
    <div class="heading">
        <h2>ユーザー編集</h2>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <form class="form" action="{{ route('admin.update', $user->id) }}" method="POST">
        @csrf
        @php
        $old_names = old('contact_name', []);
        $old_relationships = old('relationship', []);
        $old_phones = old('contact_phone', []);
        $old_sibling_names = old('sibling_name', []);
        @endphp 
        <div class="form-group">
            <label class="blue" for="user_id" style="width: 150px; margin-right: 10px;">ユーザーID:</label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="user_id" name="user_id" class="form-control readonly-input" value="{{ $user->user_id }}" readonly>
            </div>
        </div>
        <div class="form-group">
            <label class="blue" for="name" style="width: 150px; margin-right: 10px;">ユーザー名:</label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="name" name="name" class="form-control editable-input" value="{{ old('name',$user->name) }}" required>
            </div>
            @error('name') 
            <div class="text-danger"> {{ $message }}</div>
            @enderror 
        </div>
        <div class="form-group">
            <label class="blue" for="password" style="width: 150px; margin-right: 10px;">パスワード:</label>
            <div style="position: relative; flex: 1;">
                <input type="password" id="password" name="password" class="form-control" value="{{ old('password') }}" placeholder="変更する時は入力してください">
                <i class="fa-solid fa-eye toggle-password" id="togglePassword"></i>
            </div> 
            @error('password') 
                <div class="text-danger"> {{ $message }}</div>
            @enderror 
        </div>
        <div class="form-group">
            <label class="blue" for="role" style="width: 150px; margin-right: 10px;">役割:</label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="role" name="role" class="form-control readonly-input" value="{{ $user->role }}" readonly>
            </div>
            @error('role') 
            <div class="text-danger"> {{ $message }}</div>
            @enderror 
        </div>
        <div class="form-group">
            <label for="address" style="width: 150px; margin-right: 10px;">住所:</label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="address1" name="address" class="form-control editable-input" value="{{ old('address',$user->address) }}">
            </div>
            @error('address') 
            <div class="text-danger"> {{ $message }}<div>
            @enderror
        </div>
        <div class="form-group">
            <label for="child_name" style="width: 150px; margin-right: 10px;">子ども名:</label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="child_name" name="child_name" class="form-control editable-input" value="{{ old('child_name', $user->child->child_name ?? '') }}">
            </div>
            @error('child_name') 
            <div class="text-danger"> {{ $message }}</div>
            @enderror 
        </div>
        <div class="form-group">
            <label for="birthday" style="width: 150px; margin-right: 10px;">生年月日:</label>
            <div style="position: relative; flex: 1;">
                <input type="date" id="birthday" name="birthday" class="form-control editable-input" value="{{ old('birthday', $user->child->birthday ?? '') }}" >
            </div>
            @error('birthday') 
            <div class="text-danger"> {{ $message }}</div>
            @enderror 
        </div>
        <div class="form-group">
            <label for="gender" style="width: 150px; margin-right: 10px;">性別:</label>
            <div style="position: relative; flex: 1;">
                <select id="gender" name="gender" class="form-control editable-input" >
                    <option value="">選択してください</option>
                    <option value="男" {{ old('gender', $user->child->gender ?? '') == '男' ? 'selected' : '' }}>男</option>
                    <option value="女" {{ old('gender', $user->child->gender ?? '') == '女' ? 'selected' : '' }}>女</option>
                </select>
            </div>
            @error('gender') 
            <div class="text-danger"> {{ $message }}</div>
            @enderror 
        </div>
        <div class="form-group">
            <label for="contact_name_1" style="width: 150px; margin-right: 10px;">連絡先名前１:</label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="contact_name_1" name="contact_name[]" class="form-control editable-input" value="{{ $old_names[0] ?? $user->contacts[0]->name ?? '' }}" >
            </div>
            @error('address')
            <div class="text-danger"> {{ $message }}<div>
            @enderror
        </div>
        <div class="form-group">
            <label for="relationship_1" style="width: 150px; margin-right: 10px;">連絡先続柄１:</label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="relationship_1" name="relationship[]" class="form-control editable-input" value="{{ $old_relationships[0] ?? $user->contacts[0]->relationship ?? '' }}" >
            </div>
            @error('relationship')
            <div class="text-danger"> {{ $message }}<div>
            @enderror
        </div>
        <div class="form-group">
            <label for="phone_number_1" style="width: 150px; margin-right: 10px;">連絡先電話１:</label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="phone_number_1" name="phone_number[]" class="form-control editable-input" value="{{ $old_phones[0] ?? $user->contacts[0]->phone ?? '' }}">
            </div>
            @error('phone_number') 
            <div class="text-danger"> {{ $message }}<div>
            @enderror 
        </div>
        <div class="form-group">
            <label for="contact_name_2" style="width: 150px; margin-right: 10px;">連絡先名前2:</label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="contact_name_2" name="contact_name[]" class="form-control editable-input" value="{{ $old_names[1] ?? $user->contacts[1]->name ?? '' }}" >
            </div>
            @error('contact_name') 
            <div class="text-danger"> {{ $message }}<div>
            @enderror 
        </div>
        <div class="form-group">
            <label for="relationship_2" style="width: 150px; margin-right: 10px;">連絡先続柄2:</label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="relationship_2" name="relationship[]" class="form-control editable-input" value="{{ $old_relationships[1] ?? $user->contacts[1]->relationship ?? '' }}" >
            </div>
            @error('relationship')
            <div class="text-danger"> {{ $message }}<div>
            @enderror
        </div>
        <div class="form-group">
            <label for="phone_number_2" style="width: 150px; margin-right: 10px;">連絡先電話２:</label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="phone_number_2" name="phone_number[]" class="form-control editable-input" value="{{ $old_phones[1] ?? $user->contacts[1]->phone ?? '' }}" >
            </div>
            @error('phone_number') 
            <div class="text-danger"> {{ $message }}<div>
            @enderror
        </div> 
        <div class="form-group">
            <label for="allergy" style="width: 150px; margin-right: 10px;">アレルギーその他:</label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="allergy" name="allergy" class="form-control editable-input" value="{{ old('allergy',$user->child->allergy ?? '') }}">
            </div>
        </div>
        <div class="form-group">
            <label for="sibling_name_1" style="width: 150px; margin-right: 10px;">兄弟姉妹名(未就学)1:</label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="sibling_name_1" name="sibling_name[]" class="form-control editable-input" value="{{ $old_sibling_names[0] ?? $user->child->siblings[0]->sibling_name ?? '' }}" >
            </div>
        </div>
        <div class="form-group">
            <label for="sibling_name_2" style="width: 150px; margin-right: 10px;">兄弟姉妹名(未就学)2:</label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="sibling_name_2" name="sibling_name[]" class="form-control editable-input" value="{{ $old_sibling_names[1] ?? $user->child->siblings[1]->sibling_name ?? '' }}" >
            </div>
        </div>
        <div class="form-group">
            <label for="sibling_name_3" style="width: 150px; margin-right: 10px;">兄弟姉妹名(未就学)3:</label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="sibling_name_3" name="sibling_name[]" class="form-control editable-input" value="{{ $old_sibling_names[2] ?? $user->child->siblings[2]->sibling_name ?? '' }}" >
            </div>
        </div>

        <button type="submit" class="btn">登録</button>
    </form>
    <div class="back__button">
        <a class="back" href="{{ route('admin.user') }}">back</a>
    </div>
</div>

<script>
    document.getElementById('togglePassword').addEventListener('click', function () {
        const passwordInput = document.getElementById('password');
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);

    // アイコン切り替え（Font Awesome用）
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });

</script>

@endsection