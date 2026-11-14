<?php

use App\Models\Company;
use App\Models\User;
use App\Services\CompanyLocations;
use App\Services\CreateLocation;
use App\Support\CurrentCompany;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

test('a company can create a top-level location', function () {
    $user = User::factory()->create();

    $company = Company::create([
        'name' => 'Company A',
        'slug' => 'company-a',
        'created_by' => $user->id,
    ]);

    $user->companies()->attach($company->id, [
        'role' => 'owner',
    ]);

    app(CurrentCompany::class)->set($company);

    $location = app(CreateLocation::class)->handle([
        'code' => 'WH-01',
        'name' => 'Main Warehouse',
        'type' => 'warehouse',
    ]);

    expect($location->company_id)->toBe($company->id);
    expect($location->parent_id)->toBeNull();
    expect($location->code)->toBe('WH-01');
    expect($location->name)->toBe('Main Warehouse');

    $this->assertDatabaseHas('locations', [
        'id' => $location->id,
        'company_id' => $company->id,
        'code' => 'WH-01',
    ]);
});

test('a company can create a child location under its own parent', function () {
    $user = User::factory()->create();

    $company = Company::create([
        'name' => 'Company A',
        'slug' => 'company-a',
        'created_by' => $user->id,
    ]);

    $user->companies()->attach($company->id, [
        'role' => 'owner',
    ]);

    app(CurrentCompany::class)->set($company);

    $warehouse = app(CreateLocation::class)->handle([
        'code' => 'WH-01',
        'name' => 'Main Warehouse',
        'type' => 'warehouse',
    ]);

    $bin = app(CreateLocation::class)->handle([
        'code' => 'A-01-01',
        'name' => 'Aisle A, Shelf 01, Bin 01',
        'type' => 'bin',
        'parent_id' => $warehouse->id,
    ]);

    expect($bin->company_id)->toBe($company->id);
    expect($bin->parent_id)->toBe($warehouse->id);
    expect($bin->parent->is($warehouse))->toBeTrue();

    expect($warehouse->children()->first()->is($bin))->toBeTrue();
});

test('a company cannot create a location under another company parent', function () {
    $user = User::factory()->create();

    $companyA = Company::create([
        'name' => 'Company A',
        'slug' => 'company-a',
        'created_by' => $user->id,
    ]);

    $companyB = Company::create([
        'name' => 'Company B',
        'slug' => 'company-b',
        'created_by' => $user->id,
    ]);

    $user->companies()->attach([
        $companyA->id => ['role' => 'owner'],
        $companyB->id => ['role' => 'owner'],
    ]);

    $foreignParent = $companyB->locations()->create([
        'code' => 'WH-99',
        'name' => 'Company B Warehouse',
        'type' => 'warehouse',
    ]);

    app(CurrentCompany::class)->set($companyA);

    expect(fn () => app(CreateLocation::class)->handle([
        'code' => 'BIN-001',
        'name' => 'Attempted Cross-Company Bin',
        'type' => 'bin',
        'parent_id' => $foreignParent->id,
    ]))->toThrow(ValidationException::class);

    $this->assertDatabaseMissing('locations', [
        'company_id' => $companyA->id,
        'code' => 'BIN-001',
    ]);
});

test('a location always belongs to the active company even if another company id is supplied', function () {
    $user = User::factory()->create();

    $companyA = Company::create([
        'name' => 'Company A',
        'slug' => 'company-a',
        'created_by' => $user->id,
    ]);

    $companyB = Company::create([
        'name' => 'Company B',
        'slug' => 'company-b',
        'created_by' => $user->id,
    ]);

    $user->companies()->attach([
        $companyA->id => ['role' => 'owner'],
        $companyB->id => ['role' => 'owner'],
    ]);

    app(CurrentCompany::class)->set($companyA);

    $location = app(CreateLocation::class)->handle([
        'code' => 'WH-01',
        'name' => 'Main Warehouse',
        'type' => 'warehouse',
        'company_id' => $companyB->id,
    ]);

    expect($location->company_id)->toBe($companyA->id);

    $this->assertDatabaseHas('locations', [
        'id' => $location->id,
        'company_id' => $companyA->id,
    ]);
});

test('a company can only retrieve its own locations', function () {
    $user = User::factory()->create();

    $companyA = Company::create([
        'name' => 'Company A',
        'slug' => 'company-a',
        'created_by' => $user->id,
    ]);

    $companyB = Company::create([
        'name' => 'Company B',
        'slug' => 'company-b',
        'created_by' => $user->id,
    ]);

    $user->companies()->attach([
        $companyA->id => ['role' => 'owner'],
        $companyB->id => ['role' => 'owner'],
    ]);

    $locationA = $companyA->locations()->create([
        'code' => 'WH-01',
        'name' => 'Company A Warehouse',
        'type' => 'warehouse',
    ]);

    $locationB = $companyB->locations()->create([
        'code' => 'WH-01',
        'name' => 'Company B Warehouse',
        'type' => 'warehouse',
    ]);

    $context = app(CurrentCompany::class);
    $context->set($companyA);

    $locations = app(CompanyLocations::class);

    expect($locations->query()->pluck('id')->all())
        ->toBe([$locationA->id]);

    expect($locations->findOrFail($locationA->id)->name)
        ->toBe('Company A Warehouse');

    expect(fn () => $locations->findOrFail($locationB->id))
        ->toThrow(ModelNotFoundException::class);

    $context->set($companyB);

    expect($locations->query()->pluck('id')->all())
        ->toBe([$locationB->id]);
});
