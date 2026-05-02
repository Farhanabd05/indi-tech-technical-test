<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

uses(
    Tests\TestCase::class,
    Illuminate\Foundation\Testing\RefreshDatabase::class,
)->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Helper Functions
|--------------------------------------------------------------------------
*/

/**
 * Create a user with admin role and return the test case for chaining
 */
function actingAsAdmin(): \Tests\TestCase
{
    $user = \App\Models\User::factory()->create([
        'role_id' => \App\Models\Role::where('slug', 'administrator')->first()->id,
    ]);
    return test()->actingAs($user);
}

/**
 * Create a user with supervisor role and return the test case for chaining
 */
function actingAsSupervisor(): \Tests\TestCase
{
    $user = \App\Models\User::factory()->create([
        'role_id' => \App\Models\Role::where('slug', 'supervisor')->first()->id,
    ]);
    return test()->actingAs($user);
}

/**
 * Create a user with agent role and return the test case for chaining
 */
function actingAsAgent(): \Tests\TestCase
{
    $user = \App\Models\User::factory()->create([
        'role_id' => \App\Models\Role::where('slug', 'agent')->first()->id,
    ]);
    return test()->actingAs($user);
}

/**
 * Create a user with customer role and return the test case for chaining
 */
function actingAsCustomer(): \Tests\TestCase
{
    $user = \App\Models\User::factory()->create([
        'role_id' => \App\Models\Role::where('slug', 'customer')->first()->id,
    ]);
    return test()->actingAs($user);
}

/**
 * Create a user with a specific role slug
 */
function createUserWithRole(string $roleSlug): \App\Models\User
{
    return \App\Models\User::factory()->create([
        'role_id' => \App\Models\Role::where('slug', $roleSlug)->first()->id,
    ]);
}

/**
 * Get the first role by slug
 */
function getRoleBySlug(string $slug): ?\App\Models\Role
{
    return \App\Models\Role::where('slug', $slug)->first();
}

/**
 * Create a basic ticket with minimal required data
 */
function createTicket(array $attributes = []): \App\Models\Ticket
{
    $category = \App\Models\Category::first();
    $priority = \App\Models\Priority::first();
    $customer = createUserWithRole('customer');

    return \App\Models\Ticket::create(array_merge([
        'ticket_number' => 'TCK-' . date('Y') . '-000001',
        'title' => 'Test Ticket',
        'description' => 'This is a test ticket',
        'category_id' => $category?->id ?? 1,
        'priority_id' => $priority?->id ?? 1,
        'status' => \App\Enums\TicketStatus::OPEN,
        'created_by' => $customer->id,
        'due_at' => now()->addDays(3),
    ], $attributes));
}
