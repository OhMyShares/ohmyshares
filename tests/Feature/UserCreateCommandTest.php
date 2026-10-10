<?php

namespace Tests\Feature;

use App\Console\Commands\UserCreate;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\TestCase;

class UserCreateCommandTest extends TestCase
{
    use DatabaseTransactions;

    public function testFirstUserIsAdminByDefault()
    {
        User::query()->delete();

        $this->runCreate('first@example.com');

        $this->assertSame(User::ROLE_ADMIN, (int)User::where('email', 'first@example.com')->value('role'));
    }

    public function testNextUserIsRegularUserByDefault()
    {
        User::query()->delete();
        $this->runCreate('first@example.com');

        $this->runCreate('second@example.com');

        $this->assertSame(User::ROLE_USER, (int)User::where('email', 'second@example.com')->value('role'));
    }

    private function runCreate(string $email): void
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, "Test\n$email\n\n");
        rewind($stream);

        $input = new ArrayInput([]);
        $input->setStream($stream);
        $input->setInteractive(true);

        $command = $this->app->make(UserCreate::class);
        $command->setLaravel($this->app);
        $this->assertSame(0, $command->run($input, new BufferedOutput()));
    }
}
