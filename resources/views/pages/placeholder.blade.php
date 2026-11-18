<x-layouts::app :title="__('common.' . $section)">
    <div class="flex flex-col gap-6 p-6">
        <div>
            <flux:heading size="xl">{{ __('common.' . $section) }}</flux:heading>
            <flux:subheading>
                {{ __('common.section_under_development') }}
            </flux:subheading>
        </div>

        <div class="rounded-xl border border-neutral-200 p-8 dark:border-neutral-700">
            <div class="flex flex-col items-center justify-center gap-3 py-10 text-center">
                <flux:heading size="lg">
                    {{ __('common.coming_soon') }}
                </flux:heading>

                <flux:text class="max-w-md">
                    {{ __('common.building_section') }}
                    {{ __('common.functionality_incrementally') }}
                </flux:text>
            </div>
        </div>
    </div>
</x-layouts::app>
