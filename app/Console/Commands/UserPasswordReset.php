<?php

namespace App\Console\Commands;

use App\Components\FileUtils;
use App\Mail\SignupMail;
use App\Models\Ticker;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class UserPasswordReset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:password-reset {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Changes user password';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();
        if ($user) {
            $new_password = User::generatePassword();
            $user->password = bcrypt($new_password);
            $saved = $user->save();
            if ($saved) {
                $this->line("\nNew password: $new_password");
                return;
            }
        }
        $this->line("\nUser not found!");
    }
}
