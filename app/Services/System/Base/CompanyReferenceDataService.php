<?php

declare(strict_types=1);

namespace App\Services\System\Base;

use Illuminate\Database\Eloquent\{Builder, Collection};
use Illuminate\Support\Facades\{Auth, DB};
use InvalidArgumentException;

use App\Models\System\Assets\Asset;
use App\Models\System\Catalogs\{Brand, Category, Item};
use App\Models\System\Customers\Customer;
use App\Models\System\Finance\{PaymentMethod, Tax};
use App\Models\System\Organizations\{Branch, Role, User};
use App\Models\System\Warehouses\Warehouse;

/**
 * Provides reusable, company-scoped records used by module initParams.
 */
final class CompanyReferenceDataService {

    private function __construct(
        private readonly int $companyId,
        private readonly ?int $userId = null
    ) {

        if($companyId <= 0) {

            throw new InvalidArgumentException("Company ID must be greater than zero.");

        }

    }

    public static function for(int $companyId, ?int $userId = null): self {

        return new self($companyId, $userId ?? Auth::id());

    }

    public function categories(): Collection {

        return Category::query()
                       ->where("company_id", $this->companyId)
                       ->where("status", "active")
                       ->orderBy("name")
                       ->get();

    }

    public function brands(): Collection {

        return Brand::query()
                    ->where("company_id", $this->companyId)
                    ->where("status", "active")
                    ->orderBy("name")
                    ->get();

    }

    public function stockWarehouses(): Collection {

        $query = Warehouse::query()
                          ->with("branch")
                          ->whereHas("branch", function($query) {

                            $query->where("company_id", $this->companyId);

                          })
                          ->where("status", "active");

        if($branchIds = $this->allowedBranchIds()) {

            $query->whereIn("branch_id", $branchIds);

        }

        return $query->orderBy("name")->get();

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
                   ->with(["currency", "brand", "categoryItems.category", "warehouseItems.warehouse"])
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

    public function taxesFor(string $scope): Collection {

        return Tax::query()
                  ->where("company_id", $this->companyId)
                  ->whereIn("scope", [$scope, "both"])
                  ->where("status", "active")
                  ->orderByDesc("is_default")
                  ->orderBy("name")
                  ->get();

    }

    public function paymentMethodsFor(string $scope): Collection {

        return PaymentMethod::query()
                            ->where("company_id", $this->companyId)
                            ->whereIn("scope", [$scope, "both"])
                            ->where("status", "active")
                            ->orderByDesc("is_default")
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

        $query = Branch::query()
                       ->where("company_id", $this->companyId);

        if($branchIds = $this->allowedBranchIds()) {

            $query->whereIn("id", $branchIds);

        }

        return $query->orderBy("name");

    }

    public function allowedBranchIds(): array {

        if(!$this->userId) {

            return [];

        }

        return DB::table("user_branches")
                 ->where("company_id", $this->companyId)
                 ->where("user_id", $this->userId)
                 ->where("status", "active")
                 ->pluck("branch_id")
                 ->map(fn($branchId) => (int) $branchId)
                 ->values()
                 ->all();

    }

    private function customerQuery(): Builder {

        return Customer::query()
                       ->where("company_id", $this->companyId)
                       ->orderBy("name");

    }

}
