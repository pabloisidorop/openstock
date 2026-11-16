<?php

use App\Services\CreateCompany as CreateCompanyService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

new class extends Component
{
    public string $name = '';

    public string $template = 'standard';

    public function save()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'template' => [
                'required',
                'string',
                Rule::in(array_keys(config('openstock.templates', []))),
            ],
        ]);

        $company = app(CreateCompanyService::class)->handle(
            Auth::user(),
            $validated['name'],
            $validated['template']
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
            Set up a workspace for your business. You can customize your warehouse structure as your operations grow.
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
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                Warehouse setup
            </label>

            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Choose a starting structure. You can change and expand it later.
            </p>

            <div class="mt-3 space-y-3">
                <label
                    @class([
                        'flex cursor-pointer gap-3 rounded-lg border p-4 transition-colors',
                        'border-indigo-500 bg-indigo-50/50 dark:border-indigo-400 dark:bg-indigo-950/30' => $template === 'basic',
                        'border-zinc-300 hover:border-zinc-400 dark:border-zinc-700 dark:hover:border-zinc-500' => $template !== 'basic',
                    ])
                >
                    <input
                        type="radio"
                        wire:model="template"
                        value="basic"
                        class="mt-1 accent-indigo-600"
                    >

                    <span>
                        <span class="block text-sm font-medium text-zinc-900 dark:text-white">
                            Basic
                        </span>
                        <span class="mt-1 block text-sm text-zinc-600 dark:text-zinc-400">
                            Warehouse, Receiving, Storage and Shipping. A simple starting point.
                        </span>
                    </span>
                </label>

                <label
                    @class([
                        'flex cursor-pointer gap-3 rounded-lg border p-4 transition-colors',
                        'border-indigo-500 bg-indigo-50/50 dark:border-indigo-400 dark:bg-indigo-950/30' => $template === 'standard',
                        'border-zinc-300 hover:border-zinc-400 dark:border-zinc-700 dark:hover:border-zinc-500' => $template !== 'standard',
                    ])
                >
                    <input
                        type="radio"
                        wire:model="template"
                        value="standard"
                        class="mt-1 accent-indigo-600"
                    >

                    <span>
                        <span class="flex flex-wrap items-center gap-2 text-sm font-medium text-zinc-900 dark:text-white">
                            Standard
                            <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900 dark:text-indigo-200">
                                Recommended
                            </span>
                        </span>
                        <span class="mt-1 block text-sm text-zinc-600 dark:text-zinc-400">
                            Includes receiving, storage zones, racks, shelves, picking, packing, shipping and returns.
                        </span>
                    </span>
                </label>
            </div>

            @error('template')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <button
                type="submit"
                wire:loading.attr="disabled"
                class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:focus-visible:ring-offset-zinc-900"
            >
                <span wire:loading.remove>Create workspace</span>
                <span wire:loading>Creating...</span>
            </button>
        </div>
    </form>
</div>
