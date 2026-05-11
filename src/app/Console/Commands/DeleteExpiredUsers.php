<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;


class DeleteExpiredUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-expired-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::whereNotNull('delete_at_target')
        ->where('delete_at_target', '<=', now())
        ->get();

        $this->info('対象ユーザー数: ' . $users->count());

        foreach ($users as $user) {
            $this->info('削除ユーザーID: ' . $user->id);
            $user->children()->delete();
            $user->delete();
        }

        $this->info('完了');
        //
    }
}
