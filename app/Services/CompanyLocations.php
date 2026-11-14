<?php

namespace App\Services;

use App\Models\Location;
use App\Support\CurrentCompany;
use Illuminate\Database\Eloquent\Builder;

class CompanyLocations
{
    public function __construct(
        protected CurrentCompany $currentCompany
    ) {}

    /**
     * @return Builder<Location>
     */
    public function query(): Builder
    {
        return Location::query()
            ->where('company_id', $this->currentCompany->id());
    }

    public function findOrFail(int $locationId): Location
    {
        return $this->query()->findOrFail($locationId);
    }
}
