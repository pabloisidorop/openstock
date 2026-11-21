<?php

use App\Models\Company;
use App\Models\User;
use App\Support\CurrentCompany;
use Livewire\Livewire;

test('products page renders in all supported languages', function () {
    $translations = [
        'en' => [
            'Products',
            'Manage the products belonging to this workspace.',
            'Create product',
        ],
        'es_419' => [
            'Productos',
            'Administra los productos de este espacio de trabajo.',
            'Crear producto',
        ],
        'pt_BR' => [
            'Produtos',
            'Gerencie os produtos deste espaço de trabalho.',
            'Criar produto',
        ],
        'pt_PT' => [
            'Produtos',
            'Faça a gestão dos produtos desta área de trabalho.',
            'Criar produto',
        ],
    ];

    foreach ($translations as $locale => $expectedText) {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'locale' => $locale,
        ]);

        $company = Company::create([
            'name' => 'Test Company',
            'slug' => 'test-company-'.$locale,
            'created_by' => $user->id,
        ]);

        $user->companies()->attach($company->id, [
            'role' => 'owner',
        ]);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id])
            ->get(route('products'))
            ->assertOk()
            ->assertSee($expectedText[0])
            ->assertSee($expectedText[1])
            ->assertSee($expectedText[2]);
    }
});

test('user can create a product and receives a translated success message', function (string $locale, string $expectedMessage) {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'locale' => $locale,
    ]);

    $company = Company::create([
        'name' => 'Test Company',
        'slug' => 'test-company-'.$locale,
        'created_by' => $user->id,
    ]);

    $user->companies()->attach($company->id, [
        'role' => 'owner',
    ]);

    $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id]);
    app(CurrentCompany::class)->set($company);
    app()->setLocale($locale);

    Livewire::test('products')
        ->set('sku', 'FRAME-001')
        ->set('name', 'Handcrafted Picture Frame')
        ->set('description', 'Wooden picture frame')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee($expectedMessage);

    $this->assertDatabaseHas('products', [
        'company_id' => $company->id,
        'sku' => 'FRAME-001',
        'name' => 'Handcrafted Picture Frame',
        'description' => 'Wooden picture frame',
        'base_unit_code' => 'ea',
        'is_active' => true,
    ]);
})->with([
    'English' => ['en', 'Product created successfully.'],
    'Spanish' => ['es_419', 'Producto creado correctamente.'],
    'Brazilian Portuguese' => ['pt_BR', 'Produto criado com sucesso.'],
    'European Portuguese' => ['pt_PT', 'Produto criado com sucesso.'],
]);

it('user can create a product with a custom base unit', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'locale' => 'en',
    ]);

    $company = Company::create([
        'name' => 'Test Company',
        'slug' => 'test-company-custom-unit',
        'created_by' => $user->id,
    ]);

    $user->companies()->attach($company->id, [
        'role' => 'owner',
    ]);

    $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id]);

    app(CurrentCompany::class)->set($company);
    app()->setLocale('en');

    Livewire::test('products')
        ->set('sku', 'GRAM-001')
        ->set('name', 'Flour')
        ->set('base_unit_code', 'g')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('Product created successfully.');

    $this->assertDatabaseHas('products', [
        'company_id' => $company->id,
        'sku' => 'GRAM-001',
        'name' => 'Flour',
        'base_unit_code' => 'g',
    ]);
});

test('invalid product data is rejected without creating a product', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'locale' => 'en',
    ]);

    $company = Company::create([
        'name' => 'Test Company',
        'slug' => 'test-company-validation',
        'created_by' => $user->id,
    ]);

    $user->companies()->attach($company->id, [
        'role' => 'owner',
    ]);

    $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id]);
    app(CurrentCompany::class)->set($company);
    app()->setLocale('en');

    Livewire::test('products')
        ->set('sku', '')
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['sku', 'name']);

    $this->assertDatabaseCount('products', 0);
});

test('unsupported base unit is rejected without creating a product', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'locale' => 'en',
    ]);

    $company = Company::create([
        'name' => 'Test Company',
        'slug' => 'test-company-invalid-unit',
        'created_by' => $user->id,
    ]);

    $user->companies()->attach($company->id, [
        'role' => 'owner',
    ]);

    $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id]);

    app(CurrentCompany::class)->set($company);
    app()->setLocale('en');

    Livewire::test('products')
        ->set('sku', 'INVALID-UNIT-001')
        ->set('name', 'Invalid Unit Product')
        ->set('base_unit_code', 'xyz')
        ->call('save')
        ->assertHasErrors(['base_unit_code']);

    $this->assertDatabaseMissing('products', [
        'company_id' => $company->id,
        'sku' => 'INVALID-UNIT-001',
    ]);
});
