<?php

namespace App\Services;

use App\Models\Product;
use App\Support\CurrentCompany;
use Illuminate\Database\Eloquent\Builder;

class CompanyProducts
{
    public function __construct(
        protected CurrentCompany $currentCompany
    ) {}

    /**
     * @return Builder<Product>
     */
    public function query(): Builder
    {
        return Product::query()
            ->where('company_id', $this->currentCompany->id());
    }

    public function findOrFail(int $productId): Product
    {
        return $this->query()->findOrFail($productId);
    }
}
