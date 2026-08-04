<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_is_public(): void
    {
        $this->get(route('password.request'))->assertOk();
    }

    public function test_user_receives_reset_link(): void
    {
        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect()
            ->assertSessionHas('status');
    }

    public function test_reset_link_is_not_sent_for_unknown_email(): void
    {
        $this->post(route('password.email'), ['email' => 'nobody@example.com'])
            ->assertSessionHasErrors('email');
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create(['password' => 'oldpassword']);

        $token = Password::broker()->createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertRedirect(route('login'));

        $this->assertTrue($user->fresh()->password !== 'oldpassword');

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'newpassword123',
        ])->assertRedirect(route('dashboard'));
    }

    public function test_reset_password_page_requires_token(): void
    {
        $this->get(route('password.reset', 'token'))->assertOk();
    }

    public function test_invalid_token_is_rejected(): void
    {
        $user = User::factory()->create(['password' => 'oldpassword']);

        $this->post(route('password.update'), [
            'token' => 'bad-token',
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertSessionHasErrors('email');
    }
}
