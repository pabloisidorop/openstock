<?php

use App\Livewire\Settings\Language;
use App\Models\User;
use Livewire\Livewire;

test('language settings page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('language.edit'))
        ->assertOk()
        ->assertSee('Language')
        ->assertSee('English')
        ->assertSee('Español (Latinoamérica)')
        ->assertSee('Português (Brasil)')
        ->assertSee('Português (Portugal)')
        ->assertSee('Use company default');
});

test('language component loads the existing personal preference', function () {
    $user = User::factory()->create([
        'locale' => 'pt_BR',
    ]);

    $this->actingAs($user);

    Livewire::test(Language::class)
        ->assertSet('locale', 'pt_BR');
});

test('user can save a supported personal language', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Language::class)
        ->set('locale', 'pt_BR')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('language.edit'));

    expect($user->fresh()->locale)->toBe('pt_BR');
});

test('user can return to the company default language', function () {
    $user = User::factory()->create([
        'locale' => 'pt_BR',
    ]);

    $this->actingAs($user);

    Livewire::test(Language::class)
        ->set('locale', '')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('language.edit'));

    expect($user->fresh()->locale)->toBeNull();
});

test('unsupported languages cannot be saved', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Language::class)
        ->set('locale', 'invalid')
        ->call('save')
        ->assertHasErrors(['locale']);

    expect($user->fresh()->locale)->toBeNull();
});
