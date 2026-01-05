<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "Tests\TestCase". Of course, you may
| need to change it using the `uses()` function to bind a different classes or traits.
|
*/

// Feature tests use Laravel TestCase with RefreshDatabase
uses(TestCase::class, RefreshDatabase::class)->in('Feature');

// Unit tests use the base TestCase
uses(TestCase::class)->in('Unit');

// Unit tests that interact with database need RefreshDatabase
uses(RefreshDatabase::class)->in('Unit/Http/Resources');
uses(RefreshDatabase::class)->in('Unit/Repositories');
uses(RefreshDatabase::class)->in('Unit/Http/Requests');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
| Example:
| expect()->extend('toBeOne', function () {
|     return $this->toBe(1);
| });
|
*/

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the amount of code you need to write.
|
*/

/**
 * Create an authenticated user for testing
 *
 * @param array<string, mixed> $attributes
 */
function actingAsUser(array $attributes = []): \App\Models\User
{
    return \App\Models\User::create(\array_merge([
        'first_name' => 'Test',
        'last_name'  => 'User',
        'email'      => 'test@example.com',
        'password'   => \Illuminate\Support\Facades\Hash::make('password'),
    ], $attributes));
}

/**
 * Get a valid registration payload
 *
 * @param array<string, mixed> $overrides
 * @return array<string, mixed>
 */
function validRegistrationData(array $overrides = []): array
{
    return \array_merge([
        'first_name'            => 'John',
        'last_name'             => 'Doe',
        'email'                 => 'john@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ], $overrides);
}

/**
 * Get a valid login payload
 *
 * @param array<string, mixed> $overrides
 * @return array<string, mixed>
 */
function validLoginData(array $overrides = []): array
{
    return \array_merge([
        'email'    => 'test@example.com',
        'password' => 'password',
    ], $overrides);
}
