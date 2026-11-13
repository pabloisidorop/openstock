<?php

use App\Http\Middleware\SetActiveCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

// Users must be authenticated and verified to create a workspace.
Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('companies/create', 'create-company')
        ->name('companies.create');

    Route::post('companies/switch', function (Request $request) {
        $validated = $request->validate([
            'company_id' => ['required', 'integer'],
        ]);

        $company = $request->user()
            ->companies()
            ->whereKey($validated['company_id'])
            ->firstOrFail();

        $request->session()->put('active_company_id', $company->id);

        return redirect()->route('dashboard');
    })->name('companies.switch');

});

// All workspace-specific pages require a valid active company.
Route::middleware(['auth', 'verified', SetActiveCompany::class])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::livewire('products', 'products')->name('products');

    Route::view('locations', 'pages.placeholder', [
        'section' => 'Locations',
    ])->name('locations');

    Route::view('inventory', 'pages.placeholder', [
        'section' => 'Inventory',
    ])->name('inventory');

    Route::view('transactions', 'pages.placeholder', [
        'section' => 'Transactions',
    ])->name('transactions');

    Route::view('orders', 'pages.placeholder', [
        'section' => 'Orders',
    ])->name('orders');

    Route::view('suppliers', 'pages.placeholder', [
        'section' => 'Suppliers',
    ])->name('suppliers');
});

require __DIR__.'/settings.php';
