<?php

use App\Models\Company;
use App\Models\User;

test('personal language preference takes priority over company default', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'locale' => 'pt_BR',
    ]);

    $company = Company::create([
        'name' => 'Locale Test Workspace',
        'slug' => 'locale-test-workspace',
        'created_by' => $user->id,
        'default_locale' => 'es_419',
    ]);

    $user->companies()->attach($company->id, [
        'role' => 'owner',
    ]);

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('dashboard'));

    $response->assertOk();

    expect(app()->getLocale())->toBe('pt_BR');
});

test('company default language is used when personal preference is unset', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'locale' => null,
    ]);

    $company = Company::create([
        'name' => 'Locale Test Workspace',
        'slug' => 'locale-test-workspace',
        'created_by' => $user->id,
        'default_locale' => 'pt_PT',
    ]);

    $user->companies()->attach($company->id, [
        'role' => 'owner',
    ]);

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('dashboard'));

    $response->assertOk();

    expect(app()->getLocale())->toBe('pt_PT');
});

test('english fallback is used when neither preference is valid', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'locale' => 'invalid',
    ]);

    $company = Company::create([
        'name' => 'Locale Test Workspace',
        'slug' => 'locale-test-workspace',
        'created_by' => $user->id,
        'default_locale' => 'invalid',
    ]);

    $user->companies()->attach($company->id, [
        'role' => 'owner',
    ]);

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('dashboard'));

    $response->assertOk();

    expect(app()->getLocale())->toBe('en');
});

test('invalid personal language is ignored in favor of company default', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'locale' => 'invalid',
    ]);

    $company = Company::create([
        'name' => 'Locale Test Workspace',
        'slug' => 'locale-test-workspace',
        'created_by' => $user->id,
        'default_locale' => 'es_419',
    ]);

    $user->companies()->attach($company->id, [
        'role' => 'owner',
    ]);

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('dashboard'));

    $response->assertOk();

    expect(app()->getLocale())->toBe('es_419');
});
test('personal language preference is preserved during Livewire interactions', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'locale' => 'pt_BR',
    ]);

    $company = Company::create([
        'name' => 'Livewire Locale Workspace',
        'slug' => 'livewire-locale-workspace',
        'created_by' => $user->id,
        'default_locale' => 'en',
    ]);

    $user->companies()->attach($company->id, [
        'role' => 'owner',
    ]);

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('products'));

    $response->assertOk();

    expect(app()->getLocale())->toBe('pt_BR');

    $response->assertSee('Produtos');
});
