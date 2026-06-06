<?php

namespace App\Services;

use App\Events\PasswordResetRequested;
use App\Events\UserRegistered;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Register a new admin user.
     *
     * @param array $data
     * @return User
     * @throws ValidationException
     */
    public function register(array $data): User
    {
        // Validate input data
        $this->validateRegistrationData($data);

        return DB::transaction(function () use ($data) {
            // Create user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'email_verified_at' => now(),
            ]);

            // Emit UserRegistered event
            event(new UserRegistered($user, now()));

            return $user;
        });
    }

    /**
     * Authenticate a user with email and password.
     *
     * @param string $email
     * @param string $password
     * @param bool $remember
     * @return bool
     */
    public function login(string $email, string $password, bool $remember = true): bool
    {
        // Find user by email
        $user = User::where('email', $email)->first();

        // If user not found or password doesn't match, return false
        if (!$user || !Hash::check($password, $user->password)) {
            return false;
        }

        // Check if user is suspended
        if ($user->isSuspended()) {
            return false;
        }

        // Check if email is verified
        if (!$user->isEmailVerified()) {
            return false;
        }

        // Update last_login_at timestamp
        $user->update(['last_login_at' => now()]);

        // Reset failed login attempts counter
        Cache::forget("login_attempts:{$email}");
        Cache::forget("login_lockout:{$email}");

        // Create session using Auth::login() with remember me option
        Auth::login($user, $remember);

        return true;
    }

    /**
     * Check if an account is rate limited.
     *
     * @param string $email
     * @return bool
     */
    public function checkRateLimit(string $email): bool
    {
        // Check if account is locked
        if (Cache::has("login_lockout:{$email}")) {
            return false;
        }

        return true;
    }

    /**
     * Increment failed login attempts for an email.
     *
     * @param string $email
     * @return void
     */
    public function incrementFailedAttempts(string $email): void
    {
        $key = "login_attempts:{$email}";
        $attempts = Cache::get($key, 0);
        $attempts++;

        // Set cache with 60 second TTL
        Cache::put($key, $attempts, 60);

        // If 5 attempts reached, lock the account for 15 minutes
        if ($attempts >= 5) {
            Cache::put("login_lockout:{$email}", true, 900);
        }
    }

    /**
     * Reset failed login attempts for an email.
     *
     * @param string $email
     * @return void
     */
    public function resetFailedAttempts(string $email): void
    {
        Cache::forget("login_attempts:{$email}");
        Cache::forget("login_lockout:{$email}");
    }

    /**
     * Request a password reset for the given email.
     *
     * @param string $email
     * @return void
     */
    public function requestPasswordReset(string $email): void
    {
        // Find user by email
        $user = User::where('email', $email)->first();

        // If user not found, silently return (security: don't reveal if email exists)
        if (!$user) {
            return;
        }

        // Generate secure reset token
        $token = Str::random(64);

        // Store token in password_reset_tokens table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // Emit PasswordResetRequested event
        event(new PasswordResetRequested($user, $token));
    }

    /**
     * Reset the password for the given token and password.
     *
     * @param string $token
     * @param string $password
     * @return bool
     */
    public function resetPassword(string $token, string $password): bool
    {
        // Validate password strength
        $this->validatePasswordStrength($password);

        // Find the password reset token record
        $resetRecord = DB::table('password_reset_tokens')
            ->where('created_at', '>', now()->subHour())
            ->first();

        if (!$resetRecord) {
            return false;
        }

        // Verify the token matches
        if (!Hash::check($token, $resetRecord->token)) {
            return false;
        }

        // Find user by email
        $user = User::where('email', $resetRecord->email)->first();

        if (!$user) {
            return false;
        }

        // Update user password
        $user->update(['password' => $password]);

        // Delete the token record to prevent reuse
        DB::table('password_reset_tokens')
            ->where('email', $resetRecord->email)
            ->delete();

        return true;
    }
    /**
     * Log out the currently authenticated user.
     *
     * @return void
     */
    public function logout(): void
    {
        // Destroy session
        Auth::logout();

        // Invalidate the session
        session()->invalidate();
    }


    /**
     * Validate registration data.
     *
     * @param array $data
     * @return void
     * @throws ValidationException
     */
    private function validateRegistrationData(array $data): void
    {
        $errors = [];

        // Check required fields
        if (empty($data['name'])) {
            $errors['name'][] = 'Name is required.';
        }

        if (empty($data['email'])) {
            $errors['email'][] = 'Email is required.';
        }

        if (empty($data['password'])) {
            $errors['password'][] = 'Password is required.';
        }

        // Validate email format
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'][] = 'Email must be a valid email address.';
        }

        // Check if email already exists
        if (!empty($data['email']) && User::where('email', $data['email'])->exists()) {
            $errors['email'][] = 'Email is already registered.';
        }

        // Validate password strength (min 8 chars, mixed case, numbers)
        if (!empty($data['password'])) {
            if (strlen($data['password']) < 8) {
                $errors['password'][] = 'Password must be at least 8 characters.';
            }

            if (!preg_match('/[a-z]/', $data['password'])) {
                $errors['password'][] = 'Password must contain lowercase letters.';
            }

            if (!preg_match('/[A-Z]/', $data['password'])) {
                $errors['password'][] = 'Password must contain uppercase letters.';
            }

            if (!preg_match('/\d/', $data['password'])) {
                $errors['password'][] = 'Password must contain numbers.';
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Validate password strength.
     *
     * @param string $password
     * @return void
     * @throws ValidationException
     */
    private function validatePasswordStrength(string $password): void
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors['password'][] = 'Password must be at least 8 characters.';
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors['password'][] = 'Password must contain lowercase letters.';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors['password'][] = 'Password must contain uppercase letters.';
        }

        if (!preg_match('/\d/', $password)) {
            $errors['password'][] = 'Password must contain numbers.';
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }
}