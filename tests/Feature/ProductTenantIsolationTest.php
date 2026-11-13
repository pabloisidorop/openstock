<?php

use App\Models\Company;
use App\Models\User;
use App\Services\CompanyProducts;
use App\Support\CurrentCompany;
use Illuminate\Database\Eloquent\ModelNotFoundException;

test('a company can only retrieve its own products', function () {
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

    $productA = $companyA->products()->create([
        'sku' => 'SKU-001',
        'name' => 'Product A',
    ]);

    $productB = $companyB->products()->create([
        'sku' => 'SKU-001',
        'name' => 'Product B',
    ]);

    $context = app(CurrentCompany::class);
    $context->set($companyA);

    $products = app(CompanyProducts::class);

    expect($products->query()->pluck('id')->all())
        ->toBe([$productA->id]);

    expect($products->findOrFail($productA->id)->name)
        ->toBe('Product A');

    expect(fn () => $products->findOrFail($productB->id))
        ->toThrow(ModelNotFoundException::class);

    $context->set($companyB);

    expect($products->query()->pluck('id')->all())
        ->toBe([$productB->id]);
});

test('a company member cannot access another active company product through the policy', function () {
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

    $productA = $companyA->products()->create([
        'sku' => 'SKU-001',
        'name' => 'Product A',
    ]);

    $context = app(CurrentCompany::class);
    $context->set($companyB);

    expect($user->can('view', $productA))->toBeFalse();
    expect($user->can('update', $productA))->toBeFalse();
    expect($user->can('delete', $productA))->toBeFalse();
});

test('the products page only displays products from the active workspace', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

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

    $companyA->products()->create([
        'sku' => 'SKU-001',
        'name' => 'Product A',
    ]);

    $companyB->products()->create([
        'sku' => 'SKU-001',
        'name' => 'Product B',
    ]);

    $this->actingAs($user)
        ->withSession(['active_company_id' => $companyA->id])
        ->get(route('products'))
        ->assertOk()
        ->assertSee('Product A')
        ->assertDontSee('Product B');
});
