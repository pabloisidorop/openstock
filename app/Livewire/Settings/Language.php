<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Language settings')]
class Language extends Component
{
    public string $locale = '';

    /**
     * Load the authenticated user's language preference.
     */
    public function mount(): void
    {
        /** @var User $user */
        $user = request()->user();

        $this->locale = $user->locale ?? '';
    }

    /**
     * Save the authenticated user's language preference.
     */
    public function save(): void
    {
        $supportedLocales = array_keys(
            config('openstock.locales.supported', [])
        );

        $validated = $this->validate([
            'locale' => [
                'present',
                'string',
                Rule::in(array_merge([''], $supportedLocales)),
            ],
        ]);

        /** @var User $user */
        $user = request()->user();

        $user->update([
            'locale' => $validated['locale'] !== ''
                ? $validated['locale']
                : null,
        ]);

        // Reload the page so SetLocale resolves the new preference.
        $this->redirect(route('language.edit'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.settings.language', [
            'supportedLocales' => config('openstock.locales.supported'),
        ]);
    }
}
