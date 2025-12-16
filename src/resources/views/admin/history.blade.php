@extends('layouts.admin') 

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/history.css') }}">
@endsection 

@section('content')
<div class="content">
    <div class="heading">
        <h2>個別利用履歴</h2>
    </div>

    <div class="search">
        <form action="{{ route('admin.history.show') }}" method="GET" class="mb-4">
            <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">

            <div class="form-group">
                <input type="text" id="child_name" name="child_name" class="form-control" placeholder="子ども名を入力" value="{{ request('child_name') }}"autocomplete="off">
                <ul id="child_suggestions" class="list-group position-absolute" style="z-index: 1000;"></ul>
            </div>
            <button type="submit" class="btn btn-primary">検索</button>
        </form>
    </div>

    @php
        $prevMonth = $month->copy()->subMonth();
        $nextMonth = $month->copy()->addMonth();
    @endphp


    <div class="mb-3">
        <a href="{{ route('admin.history.show', ['child_name' => $childName, 'month' => $prevMonth->format('Y-m')]) }}" class="before">&laquo; 前月</a>
        <span class="month">{{ $month->format('Y年m月') }}</span>
        <a href="{{ route('admin.history.show', ['child_name' => $childName, 'month' => $nextMonth->format('Y-m')]) }}" class="before">翌月 &raquo;</a>
    </div>

    <table class="custom_table">
        <thead>
            <tr>
                <th>利用日</th>
                <th>利用時間</th>
                <th>給食</th>
                <th>おやつ</th>
                <th>保育料</th>
                <th>小計</th>
            </tr>
        </thead>
        
        <tbody>
            @foreach($rows as $row)
            <tr>
                <td>{{ \Carbon\Carbon::parse($row['date'])->format('d日') }}</td>
                <td>{{ $row['time']  }}</td>
                <td>{{ number_format($row['meal'])  }}</td>
                <td>{{ number_format($row['snack'])  }}</td>
                <td>{{ number_format($row['nursery'])  }}</td>
                <td>{{ number_format($row['subtotal']) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="table-total">
                <td class="total-label">月合計</td>
                <td ></td>
                <td >{{ number_format($totalMeal)  }}</td>
                <td>{{ number_format($totalSnack) }}</td>
                <td>{{ number_format($totalNursery) }}</td>
                <td>{{ number_format($totalAll) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="text-end mt-3">
        <strong>利用日数：{{ $totalDays }} 日</strong>
    </div>

    <div class="back__button">
        <a class="back" href="{{ route('admin.history') }}">back</a>
    </div>
</div>

<script>
const input = document.getElementById('child_name');
const suggestionBox = document.getElementById('child_suggestions');

input.addEventListener('input', function() {
    const query = this.value.trim();
    if(query.length < 1){
        suggestionBox.innerHTML = '';
        return;
    }

    fetch(`/admin/child-search?term=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => {
            if(data.length === 0){
                suggestionBox.innerHTML = '<li class="list-group-item disabled">該当なし</li>';
                return;
            }

            suggestionBox.innerHTML = data.map(name => 
                `<li class="list-group-item list-group-item-action">${name}</li>`
            ).join('');

            // クリックイベントを追加して選択できるように
            document.querySelectorAll('#child_suggestions li.list-group-item-action').forEach(el => {
                el.addEventListener('click', function() {
                    input.value = this.textContent;   // 入力欄に反映
                    suggestionBox.innerHTML = '';      // 候補を消す
                });
            });
        });
});
</script>

@endsection
