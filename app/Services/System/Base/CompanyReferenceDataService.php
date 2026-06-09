<?php

declare(strict_types=1);

namespace App\Services\System\Base;

use Illuminate\Database\Eloquent\{Builder, Collection};
use InvalidArgumentException;

use App\Models\System\Assets\Asset;
use App\Models\System\Catalogs\{Category, Item};
use App\Models\System\Customers\Customer;
use App\Models\System\Organizations\{Branch, Role, User};
use App\Models\System\Warehouses\Warehouse;

/**
 * Provides reusable, company-scoped records used by module initParams.
 */
final class CompanyReferenceDataService {

    private function __construct(private readonly int $companyId) {

        if($companyId <= 0) {

            throw new InvalidArgumentException("Company ID must be greater than zero.");

        }

    }

    public static function for(int $companyId): self {

        return new self($companyId);

    }

    public function categories(): Collection {

        return Category::query()
                       ->where("company_id", $this->companyId)
                       ->where("status", "active")
                       ->orderBy("name")
                       ->get();

    }

    public function stockWarehouses(): Collection {

        return Warehouse::query()
                        ->with("branch")
                        ->whereHas("branch", function($query) {

                            $query->where("company_id", $this->companyId);

                        })
                        ->where("status", "active")
                        ->orderBy("name")
                        ->get();

    }

    public function activeBranches(): Collection {

        return $this->branchQuery()
                    ->where("status", "active")
                    ->get();

    }

    public function branchesWithSeries(): Collection {

        return $this->branchQuery()
                    ->where("status", "active")
                    ->with("series.documentType")
                    ->get();

    }

    public function customers(): Collection {

        return $this->customerQuery()->get();

    }

    public function activeCustomers(): Collection {

        return $this->customerQuery()
                    ->where("status", "active")
                    ->get();

    }

    public function saleItems(): Collection {

        return Item::query()
                   ->where("company_id", $this->companyId)
                   ->where("status", "active")
                   ->with("currency")
                   ->orderBy("type")
                   ->orderBy("name")
                   ->get();

    }

    public function roles(): Collection {

        return Role::query()
                   ->where("company_id", $this->companyId)
                   ->where("status", "active")
                   ->orderBy("name")
                   ->get();

    }

    public function assets(): Collection {

        return Asset::query()
                    ->where("company_id", $this->companyId)
                    ->where("status", "active")
                    ->orderBy("name")
                    ->get();

    }

    public function users(): Collection {

        return User::query()
                   ->where("company_id", $this->companyId)
                   ->where("status", "active")
                   ->with("identityDocumentType")
                   ->orderBy("name")
                   ->get();

    }

    private function branchQuery(): Builder {

        return Branch::query()
                     ->where("company_id", $this->companyId)
                     ->orderBy("name");

    }

    private function customerQuery(): Builder {

        return Customer::query()
                       ->where("company_id", $this->companyId)
                       ->orderBy("name");

    }

}
