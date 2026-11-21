<?php

use App\Models\Product;
use App\Services\CompanyProducts;
use App\Services\CreateProduct;
use App\Support\CurrentCompany;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public string $sku = '';

    public string $name = '';

    public string $description = '';

    public string $base_unit_code = 'ea';

    public bool $is_active = true;

    public function mount(): void
    {
        $this->authorize('viewAny', Product::class);
    }

    public function save(): void
    {
        $companyId = app(CurrentCompany::class)->id();

        $validated = $this->validate([
            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'sku')
                    ->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'base_unit_code' => [
             'required',
             'string',
              Rule::in(array_keys(config('openstock.units.supported'))),
            ],
            'is_active' => ['required', 'boolean'],
        ]);

        app(CreateProduct::class)->handle($validated);

        $this->reset('sku', 'name', 'description');
        $this->base_unit_code = config('openstock.units.default');
        $this->is_active = true;

        unset($this->products);

        session()->flash('status', __('common.product_created'));
    }

    #[Computed]
    public function products()
    {
        return app(CompanyProducts::class)
            ->query()
            ->orderBy('name')
            ->get();
    }
};
?>

<div class="flex flex-col gap-6 p-6">
    <div>
        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">
            {{ __('common.products') }}
        </h1>

        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('common.manage_products') }}
        </p>
    </div>

    @if (session()->has('status'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-900 dark:bg-green-950 dark:text-green-300">
            {{ session('status') }}
        </div>
    @endif

    <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
        <h2 class="mb-5 text-lg font-semibold text-zinc-900 dark:text-white">
            {{ __('common.create_product') }}
        </h2>

        <form wire:submit="save" class="space-y-5">
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label for="sku" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        {{ __('common.sku') }} *
                    </label>

                    <input
                        id="sku"
                        type="text"
                        wire:model="sku"
                        maxlength="100"
                        class="mt-2 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                    >

                    @error('sku')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        {{ __('common.product_name') }} *
                    </label>

                    <input
                        id="name"
                        type="text"
                        wire:model="name"
                        class="mt-2 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                    >

                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

                            <div>
                <label for="base_unit_code" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                    {{ __('common.base_unit') }} *
                </label>

                <select
                    id="base_unit_code"
                    wire:model="base_unit_code"
                    class="mt-2 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                >
                    @foreach (config('openstock.units.supported') as $code => $unit)
                        <option value="{{ $code }}">
                            {{ __('common.' . $unit['label']) }} ({{ $code }})
                        </option>
                    @endforeach
                </select>

                @error('base_unit_code')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                    {{ __('common.description') }}
                </label>

                <textarea
                    id="description"
                    wire:model="description"
                    rows="3"
                    class="mt-2 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                ></textarea>

                @error('description')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="inline-flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                    <input
                        type="checkbox"
                        wire:model="is_active"
                        class="rounded border-zinc-300"
                    >
                    {{ __('common.active_product') }}
                </label>

                @error('is_active')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-700 disabled:opacity-50 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                >
                    <span wire:loading.remove>{{ __('common.create_product') }}</span>
                    <span wire:loading>{{ __('common.creating') }}</span>
                </button>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
        @if ($this->products->isEmpty())
            <div class="flex flex-col items-center gap-3 py-12 text-center">
                <h2 class="text-lg font-medium text-zinc-900 dark:text-white">
                    {{ __('common.no_products_yet') }}
                </h2>

                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('common.products_added_to_workspace') }}
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-neutral-50 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300">
                        <tr>
                            <th class="px-4 py-3 font-medium">{{ __('common.sku') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('common.product_name') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('common.base_unit') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('common.description') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('common.status') }}</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @foreach ($this->products as $product)
                            <tr wire:key="product-{{ $product->id }}">
                                <td class="px-4 py-3 font-mono text-zinc-700 dark:text-zinc-300">
                                    {{ $product->sku }}
                                </td>

                                <td class="px-4 py-3 font-medium text-zinc-900 dark:text-white">
                                    {{ $product->name }}
                                </td>

                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                    {{ __('common.' . config('openstock.units.supported')[$product->base_unit_code]['label']) }}
                                    ({{ $product->base_unit_code }})
                                </td>

                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                    {{ $product->description ?: '—' }}
                                </td>

                                <td class="px-4 py-3">
                                    @if ($product->is_active)
                                        <span class="text-green-700 dark:text-green-400">
                                            {{ __('common.active') }}
                                        </span>
                                    @else
                                        <span class="text-zinc-500">
                                            {{ __('common.inactive') }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>