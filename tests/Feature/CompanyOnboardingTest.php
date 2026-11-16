<?php

use App\Models\Company;
use App\Models\Location;
use App\Models\LocationType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function verifiedUser(): User
{
    return User::factory()->create([
        'email_verified_at' => now(),
    ]);
}

it('allows a verified user to visit the company creation page', function () {
    $user = verifiedUser();

    $response = $this->actingAs($user)
        ->get(route('companies.create'));

    $response->assertOk()
        ->assertSee('Warehouse setup')
        ->assertSee('Basic')
        ->assertSee('Standard');
});

it('selects the standard template by default', function () {
    $user = verifiedUser();

    Livewire::actingAs($user);

    Livewire::test('create-company')
        ->assertSet('template', 'standard');
});

it('creates a company with the basic template through onboarding', function () {
    $user = verifiedUser();

    Livewire::actingAs($user);

    Livewire::test('create-company')
        ->set('name', 'Basic Onboarding Company')
        ->set('template', 'basic')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $company = Company::where('name', 'Basic Onboarding Company')
        ->firstOrFail();

    expect($company->locations()->count())->toBe(4)
        ->and($company->locationTypes()->count())->toBe(3)
        ->and($company->users()->whereKey($user->id)->firstOrFail()->pivot->role)
        ->toBe('owner')
        ->and(session('active_company_id'))->toBe($company->id);
});

it('creates a company with the standard template through onboarding', function () {
    $user = verifiedUser();

    Livewire::actingAs($user);

    Livewire::test('create-company')
        ->set('name', 'Standard Onboarding Company')
        ->set('template', 'standard')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $company = Company::where('name', 'Standard Onboarding Company')
        ->firstOrFail();

    expect($company->locations()->count())->toBe(10)
        ->and($company->locationTypes()->count())->toBe(3)
        ->and(session('active_company_id'))->toBe($company->id);
});

it('rejects an invalid template selection', function () {
    $user = verifiedUser();

    Livewire::actingAs($user);

    Livewire::test('create-company')
        ->set('name', 'Invalid Template Company')
        ->set('template', 'nonexistent')
        ->call('save')
        ->assertHasErrors(['template']);

    expect(Company::where('name', 'Invalid Template Company')->exists())->toBeFalse()
        ->and(Location::count())->toBe(0)
        ->and(LocationType::count())->toBe(0);
});
