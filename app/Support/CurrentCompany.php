<?php

namespace App\Support;

use App\Models\Company;
use Illuminate\Auth\Access\AuthorizationException;

class CurrentCompany
{
    protected ?Company $company = null;

    public function set(Company $company): void
    {
        $this->company = $company;
    }

    public function get(): Company
    {
        if (! $this->company) {
            throw new AuthorizationException(
                'No active workspace has been selected.'
            );
        }

        return $this->company;
    }

    public function id(): int
    {
        return $this->get()->id;
    }
}
