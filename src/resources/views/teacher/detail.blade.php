@extends('layouts.teacher')

@section('css')
<link rel="stylesheet" href="{{ asset('css/teacher/detail.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h3>
            {{ $isNonmember ? $reservation->child_name : $reservation->child->child_name }}
        </h3>
        @if(!empty($date))
            <h4>{{ \Carbon\Carbon::parse($date)->format('Y年m月d日') }}</h4>
        @endif
    </div>

    <table class="table table-bordered">
        <tr>
            <th>年齢区分:</th>
            <td>{{ $typeName ?? ''}}</td>
        </tr>
        <tr>
            <th>利用回数:</th>
            <td>{{ $reservation->round_type ?? '―' }}</td>
        </tr>
        <tr>
            <th>利用目的:</th>
            <td>{{ $reservation->purpose ?? '―' }}</td>
        </tr>
        <tr>
            <th>保護者名:</th>
            <td>{{ $isNonmember ? $reservation->guardian_name ?? '' : ($reservation->child->user->name ?? '') }}</td>
        </tr>
        <tr>
            <th>住所:</th>
            <td>{{ $isNonmember ? $reservation->address ?? '' : ($reservation->child->user->address ?? '') }}</td>
        </tr>
        <tr>
            <th>連絡先1:</th>
            <td>
                @php
                    $contact1 = $isNonmember ? $reservation->contact_name_1 ?? '' : ($reservation->child->user->contacts[0]->contact_name ?? '');
                    $relation1 = $isNonmember ? $reservation->relationship_1 ?? '' : ($reservation->child->user->contacts[0]->relationship ?? '');
                    $phone1 = $isNonmember ? $reservation->phone_number_1 ?? '' : ($reservation->child->user->contacts[0]->phone_number ?? '');
                @endphp
                {{ $contact1 }}（{{ $relation1 }}）{{ $phone1 }}
            </td>
        </tr>
        <tr>
            <th>連絡先2:</th>
            <td>
                @php
                    $contact2 = $isNonmember ? $reservation->contact_name_2 ?? '' : ($reservation->child->user->contacts[1]->contact_name ?? '');
                    $relation2 = $isNonmember ? $reservation->relationship_2 ?? '' : ($reservation->child->user->contacts[1]->relationship ?? '');
                    $phone2 = $isNonmember ? $reservation->phone_number_2 ?? '' : ($reservation->child->user->contacts[1]->phone_number ?? '');
                @endphp
                {{ $contact2 }}（{{ $relation2 }}）{{ $phone2 }}
            </td>
        </tr>
        <tr>
            <th>生年月日:</th>
            <td>
                @if($isNonmember)
                    {{ $reservation->birthday ? \Carbon\Carbon::parse($reservation->birthday)->format('Y-m-d') : '' }}
                @else
                    {{ $reservation->child->birthday ? \Carbon\Carbon::parse($reservation->child->birthday)->format('Y年m月d日') : '' }}
                @endif
            </td>
        </tr>
        
        <tr>
            <th>兄弟クラス:</th>
            <td>
                @if($isNonmember)
                    {{ $reservation->sibling_class ?? '' }}
                @else
                    {{ $reservation->child->siblings->pluck('sibling_class')->join('、') ?? '' }}
                @endif
            </td>
        </tr>
        <tr>
            <th>兄弟名:</th>
            <td>
                @if($isNonmember)
                    {{ $reservation->sibling_name ?? '' }}
                @else
                    {{ $reservation->child->siblings->pluck('sibling_name')->join('、') ?? '' }}
                @endif
            </td>
        </tr>
        <tr>
            <th>備考:</th>
            <td>{{ $reservation->note ?? '' }}</td>
        </tr>
    </table>
    <div class="back__button">
        <a class="back" href="{{ route('teacher.reservation.list', ['date' => $date]) }}">back</a>
    </div>
</div>
@endsection
