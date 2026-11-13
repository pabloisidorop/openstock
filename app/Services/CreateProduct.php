<?php

namespace App\Services;

use App\Models\Product;
use App\Support\CurrentCompany;

class CreateProduct
{
    public function __construct(
        protected CurrentCompany $currentCompany
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(array $attributes): Product
    {
        return $this->currentCompany
            ->get()
            ->products()
            ->create($attributes);
    }
}
