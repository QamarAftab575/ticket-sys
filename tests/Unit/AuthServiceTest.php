<?php

namespace Tests\Unit;

use App\Events\UserRegistered;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();

        // Create roles needed for tests
        Role::firstOrCreate(['name' => 'super-admin']);
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'manager']);
        Role::firstOrCreate(['name' => 'agent']);
        Role::firstOrCreate(['name' => 'employee']);
    }

    /**
     * Test successful login with valid credentials.
     */
    public function test_login_succeeds_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => 'SecurePass123',
            'email_verified_at' => now(),
            'is_suspended' => false,
        ]);

        $result = $this->authService->login('login@example.com', 'SecurePass123');

        $this->assertTrue($result);
        $this->assertAuthenticated();
        $this->assertEquals($user->id, auth()->user()->id);
    }

    /**
     * Test login fails with invalid email.
     */
    public function test_login_fails_with_invalid_email(): void
    {
        $result = $this->authService->login('nonexistent@example.com', 'SecurePass123');

        $this->assertFalse($result);
        $this->assertGuest();
    }

    /**
     * Test login fails with incorrect password.
     */
    public function test_login_fails_with_incorrect_password(): void
    {
        User::factory()->create([
            'email' => 'login@example.com',
            'password' => 'SecurePass123',
            'email_verified_at' => now(),
            'is_suspended' => false,
        ]);

        $result = $this->authService->login('login@example.com', 'WrongPassword123');

        $this->assertFalse($result);
        $this->assertGuest();
    }

    /**
     * Test login fails when user is suspended.
     */
    public function test_login_fails_when_user_is_suspended(): void
    {
        User::factory()->create([
            'email' => 'suspended@example.com',
            'password' => 'SecurePass123',
            'email_verified_at' => now(),
            'is_suspended' => true,
        ]);

        $result = $this->authService->login('suspended@example.com', 'SecurePass123');

        $this->assertFalse($result);
        $this->assertGuest();
    }

    /**
     * Test login fails when email is not verified.
     */
    public function test_login_fails_when_email_not_verified(): void
    {
        User::factory()->create([
            'email' => 'unverified@example.com',
            'password' => 'SecurePass123',
            'email_verified_at' => null,
            'is_suspended' => false,
        ]);

        $result = $this->authService->login('unverified@example.com', 'SecurePass123');

        $this->assertFalse($result);
        $this->assertGuest();
    }

    /**
     * Test login updates last_login_at timestamp.
     */
    public function test_login_updates_last_login_at(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => 'SecurePass123',
            'email_verified_at' => now(),
            'is_suspended' => false,
            'last_login_at' => null,
        ]);

        $this->authService->login('login@example.com', 'SecurePass123');

        $user->refresh();
        $this->assertNotNull($user->last_login_at);
    }

    /**
     * Test login emits UserRegistered event.
     */
    public function test_login_emits_user_registered_event(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => 'SecurePass123',
            'email_verified_at' => now(),
            'is_suspended' => false,
        ]);

        $eventDispatched = false;
        $eventUser = null;

        // Listen for the event
        \Illuminate\Support\Facades\Event::listen(UserRegistered::class, function ($event) use (&$eventDispatched, &$eventUser) {
            $eventDispatched = true;
            $eventUser = $event->user;
        });

        $this->authService->login('login@example.com', 'SecurePass123');

        $this->assertTrue($eventDispatched);
        $this->assertEquals($user->id, $eventUser->id);
    }

    /**
     * Test login resets failed login attempts counter.
     */
    public function test_login_resets_failed_attempts_counter(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => 'SecurePass123',
            'email_verified_at' => now(),
            'is_suspended' => false,
        ]);

        // Set failed attempts in cache
        \Illuminate\Support\Facades\Cache::put('login_attempts:login@example.com', 3, 60);
        \Illuminate\Support\Facades\Cache::put('login_lockout:login@example.com', true, 900);

        $this->authService->login('login@example.com', 'SecurePass123');

        // Verify cache keys were cleared
        $this->assertFalse(\Illuminate\Support\Facades\Cache::has('login_attempts:login@example.com'));
        $this->assertFalse(\Illuminate\Support\Facades\Cache::has('login_lockout:login@example.com'));
    }

    /**
     * Test checkRateLimit returns true when account is not locked.
     */
    public function test_check_rate_limit_returns_true_when_not_locked(): void
    {
        $result = $this->authService->checkRateLimit('test@example.com');

        $this->assertTrue($result);
    }

    /**
     * Test checkRateLimit returns false when account is locked.
     */
    public function test_check_rate_limit_returns_false_when_locked(): void
    {
        // Set lockout in cache
        \Illuminate\Support\Facades\Cache::put('login_lockout:test@example.com', true, 900);

        $result = $this->authService->checkRateLimit('test@example.com');

        $this->assertFalse($result);
    }

    /**
     * Test incrementFailedAttempts increments counter.
     */
    public function test_increment_failed_attempts_increments_counter(): void
    {
        $email = 'test@example.com';

        $this->authService->incrementFailedAttempts($email);

        $this->assertEquals(1, \Illuminate\Support\Facades\Cache::get("login_attempts:{$email}"));
    }

    /**
     * Test incrementFailedAttempts increments counter multiple times.
     */
    public function test_increment_failed_attempts_increments_multiple_times(): void
    {
        $email = 'test@example.com';

        $this->authService->incrementFailedAttempts($email);
        $this->authService->incrementFailedAttempts($email);
        $this->authService->incrementFailedAttempts($email);

        $this->assertEquals(3, \Illuminate\Support\Facades\Cache::get("login_attempts:{$email}"));
    }

    /**
     * Test incrementFailedAttempts locks account after 5 attempts.
     */
    public function test_increment_failed_attempts_locks_account_after_5_attempts(): void
    {
        $email = 'test@example.com';

        for ($i = 0; $i < 5; $i++) {
            $this->authService->incrementFailedAttempts($email);
        }

        $this->assertTrue(\Illuminate\Support\Facades\Cache::has("login_lockout:{$email}"));
    }

    /**
     * Test incrementFailedAttempts does not lock account before 5 attempts.
     */
    public function test_increment_failed_attempts_does_not_lock_before_5_attempts(): void
    {
        $email = 'test@example.com';

        for ($i = 0; $i < 4; $i++) {
            $this->authService->incrementFailedAttempts($email);
        }

        $this->assertFalse(\Illuminate\Support\Facades\Cache::has("login_lockout:{$email}"));
    }

    /**
     * Test resetFailedAttempts clears both cache keys.
     */
    public function test_reset_failed_attempts_clears_cache_keys(): void
    {
        $email = 'test@example.com';

        // Set cache keys
        \Illuminate\Support\Facades\Cache::put("login_attempts:{$email}", 5, 60);
        \Illuminate\Support\Facades\Cache::put("login_lockout:{$email}", true, 900);

        $this->authService->resetFailedAttempts($email);

        $this->assertFalse(\Illuminate\Support\Facades\Cache::has("login_attempts:{$email}"));
        $this->assertFalse(\Illuminate\Support\Facades\Cache::has("login_lockout:{$email}"));
    }

    /**
     * Test resetFailedAttempts works when cache keys don't exist.
     */
    public function test_reset_failed_attempts_works_when_cache_empty(): void
    {
        $email = 'test@example.com';

        // Should not throw exception
        $this->authService->resetFailedAttempts($email);

        $this->assertFalse(\Illuminate\Support\Facades\Cache::has("login_attempts:{$email}"));
        $this->assertFalse(\Illuminate\Support\Facades\Cache::has("login_lockout:{$email}"));
    }

    /**
     * Test requestPasswordReset sends email for valid user.
     */
    public function test_request_password_reset_sends_email_for_valid_user(): void
    {
        $user = User::factory()->create([
            'email' => 'reset@example.com',
        ]);

        $eventDispatched = false;
        $eventUser = null;

        // Listen for the event
        \Illuminate\Support\Facades\Event::listen(\App\Events\PasswordResetRequested::class, function ($event) use (&$eventDispatched, &$eventUser) {
            $eventDispatched = true;
            $eventUser = $event->user;
        });

        $this->authService->requestPasswordReset('reset@example.com');

        $this->assertTrue($eventDispatched);
        $this->assertEquals($user->id, $eventUser->id);
    }

    /**
     * Test requestPasswordReset creates token in database.
     */
    public function test_request_password_reset_creates_token_in_database(): void
    {
        $user = User::factory()->create([
            'email' => 'reset@example.com',
        ]);

        $this->authService->requestPasswordReset('reset@example.com');

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'reset@example.com',
        ]);
    }

    /**
     * Test requestPasswordReset silently returns for non-existent user.
     */
    public function test_request_password_reset_silently_returns_for_nonexistent_user(): void
    {
        $eventDispatched = false;

        // Listen for the event
        \Illuminate\Support\Facades\Event::listen(\App\Events\PasswordResetRequested::class, function ($event) use (&$eventDispatched) {
            $eventDispatched = true;
        });

        $this->authService->requestPasswordReset('nonexistent@example.com');

        $this->assertFalse($eventDispatched);
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => 'nonexistent@example.com',
        ]);
    }

    /**
     * Test resetPassword updates user password.
     */
    public function test_reset_password_updates_user_password(): void
    {
        $user = User::factory()->create([
            'email' => 'reset@example.com',
            'password' => 'OldPassword123',
        ]);

        // Request password reset to get token
        $this->authService->requestPasswordReset('reset@example.com');

        // Get the token from database
        $resetRecord = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('email', 'reset@example.com')
            ->first();

        // Extract the plain token (we need to find it from the event)
        $token = null;
        \Illuminate\Support\Facades\Event::listen(\App\Events\PasswordResetRequested::class, function ($event) use (&$token) {
            $token = $event->token;
        });

        // Request again to capture token
        $this->authService->requestPasswordReset('reset@example.com');

        // Reset password
        $result = $this->authService->resetPassword($token, 'NewPassword123');

        $this->assertTrue($result);
        $user->refresh();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('NewPassword123', $user->password));
    }

    /**
     * Test resetPassword returns false for invalid token.
     */
    public function test_reset_password_returns_false_for_invalid_token(): void
    {
        User::factory()->create([
            'email' => 'reset@example.com',
        ]);

        $result = $this->authService->resetPassword('invalid_token', 'NewPassword123');

        $this->assertFalse($result);
    }

    /**
     * Test resetPassword returns false for expired token.
     */
    public function test_reset_password_returns_false_for_expired_token(): void
    {
        $user = User::factory()->create([
            'email' => 'reset@example.com',
        ]);

        // Create an expired token
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->insert([
            'email' => 'reset@example.com',
            'token' => \Illuminate\Support\Facades\Hash::make('test_token'),
            'created_at' => now()->subHours(2),
        ]);

        $result = $this->authService->resetPassword('test_token', 'NewPassword123');

        $this->assertFalse($result);
    }

    /**
     * Test resetPassword deletes token after successful reset.
     */
    public function test_reset_password_deletes_token_after_reset(): void
    {
        $user = User::factory()->create([
            'email' => 'reset@example.com',
        ]);

        // Request password reset to get token
        $token = null;
        \Illuminate\Support\Facades\Event::listen(\App\Events\PasswordResetRequested::class, function ($event) use (&$token) {
            $token = $event->token;
        });

        $this->authService->requestPasswordReset('reset@example.com');

        // Reset password
        $this->authService->resetPassword($token, 'NewPassword123');

        // Verify token is deleted
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => 'reset@example.com',
        ]);
    }

    /**
     * Test resetPassword validates password strength.
     */
    public function test_reset_password_validates_password_strength(): void
    {
        $user = User::factory()->create([
            'email' => 'reset@example.com',
        ]);

        // Request password reset to get token
        $token = null;
        \Illuminate\Support\Facades\Event::listen(\App\Events\PasswordResetRequested::class, function ($event) use (&$token) {
            $token = $event->token;
        });

        $this->authService->requestPasswordReset('reset@example.com');

        // Try to reset with weak password
        $this->expectException(ValidationException::class);
        $this->authService->resetPassword($token, 'weak');
    }

    /**
     * Test logout destroys session for authenticated user.
     */
    public function test_logout_destroys_session(): void
    {
        $user = User::factory()->create([
            'email' => 'logout@example.com',
            'password' => 'SecurePass123',
            'email_verified_at' => now(),
            'is_suspended' => false,
        ]);

        // Login first
        $this->authService->login('logout@example.com', 'SecurePass123');
        $this->assertAuthenticated();

        // Logout
        $this->authService->logout();

        // Verify user is no longer authenticated
        $this->assertGuest();
    }

    /**
     * Test logout invalidates session.
     */
    public function test_logout_invalidates_session(): void
    {
        $user = User::factory()->create([
            'email' => 'logout@example.com',
            'password' => 'SecurePass123',
            'email_verified_at' => now(),
            'is_suspended' => false,
        ]);

        // Login first
        $this->authService->login('logout@example.com', 'SecurePass123');
        $oldSessionId = session()->getId();

        // Logout
        $this->authService->logout();

        // Verify session is invalidated (new session ID should be generated)
        $this->assertNotEquals($oldSessionId, session()->getId());
    }

    /**
     * Test first registered user receives both super-admin and admin roles.
     */
    public function test_first_user_receives_both_super_admin_and_admin_roles(): void
    {
        // Ensure no users exist
        User::query()->delete();

        $userData = [
            'name' => 'First User',
            'email' => 'first@example.com',
            'password' => 'SecurePass123',
        ];

        $user = $this->authService->register($userData);

        $this->assertTrue($user->hasRole('super-admin'));
        $this->assertTrue($user->hasRole('admin'));
    }

    /**
     * Test subsequent users receive only admin role.
     */
    public function test_subsequent_users_receive_only_admin_role(): void
    {
        // Create first user
        User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $userData = [
            'name' => 'Second User',
            'email' => 'second@example.com',
            'password' => 'SecurePass123',
        ];

        $user = $this->authService->register($userData);

        $this->assertFalse($user->hasRole('super-admin'));
        $this->assertTrue($user->hasRole('admin'));
    }

    /**
     * Test register does not include hardcoded UUID.
     */
    public function test_register_does_not_include_hardcoded_uuid(): void
    {
        // Ensure no users exist
        User::query()->delete();

        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'SecurePass123',
        ];

        $user = $this->authService->register($userData);

        // Verify UUID is generated (not null and is valid UUID format)
        $this->assertNotNull($user->id);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $user->id);
    }
}
