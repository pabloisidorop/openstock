<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">
        {{ __('common.language_settings') }}
    </flux:heading>

    <x-settings.layout
        :heading="__('common.language')"
        :subheading="__('common.choose_account_language')"
    >
        <form wire:submit="save" class="my-6 w-full space-y-6">
            <div>
                <label
                    for="locale"
                    class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                >
                    {{ __('common.preferred_language') }}
                </label>

                <select
                    id="locale"
                    wire:model="locale"
                    class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                >
                    <option value="">
                        {{ __('common.use_company_default') }}
                    </option>

                    @foreach ($supportedLocales as $key => $supportedLocale)
                        <option value="{{ $key }}">
                            {{ $supportedLocale['native_name'] }}
                        </option>
                    @endforeach
                </select>

                @error('locale')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('common.company_default_language_help') }}
                </p>
            </div>

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">
                    {{ __('common.save') }}
                </flux:button>
            </div>
        </form>
    </x-settings.layout>
</section>