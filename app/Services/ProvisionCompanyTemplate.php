<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Location;
use App\Models\LocationType;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProvisionCompanyTemplate
{
    /**
     * Provision the initial location types and hierarchy for a company.
     */
    public function handle(Company $company, string $templateKey): void
    {
        $template = config("openstock.templates.{$templateKey}");

        if (! is_array($template)) {
            throw ValidationException::withMessages([
                'template' => 'The selected warehouse template is invalid.',
            ]);
        }

        DB::transaction(function () use ($company, $template): void {
            $locationTypes = [];

            foreach ($this->collectLocationDefinitions($template['locations']) as $definition) {
                $typeCode = $definition['type'];

                if (! isset($locationTypes[$typeCode])) {
                    $locationTypes[$typeCode] = $company->locationTypes()->create([
                        'code' => $typeCode,
                        'name' => match ($typeCode) {
                            'warehouse' => 'Warehouse',
                            'storage_area' => 'Storage Area',
                            'operational_area' => 'Operational Area',
                            default => $typeCode,
                        },
                    ]);
                }
            }

            foreach ($template['locations'] as $definition) {
                $this->createLocationTree($company, $definition, $locationTypes);
            }
        });
    }

    /**
     * Collect all unique location type definitions.
     *
     * @param  array<int, array<string, mixed>>  $definitions
     * @return array<int, array<string, mixed>>
     */
    private function collectLocationDefinitions(array $definitions): array
    {
        $collected = [];

        foreach ($definitions as $definition) {
            $collected[] = $definition;

            if (isset($definition['children'])) {
                $collected = array_merge(
                    $collected,
                    $this->collectLocationDefinitions($definition['children'])
                );
            }
        }

        return $collected;
    }

    /**
     * Recursively create locations and their children.
     *
     * @param  array<string, mixed>  $definition
     * @param  array<string, LocationType>  $locationTypes
     */
    private function createLocationTree(
        Company $company,
        array $definition,
        array $locationTypes,
        ?Location $parent = null
    ): void {
        $location = $company->locations()->make([
            'code' => $definition['code'],
            'name' => $definition['name'],
            'type' => $definition['type'],
            'is_active' => true,
        ]);

        $location->locationType()->associate(
            $locationTypes[$definition['type']]
        );

        if ($parent !== null) {
            $location->parent()->associate($parent);
        }

        $location->save();

        foreach ($definition['children'] ?? [] as $child) {
            $this->createLocationTree($company, $child, $locationTypes, $location);
        }
    }
}
