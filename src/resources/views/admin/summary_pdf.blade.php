<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>保育料集計表</title>
    <style>
        
        body, h3, th, td, tfoot {
            font-family: ipag; 
            font-weight: normal;
        }

        h3 {
            text-align: center;
            color: #3b8d4c;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 auto;
            font-size: 11px;
        }

        th, td {
            border: 1px solid #999;
            padding: 6px;
            text-align: center;
        }

        thead th {
            background-color: #3b8d4c;
            color: #fff;
        }

        tbody tr:nth-child(odd) {
            background-color: #f9fcf8;
        }

        tbody tr:nth-child(even) {
            background-color: #eef7f0;
        }

        tfoot td {
            background-color: #d4e9d7;
            font-weight: normal;
            border-top: 2px solid #3b8d4c;
            text-align: center;
        }

        .footer {
            text-align: right;
            margin-top: 10px;
            font-weight: normal;
        }
    </style>
</head>
<body>
    <h3>{{ $month->format('Y年m月') }} 保育料集計表</h3>

    <table>
        <thead>
            <tr>
                <th>日付</th>
                <th>4時間未満</th>
                <th>4時間以上</th>
                <th>保育利用料</th>
                <th>給食</th>
                <th>おやつ</th>
                <th>小計</th>
            </tr>
        </thead>
        <tbody>
            @foreach($summary as $date => $s)
                @php $subtotal = $s['careFee'] + $s['mealFee'] + $s['snackFee']; @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($date)->format('d') }}</td>
                    <td>{{ $s['under4'] }}</td>
                    <td>{{ $s['over4'] }}</td>
                    <td>{{ number_format($s['careFee']) }}</td>
                    <td>{{ number_format($s['mealFee']) }}</td>
                    <td>{{ number_format($s['snackFee']) }}</td>
                    <td>{{ number_format($subtotal) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td class="total-label">月合計</td>
                <td>{{ $totalUnder4 ?? 0 }}</td>
                <td>{{ $totalOver4 ?? 0 }}</td>
                <td>{{ number_format($totalCare) }}</td>
                <td>{{ number_format($totalMeal) }}</td>
                <td>{{ number_format($totalSnack) }}</td>
                <td>{{ number_format($totalAll) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        開所日数：{{ $totalDays }} 日
    </div>
</body>
</html>
