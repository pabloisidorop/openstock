<?php

use App\Models\Company;
use App\Models\Location;
use App\Models\LocationType;
use App\Models\User;
use App\Services\ProvisionCompanyTemplate;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function createTemplateTestCompany(): Company
{
    $user = User::factory()->create();

    return Company::create([
        'name' => 'Template Test Company',
        'slug' => 'template-test-'.uniqid(),
        'created_by' => $user->id,
    ]);
}

it('provisions the basic template location types and hierarchy', function () {
    $company = createTemplateTestCompany();

    app(ProvisionCompanyTemplate::class)->handle($company, 'basic');

    expect($company->locationTypes()->count())->toBe(3)
        ->and($company->locations()->count())->toBe(4);

    $warehouse = $company->locations()->where('code', 'warehouse')->firstOrFail();

    expect($warehouse->locationType->code)->toBe('warehouse')
        ->and($warehouse->children()->count())->toBe(3)
        ->and($company->locations()->where('code', 'receiving')->firstOrFail()->parent_id)
        ->toBe($warehouse->id);
});

it('provisions the standard template with nested locations', function () {
    $company = createTemplateTestCompany();

    app(ProvisionCompanyTemplate::class)->handle($company, 'standard');

    expect($company->locationTypes()->count())->toBe(3)
        ->and($company->locations()->count())->toBe(10);

    $shelf = $company->locations()->where('code', 'shelf-a')->firstOrFail();
    $rack = $company->locations()->where('code', 'rack-a')->firstOrFail();
    $zone = $company->locations()->where('code', 'zone-a')->firstOrFail();

    expect($shelf->parent_id)->toBe($rack->id)
        ->and($rack->parent_id)->toBe($zone->id)
        ->and($zone->parent_id)->toBe(
            $company->locations()->where('code', 'storage')->firstOrFail()->id
        );
});

it('keeps all provisioned locations and types within their company', function () {
    $company = createTemplateTestCompany();
    $anotherCompany = createTemplateTestCompany();

    app(ProvisionCompanyTemplate::class)->handle($company, 'basic');

    expect($company->locations()->count())->toBe(4)
        ->and($anotherCompany->locations()->count())->toBe(0)
        ->and($company->locationTypes()->count())->toBe(3)
        ->and($anotherCompany->locationTypes()->count())->toBe(0);

    foreach ($company->locations as $location) {
        expect($location->company_id)->toBe($company->id)
            ->and($location->locationType->company_id)->toBe($company->id);
    }
});

it('rejects an unknown template', function () {
    $company = createTemplateTestCompany();

    app(ProvisionCompanyTemplate::class)->handle($company, 'unknown');
})->throws(ValidationException::class);

it('rolls back all provisioning when location creation fails', function () {
    $company = createTemplateTestCompany();

    config()->set('openstock.templates.rollback-test', [
        'name' => 'Rollback Test',
        'description' => 'Template used to test transaction rollback.',
        'locations' => [
            [
                'code' => 'duplicate',
                'name' => 'First Location',
                'type' => 'warehouse',
                'children' => [
                    [
                        'code' => 'duplicate',
                        'name' => 'Second Location',
                        'type' => 'storage_area',
                    ],
                ],
            ],
        ],
    ]);

    expect(fn () => app(ProvisionCompanyTemplate::class)
        ->handle($company, 'rollback-test'))
        ->toThrow(QueryException::class);

    expect(LocationType::where('company_id', $company->id)->count())->toBe(0)
        ->and(Location::where('company_id', $company->id)->count())->toBe(0);
});
