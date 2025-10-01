<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FeeItem;

class FeeItemTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FeeItem::create([
            'item_name' => '未満児保育料',
            'category' => '未満児保育',
            'unit' => '時間',
            'amount' => 500,
            'start_date' => '2025-09-30',
        ]);

        FeeItem::create([
            'item_name' => '以上児保育料',
            'category' => '以上児保育',
            'unit' => '時間',
            'amount' => 300,
            'start_date' => '2025-09-30',
        ]);

        FeeItem::create([
            'item_name' => '給食代',
            'category' => '給食',
            'unit' => '1回',
            'amount' => 300,
            'start_date' => '2025-09-30',
        ]);
        //
        FeeItem::create([
            'item_name' => 'おやつ代',
            'category' => 'おやつ',
            'unit' => '1回',
            'amount' => 100,
            'start_date' => '2025-09-30',
        ]);
    }
}
