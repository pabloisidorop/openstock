
<?php

use App\Services\CreateCompany as CreateCompanyService;
use Livewire\Component;

new class extends Component
{
    public string $name = '';

    public function save()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
        ]);

        $company = app(CreateCompanyService::class)->handle(
            auth()->user(),
            $validated['name']
        );

        session(['active_company_id' => $company->id]);

        return $this->redirectRoute('dashboard');
    }
};
?>

<div class="mx-auto max-w-2xl p-6">
    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">
            Create a workspace
        </h1>

        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
            Set up a workspace for your business. You can invite other users later.
        </p>
    </div>

    <form wire:submit="save" class="space-y-6">
        <div>
            <label for="name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                Company name
            </label>

            <input
                id="name"
                type="text"
                wire:model="name"
                placeholder="e.g. My Business"
                class="mt-2 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
            >

            @error('name')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <button
                type="submit"
                wire:loading.attr="disabled"
                class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-700 disabled:opacity-50 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
            >
                <span wire:loading.remove>Create workspace</span>
                <span wire:loading>Creating...</span>
            </button>
        </div>
    </form>
</div>
