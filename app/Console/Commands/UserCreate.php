<?php

namespace App\Console\Commands;

use App\Components\FileUtils;
use App\Mail\SignupMail;
use App\Models\Ticker;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class UserCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates new user';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $name = $this->ask('Name?');
        $email = $this->ask('Email?');
        $roles = [];
        foreach (User::roleLabels() as $key => $role) {
            $roles[] = "$key - $role";
        }
        $defaultRole = User::query()->exists() ? User::ROLE_USER : User::ROLE_ADMIN;
        $role = $this->ask(Arr::join($roles, ', ').' Role?', $defaultRole);

        $user = User::where('email', $email)->first();
        if (!$user) {
            $user = new User;
            $user->name = $name;
            $user->email = $email;
            $user->role = $role;
            $new_password = User::generatePassword();
            $user->password = bcrypt($new_password);
            $saved = $user->save();
            if ($saved) {
                $this->line("Created!\nNew password: $new_password");
                return;
            }
        }
        $this->line("\nUser already exists!");
    }
}
