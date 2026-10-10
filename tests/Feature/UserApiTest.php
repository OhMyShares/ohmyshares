<?php

namespace Tests\Feature;

use App\Mail\SignupMail;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use DatabaseTransactions;

    public function testNonAdminIsForbidden()
    {
        $user = $this->createUser(User::ROLE_USER);
        $other = $this->createUser(User::ROLE_USER);

        $this->actingAs($user, 'api')->getJson('/api/users')->assertStatus(403);
        $this->actingAs($user, 'api')->getJson('/api/users/'.$other->id)->assertStatus(403);
        $this->actingAs($user, 'api')->postJson('/api/users', ['name' => 'X', 'email' => 'x@example.com'])->assertStatus(403);
        $this->actingAs($user, 'api')->putJson('/api/users/'.$other->id, ['name' => 'X'])->assertStatus(403);
        $this->actingAs($user, 'api')->deleteJson('/api/users/'.$other->id)->assertStatus(403);
    }

    public function testAdminCreatesUserWithPassword()
    {
        Mail::fake();
        $admin = $this->createUser(User::ROLE_ADMIN);
        $email = 'created_'.Str::random(6).'@ohmyshares.com';

        $response = $this->actingAs($admin, 'api')->postJson('/api/users', [
            'name' => 'New User',
            'email' => $email,
            'password' => 'secret-password',
            'role' => User::ROLE_ADMIN,
            'locale' => 'ru',
            'is_test' => true,
            'is_ignore_funds_sufficiency' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('name', 'New User')
            ->assertJsonPath('email', $email)
            ->assertJsonPath('role', User::ROLE_ADMIN)
            ->assertJsonPath('locale', 'ru')
            ->assertJsonPath('is_test', true)
            ->assertJsonPath('is_ignore_funds_sufficiency', true)
            ->assertJsonMissingPath('password');

        $user = User::findOrFail($response->json('id'));
        $this->assertTrue(Hash::check('secret-password', $user->password));
        $this->assertSame(1, $user->portfolios()->count());
        Mail::assertNothingSent();
    }

    public function testAdminCreatesUserWithGeneratedPassword()
    {
        Mail::fake();
        $admin = $this->createUser(User::ROLE_ADMIN);

        $response = $this->actingAs($admin, 'api')->postJson('/api/users', [
            'name' => 'Generated',
            'email' => 'generated_'.Str::random(6).'@ohmyshares.com',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('role', User::ROLE_USER)
            ->assertJsonPath('is_test', false);

        $user = User::findOrFail($response->json('id'));
        $this->assertTrue(Hash::check(User::generatePassword(), $user->password));
        Mail::assertSent(SignupMail::class, fn (SignupMail $mail) => $mail->user->id === $user->id);
    }

    public function testCreateValidation()
    {
        $admin = $this->createUser(User::ROLE_ADMIN);

        $this->actingAs($admin, 'api')->postJson('/api/users', [
            'name' => '',
            'email' => $admin->email,
            'password' => 'short',
            'role' => 999,
            'locale' => 'xx',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password', 'role', 'locale']);
    }

    public function testAdminUpdatesUser()
    {
        $admin = $this->createUser(User::ROLE_ADMIN);
        $user = $this->createUser(User::ROLE_USER);
        $oldHash = $user->password;

        $this->actingAs($admin, 'api')->putJson('/api/users/'.$user->id, [
            'name' => 'Renamed',
            'email' => $user->email,
            'password' => '',
            'role' => User::ROLE_ADMIN,
            'is_test' => true,
        ])->assertStatus(200)
            ->assertJsonPath('name', 'Renamed')
            ->assertJsonPath('role', User::ROLE_ADMIN)
            ->assertJsonPath('is_test', true);

        $user->refresh();
        $this->assertSame($oldHash, $user->password);

        $this->actingAs($admin, 'api')->putJson('/api/users/'.$user->id, ['password' => 'new-password'])
            ->assertStatus(200);
        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));

        $this->actingAs($admin, 'api')->putJson('/api/users/'.$user->id, ['email' => $admin->email])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function testAdminCannotDemoteOrDeleteSelf()
    {
        $admin = $this->createUser(User::ROLE_ADMIN);

        $this->actingAs($admin, 'api')->putJson('/api/users/'.$admin->id, ['role' => User::ROLE_USER])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['role']);

        $this->actingAs($admin, 'api')->putJson('/api/users/'.$admin->id, ['name' => 'Self', 'role' => User::ROLE_ADMIN])
            ->assertStatus(200)
            ->assertJsonPath('name', 'Self');

        $this->actingAs($admin, 'api')->deleteJson('/api/users/'.$admin->id)->assertStatus(422);
        $this->assertNotNull(User::find($admin->id));
    }

    public function testShowAndDestroy()
    {
        $admin = $this->createUser(User::ROLE_ADMIN);
        $user = $this->createUser(User::ROLE_USER);

        $this->actingAs($admin, 'api')->getJson('/api/users/'.$user->id)
            ->assertStatus(200)
            ->assertJsonPath('id', $user->id)
            ->assertJsonPath('role', User::ROLE_USER);

        $this->actingAs($admin, 'api')->getJson('/api/users/0')->assertStatus(404);

        $this->actingAs($admin, 'api')->deleteJson('/api/users/'.$user->id)->assertStatus(200);
        $this->assertNull(User::find($user->id));
    }

    private function createUser(int $role): User
    {
        $user = User::create([
            'name' => 'User Tester',
            'email' => 'user_test_'.Str::random(6).'@ohmyshares.com',
            'password' => bcrypt('test_password'),
            'locale' => 'en',
        ]);
        $user->role = $role;
        $user->save();
        return $user;
    }
}
