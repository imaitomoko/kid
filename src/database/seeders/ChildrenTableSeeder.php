<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Child;

class ChildrenTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Child::create([
            'user_id' => 3,
            'child_name' => 'AAA-child',
            'birthday' => '2013-04-30',
            'allergy' => '卵',
            'gender' => '男',
        ]);

        Child::create([
            'user_id' => 4,
            'child_name' => 'BBB-child',
            'birthday' => '2013-06-30',
            'allergy' => 'なし',
            'gender' => '女',
        ]);
        //
    }
}
