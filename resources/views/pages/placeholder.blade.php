<x-layouts::app :title="$section">
    <div class="flex flex-col gap-6 p-6">
        <div>
            <flux:heading size="xl">{{ $section }}</flux:heading>
            <flux:subheading>
                This section is under development.
            </flux:subheading>
        </div>

        <div class="rounded-xl border border-neutral-200 p-8 dark:border-neutral-700">
            <div class="flex flex-col items-center justify-center gap-3 py-10 text-center">
                <flux:heading size="lg">
                    Coming soon
                </flux:heading>

                <flux:text class="max-w-md">
                    We're building this part of OpenStock.
                    Functionality will be introduced incrementally.
                </flux:text>
            </div>
        </div>
    </div>
</x-layouts::app>
