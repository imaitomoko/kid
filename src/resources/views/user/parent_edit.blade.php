@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/parent_edit.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>保護者情報の編集</h2>
    </div>

    <form class="form" action="{{ route('user.parent.update') }}" method="POST">
        @csrf
        @php
            $old_names = old('contact_name', []);
            $old_relationships = old('relationship', []);
            $old_phones = old('contact_phone', []);
            $contacts = $contacts->values();
        @endphp 
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
        <button type="submit" class="btn">更新する</button>
    </form>
    <div class="back__button">
        <a class="back" href="{{ url()->previous() }}">back</a>
    </div>
</div>

@endsection