<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;


class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'user_id' => 'admin001',
            'name' => '管理者',
            'password' => Hash::make('12345'),
            'role' => 'admin',
        ]);

        User::create([
            'user_id' => 'teacher001',
            'name' => '先生太郎',
            'password' => Hash::make('12345'),
            'role' => 'teacher',
        ]);

        User::create([
            'user_id' => 'aaa',
            'name' => 'AAA',
            'email' => 'aaa@example.com',
            'password' => Hash::make('12345'),
            'role' => 'user',
            'address' => '東京都○○区',
        ]);

        User::create([
            'user_id' => 'bbb',
            'name' => 'BBB',
            'email' => 'bbb@example.com',
            'password' => Hash::make('12345'),
            'role' => 'user',
            'address' => '東京都○○区',
        ]);

        User::create([
            'user_id' => 'ccc',
            'name' => 'CCC',
            'email' => 'ccc@example.com',
            'password' => Hash::make('12345'),
            'role' => 'user',
            'address' => '東京都○○区',
        ]);

        User::create([
            'user_id' => 'ddd',
            'name' => 'DDD',
            'email' => 'ddd@example.com',
            'password' => Hash::make('12345'),
            'role' => 'user',
            'address' => '東京都○○区',
        ]);

    }
}
