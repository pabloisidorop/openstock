<?php

use App\Models\Company;
use App\Models\Location;
use App\Models\LocationType;
use App\Models\User;
use App\Services\CreateCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('creates a company with the basic template when selected', function () {
    $user = User::factory()->create();

    $company = app(CreateCompany::class)->handle(
        $user,
        'Basic Test Company',
        'basic'
    );

    expect($company->name)->toBe('Basic Test Company')
        ->and($company->locations()->count())->toBe(4)
        ->and($company->locationTypes()->count())->toBe(3)
        ->and($company->users()->whereKey($user->id)->firstOrFail()->pivot->role)
        ->toBe('owner');
});

it('uses the standard template by default', function () {
    $user = User::factory()->create();

    $company = app(CreateCompany::class)->handle(
        $user,
        'Standard Test Company'
    );

    expect($company->locations()->count())->toBe(10)
        ->and($company->locationTypes()->count())->toBe(3);
});

it('rolls back company creation if template provisioning fails', function () {
    $user = User::factory()->create();

    expect(fn () => app(CreateCompany::class)->handle(
        $user,
        'Failed Test Company',
        'unknown'
    ))->toThrow(ValidationException::class);

    expect(Company::where('name', 'Failed Test Company')->count())->toBe(0)
        ->and(Location::count())->toBe(0)
        ->and(LocationType::count())->toBe(0)
        ->and($user->companies()->count())->toBe(0);
});
