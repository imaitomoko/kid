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
            <label for="contact_name_1" style="width: 150px; margin-right: 10px;">連絡先名前１:</label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="contact_name_1" name="contact_name[]" class="form-control editable-input" value="{{ $old_contact_names[0] ?? $user->contacts[0]->contact_name ?? '' }}" >
            </div>
            @error('contact_name.0')
            <div class="text-danger"> {{ $message }}<div>
            @enderror
        </div>
        <div class="form-group">
            <label for="relationship_1" style="width: 150px; margin-right: 10px;">連絡先続柄１:</label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="relationship_1" name="relationship[]" class="form-control editable-input" value="{{ $old_relationships[0] ?? $user->contacts[0]->relationship ?? '' }}" >
            </div>
            @error('relationship.0')
            <div class="text-danger"> {{ $message }}<div>
            @enderror
        </div>
        <div class="form-group">
            <label for="phone_number_1" style="width: 150px; margin-right: 10px;">連絡先電話１:</label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="phone_number_1" name="phone_number[]" class="form-control editable-input" value="{{ $old_phone_numbers[0] ?? $user->contacts[0]->phone_number ?? '' }}">
            </div>
            @error('phone_number.0') 
            <div class="text-danger"> {{ $message }}<div>
            @enderror 
        </div>
        <div class="form-group">
            <label for="contact_name_2" style="width: 150px; margin-right: 10px;">連絡先名前2:</label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="contact_name_2" name="contact_name[]" class="form-control editable-input" value="{{ $old_contact_names[1] ?? $user->contacts[1]->contact_name ?? '' }}" >
            </div>
            @error('contact_name.1') 
            <div class="text-danger"> {{ $message }}<div>
            @enderror 
        </div>
        <div class="form-group">
            <label for="relationship_2" style="width: 150px; margin-right: 10px;">連絡先続柄2:</label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="relationship_2" name="relationship[]" class="form-control editable-input" value="{{ $old_relationships[1] ?? $user->contacts[1]->relationship ?? '' }}" >
            </div>
            @error('relationship.1')
            <div class="text-danger"> {{ $message }}<div>
            @enderror
        </div>
        <div class="form-group">
            <label for="phone_number_2" style="width: 150px; margin-right: 10px;">連絡先電話２:</label>
            <div style="position: relative; flex: 1;">
                <input type="text" id="phone_number_2" name="phone_number[]" class="form-control editable-input" value="{{ $old_phone_numbers[1] ?? $user->contacts[1]->phone_number ?? '' }}" >
            </div>
            @error('phone_number.1') 
            <div class="text-danger"> {{ $message }}<div>
            @enderror
        </div>
        @php
            $children = old('children', $user->children->toArray());
        @endphp

        @foreach($children as $i => $child)
        <div class="child-block">

            <div class="form-group">
                <label>子ども名{{ $i + 1 }}</label>
                <input type="hidden"
                    name="children[{{ $i }}][id]"
                    value="{{ old("children.$i.id", $child['id'] ?? '') }}">

                <input type="text"
                    name="children[{{ $i }}][child_name]"
                    class="form-control"
                    value="{{ old("children.$i.child_name", $child['child_name'] ?? '') }}">
                @error("children.$i.child_name")
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>生年月日{{ $i + 1 }}</label>
                <input type="date"
                    name="children[{{ $i }}][birthday]"
                    class="form-control"
                    value="{{ old("children.$i.birthday", isset($child['birthday']) ? \Carbon\Carbon::parse($child['birthday'])->format('Y-m-d') : '') }}">
                @error("children.$i.birthday")
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>性別{{ $i + 1 }}</label>
                <select name="children[{{ $i }}][gender]" class="form-control">
                    <option value="">選択</option>
                    <option value="男" {{ old("children.$i.gender", $child['gender'] ?? '') == '男' ? 'selected' : '' }}>男</option>
                    <option value="女" {{ old("children.$i.gender", $child['gender'] ?? '') == '女' ? 'selected' : '' }}>女</option>
                </select>
                @error("children.$i.gender")
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>アレルギー</label>
                <input type="text"
                    name="children[{{ $i }}][allergy]"
                    class="form-control"
                    value="{{ $child['allergy'] ?? '' }}">
            </div>

        </div>
        @endforeach 

        <button type="submit" class="btn">修正</button>
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