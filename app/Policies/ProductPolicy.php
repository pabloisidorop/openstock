<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use App\Support\CurrentCompany;
use Illuminate\Auth\Access\AuthorizationException;

class ProductPolicy
{
    private function canAccessActiveCompany(User $user): bool
    {
        try {
            $companyId = app(CurrentCompany::class)->id();
        } catch (AuthorizationException) {
            return false;
        }

        return $user->companies()
            ->whereKey($companyId)
            ->exists();
    }

    public function viewAny(User $user): bool
    {
        return $this->canAccessActiveCompany($user);
    }

    public function view(User $user, Product $product): bool
    {
        return $this->canAccessActiveCompany($user)
            && $product->company_id === app(CurrentCompany::class)->id();
    }

    public function create(User $user): bool
    {
        return $this->canAccessActiveCompany($user);
    }

    public function update(User $user, Product $product): bool
    {
        return $this->canAccessActiveCompany($user)
            && $product->company_id === app(CurrentCompany::class)->id();
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->canAccessActiveCompany($user)
            && $product->company_id === app(CurrentCompany::class)->id();
    }

    public function restore(User $user, Product $product): bool
    {
        return false;
    }

    public function forceDelete(User $user, Product $product): bool
    {
        return false;
    }
}
