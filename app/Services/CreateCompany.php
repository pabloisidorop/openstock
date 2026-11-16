<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateCompany
{
    public function __construct(
        protected ProvisionCompanyTemplate $provisionCompanyTemplate
    ) {}

    public function handle(
        User $user,
        string $name,
        string $template = 'standard'
    ): Company {
        return DB::transaction(function () use ($user, $name, $template) {
            $company = Company::create([
                'name' => $name,
                'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
                'created_by' => $user->id,
            ]);

            $company->users()->attach($user->id, [
                'role' => 'owner',
            ]);

            $this->provisionCompanyTemplate->handle($company, $template);

            return $company;
        });
    }
}
