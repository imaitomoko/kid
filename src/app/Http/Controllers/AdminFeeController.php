<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FeeItem;

class AdminFeeController extends Controller
{
    public function index()
    {
        $feeItems = FeeItem::orderBy('start_date', 'desc')->get();

        return view('admin.fee', compact('feeItems'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name'        => 'required|string|max:255',
            'category'    => 'required|string',
            'unit' => 'required|string|in:1時間単位,30分単位,1回単位',
            'amount'       => 'required|integer|min:0',
            'start_date'  => 'required|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
        ]);

        if (empty($validated['end_date'])) {
            $validated['end_date'] = null;
        }

        FeeItem::create($validated);

        return redirect()->route('admin.fee.index')->with('success', '料金を登録しました。');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'item_name'   => 'required|string|max:255',
            'category'    => 'required|string',
            'unit' => 'required|string|in:1時間単位,30分単位,1回単位',
            'amount'      => 'required|integer|min:0',
            'start_date'  => 'required|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
        ]);

        if (empty($validated['end_date'])) {
            $validated['end_date'] = null;
        }

        $feeItem = FeeItem::findOrFail($id);
        $feeItem->update($validated);

        return redirect()->route('admin.fee.index')->with('success', '料金情報を更新しました。');
    }


    public function destroy($id)
    {
        $feeItem = FeeItem::findOrFail($id);
        $feeItem->delete();

        return redirect()->route('admin.fee.index')->with('success', '料金を削除しました。');
    }
    //
}
