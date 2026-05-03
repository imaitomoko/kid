@extends('layouts.admin') 

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/user_register.css') }}">
@endsection 

@section('content')
<div class="content">
    <div class="heading">
        <h2>ユーザー新規登録</h2>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <form class="form" action="{{ route('admin.store') }}" method="POST">
        @csrf
        @php
        $old_names = old('contact_name', []);
        $old_relationships = old('relationship', []);
        $old_phones = old('contact_phone', []);
        $old_child_names = old('child_name', []);
        $old_birthdays = old('birthday', []);
        $old_genders = old('gender', []);
        $old_allergys = old('allergy', []);
        @endphp 
        <div class="form-group">
            <label class="blue" for="user_id" style="width: 150px; margin-right: 10px;">ユーザーID:<span class="required-label">(必須)</span></label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="user_id" name="user_id" class="form-control" value="{{ old('user_id') }}" required>
            </div>
            @error('user_id') 
            <div class="text-danger"> {{ $message}}</div>
            @enderror 
        </div>
        <div class="form-group">
            <label class="blue" for="name" style="width: 150px; margin-right: 10px;">ユーザー名:<span class="required-label">(必須)</span></label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            @error('name') 
            <div class="text-danger"> {{ $message }}</div>
            @enderror 
        </div>
        <div class="form-group">
            <label class="blue" for="password" style="width: 150px; margin-right: 10px;">パスワード:<span class="required-label">(必須)</span></label>
            <div style="position: relative; flex: 1;">
                <input type="password" id="password" name="password" class="form-control" required autocomplete="new-password">
                <i class="fa-solid fa-eye toggle-password" id="togglePassword"></i>
            </div> 
            @error('password') 
                <div class="text-danger"> {{ $message }}</div>
            @enderror 
        </div>
        <div class="form-group">
            <label class="blue" for="role" style="width: 150px; margin-right: 10px;">役割:<span class="required-label">(必須)</span></label>
            <div style="position: relative; flex: 1;">
                <select id="role" name="role" class="form-control" required>
                    <option value="">選択してください</option>
                    <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>user</option>
                    <option value="teacher" {{ old('role') == 'teacher' ? 'selected' : '' }}>teacher</option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>admin</option>
                </select>
            </div>
            @error('role') 
            <div class="text-danger"> {{ $message }}</div>
            @enderror 
        </div>
        <div class="form-group">
            <label for="address" style="width: 150px; margin-right: 10px;">住所:</label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="address1" name="address" class="form-control" value="{{ old('address') }}">
            </div>
            @error('address') 
            <div class="text-danger"> {{ $message }}<div>
            @enderror
        </div>
        <div class="form-group">
            <label for="contact_name_1" style="width: 150px; margin-right: 10px;">連絡先名前１:</label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="contact_name_1" name="contact_name[]" class="form-control" value="{{ $old_names[0] ?? '' }}" >
            </div>
            @error('contact_name.0')
            <div class="text-danger"> {{ $message }}<div>
            @enderror
        </div>
        <div class="form-group">
            <label for="relationship_1" style="width: 150px; margin-right: 10px;">連絡先続柄１:</label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="relationship_1" name="relationship[]" class="form-control" value="{{ $old_relationships[0] ?? '' }}" >
            </div>
            @error('relationship.0')
            <div class="text-danger"> {{ $message }}<div>
            @enderror
        </div>
        <div class="form-group">
            <label for="phone_number_1" style="width: 150px; margin-right: 10px;">連絡先電話１:</label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="phone_number_1" name="phone_number[]" class="form-control" value="{{ $old_phones[0] ?? '' }}">
            </div>
            @error('phone_number.0') 
            <div class="text-danger"> {{ $message }}<div>
            @enderror 
        </div>
        <div class="form-group">
            <label for="contact_name_2" style="width: 150px; margin-right: 10px;">連絡先名前2:</label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="contact_name_2" name="contact_name[]" class="form-control" value="{{ $old_names[1] ?? '' }}" >
            </div>
            @error('contact_name.1') 
            <div class="text-danger"> {{ $message }}<div>
            @enderror 
        </div>
        <div class="form-group">
            <label for="relationship_2" style="width: 150px; margin-right: 10px;">連絡先続柄2:</label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="relationship_2" name="relationship[]" class="form-control" value="{{ $old_relationships[1] ?? '' }}" >
            </div>
            @error('relationship.1')
            <div class="text-danger"> {{ $message }}<div>
            @enderror
        </div>
        <div class="form-group">
            <label for="phone_number_2" style="width: 150px; margin-right: 10px;">連絡先電話２:</label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="phone_number_2" name="phone_number[]" class="form-control" value="{{ $old_phones[1] ?? '' }}" >
            </div>
            @error('phone_number.1') 
            <div class="text-danger"> {{ $message }}<div>
            @enderror
        </div> 
        <div class="form-group">
            <label for="child_name" style="width: 150px; margin-right: 10px;">子ども名1:</label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="child_name" name="child_name[]" class="form-control" value="{{ $old_child_names[0] ?? '' }}">
            </div>
            @error('child_name.0') 
            <div class="text-danger"> {{ $message }}</div>
            @enderror 
        </div>
        <div class="form-group">
            <label for="birthday" style="width: 150px; margin-right: 10px;">生年月日1:</label>
            <div style="position: relative; flex: 1;">
                <input type="date" id="birthday" name="birthday[]" class="form-control" value="{{ $old_birthday[0] ?? '' }}" >
            </div>
            @error('birthday.0') 
            <div class="text-danger"> {{ $message }}</div>
            @enderror 
        </div>
        <div class="form-group">
            <label for="gender" style="width: 150px; margin-right: 10px;">性別1:</label>
            <div style="position: relative; flex: 1;">
                <select id="gender" name="gender[]" class="form-control" >
                    <option value="">選択してください</option>
                    <option value="男" {{ (old('gender.0') == '男') ? 'selected' : '' }}>男</option>
                    <option value="女" {{ (old('gender.0') == '女') ? 'selected' : '' }}>女</option>
                </select>
            </div>
            @error('gender.0') 
            <div class="text-danger"> {{ $message }}</div>
            @enderror 
        </div>
        <div class="form-group">
            <label for="allergy" style="width: 150px; margin-right: 10px;">アレルギーその他1:</label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="allergy" name="allergy[]" class="form-control" value="{{ $old_allergy[0] ?? '' }}">
            </div>
        </div>
        <div class="form-group">
            <label for="child_name" style="width: 150px; margin-right: 10px;">子ども名2:</label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="child_name" name="child_name[]" class="form-control" value="{{ $old_child_names[1] ?? '' }}">
            </div>
            @error('child_name.1') 
            <div class="text-danger"> {{ $message }}</div>
            @enderror 
        </div>
        <div class="form-group">
            <label for="birthday" style="width: 150px; margin-right: 10px;">生年月日2:</label>
            <div style="position: relative; flex: 1;">
                <input type="date" id="birthday" name="birthday[]" class="form-control" value="{{ $old_birthday[1] ?? '' }}" >
            </div>
            @error('birthday.1') 
            <div class="text-danger"> {{ $message }}</div>
            @enderror 
        </div>
        <div class="form-group">
            <label for="gender" style="width: 150px; margin-right: 10px;">性別2:</label>
            <div style="position: relative; flex: 1;">
                <select id="gender" name="gender[]" class="form-control" >
                    <option value="">選択してください</option>
                    <option value="男" {{ (old('gender.1') == '男') ? 'selected' : '' }}>男</option>
                    <option value="女" {{ (old('gender.1') == '女') ? 'selected' : '' }}>女</option>
                </select>
            </div>
            @error('gender.1') 
            <div class="text-danger"> {{ $message }}</div>
            @enderror 
        </div>
        <div class="form-group">
            <label for="allergy" style="width: 150px; margin-right: 10px;">アレルギーその他2:</label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="allergy" name="allergy[]" class="form-control" value="{{ $old_allergy[1] ?? '' }}">
            </div>
        </div>
        <div class="form-group">
            <label for="child_name" style="width: 150px; margin-right: 10px;">子ども名3:</label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="child_name" name="child_name[]" class="form-control" value="{{ $old_child_names[2] ?? '' }}">
            </div>
            @error('child_name.2') 
            <div class="text-danger"> {{ $message }}</div>
            @enderror 
        </div>
        <div class="form-group">
            <label for="birthday" style="width: 150px; margin-right: 10px;">生年月日3:</label>
            <div style="position: relative; flex: 1;">
                <input type="date" id="birthday" name="birthday[]" class="form-control" value="{{ $old_birthday[2] ?? '' }}" >
            </div>
            @error('birthday.2') 
            <div class="text-danger"> {{ $message }}</div>
            @enderror 
        </div>
        <div class="form-group">
            <label for="gender" style="width: 150px; margin-right: 10px;">性別3:</label>
            <div style="position: relative; flex: 1;">
                <select id="gender" name="gender[]" class="form-control" >
                    <option value="">選択してください</option>
                    <option value="男" {{ (old('gender.2') == '男') ? 'selected' : '' }}>男</option>
                    <option value="女" {{ (old('gender.2') == '女') ? 'selected' : '' }}>女</option>
                </select>
            </div>
            @error('gender.2') 
            <div class="text-danger"> {{ $message }}</div>
            @enderror 
        </div>
        <div class="form-group">
            <label for="allergy" style="width: 150px; margin-right: 10px;">アレルギーその他3:</label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="allergy" name="allergy[]" class="form-control" value="{{ $old_allergy[2] ?? '' }}">
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