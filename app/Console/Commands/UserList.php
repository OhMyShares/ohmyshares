<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UserList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:list
                            {--page=1 : Page number}
                            {--per-page=20 : Users per page}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lists users with pagination';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $page = max(1, (int) $this->option('page'));
        $perPage = max(1, (int) $this->option('per-page'));

        $users = User::orderBy('id')->paginate($perPage, ['*'], 'page', $page);

        if ($users->isEmpty()) {
            $this->line('No users found.');
            return;
        }

        $roles = User::roleLabels();
        $rows = $users->map(fn(User $user): array => [
            $user->id,
            $user->name,
            $user->email,
            $roles[$user->role] ?? $user->role,
            $user->created_at,
            $user->last_active_at,
        ])->all();

        $this->table(['ID', 'Name', 'Email', 'Role', 'Created', 'Last active'], $rows);
        $this->line("Page {$users->currentPage()} of {$users->lastPage()}, total users: {$users->total()}");
    }
}
