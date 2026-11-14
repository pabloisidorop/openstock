<?php

namespace App\Services;

use App\Models\Location;
use App\Support\CurrentCompany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateLocation
{
    public function __construct(
        protected CurrentCompany $currentCompany
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(array $attributes): Location
    {
        $parentId = $attributes['parent_id'] ?? null;

        unset($attributes['parent_id']);

        return DB::transaction(function () use ($attributes, $parentId): Location {
            $company = $this->currentCompany->get();

            $location = $company->locations()->make($attributes);

            if ($parentId !== null) {
                $parent = $company->locations()->find($parentId);

                if ($parent === null) {
                    throw ValidationException::withMessages([
                        'parent_id' => 'The selected parent location is invalid.',
                    ]);
                }

                $location->parent()->associate($parent);
            }

            $location->save();

            return $location;
        });
    }
}
