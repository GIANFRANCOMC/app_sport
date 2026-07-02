<?php

declare(strict_types=1);

namespace App\Services\System\Base;

use Illuminate\Database\Eloquent\{Builder, Collection};
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

use App\Models\System\Assets\Asset;
use App\Models\System\Catalogs\{Brand, Category, Item};
use App\Models\System\Customers\Customer;
use App\Models\System\Finance\{PaymentMethod, Tax};
use App\Models\System\Finance\CashRegister;
use App\Models\System\Organizations\{Branch, Role, User};
use App\Models\System\Warehouses\Warehouse;
use App\Services\System\Organizations\AccessScopeService;

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

        $warehouseIds = $this->allowedWarehouseIds();
        if($warehouseIds !== null) {

            $query->whereIn("id", $warehouseIds);

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

    public function cashRegisters(): Collection {

        $query = CashRegister::query()
            ->with("branch")
            ->where("company_id", $this->companyId)
            ->where("status", "active");
        $cashRegisterIds = $this->allowedCashRegisterIds();

        if($cashRegisterIds !== null) {
            $query->whereIn("id", $cashRegisterIds);
        }

        return $query->orderBy("name")->get();

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

        $branchIds = $this->allowedBranchIds();
        if($branchIds !== null) {

            $query->whereIn("id", $branchIds);

        }

        return $query->orderBy("name");

    }

    public function allowedBranchIds(): ?array {

        if(!$this->userId) {

            return null;

        }

        $user = User::query()
            ->where("company_id", $this->companyId)
            ->find($this->userId);

        return $user
            ? AccessScopeService::allowedIds($user, AccessScopeService::BRANCH)
            : [];

    }

    public function allowedCashRegisterIds(): ?array {

        return $this->allowedIds(AccessScopeService::CASH_REGISTER);

    }

    public function allowedWarehouseIds(): ?array {

        return $this->allowedIds(AccessScopeService::WAREHOUSE);

    }

    private function allowedIds(string $type): ?array {

        if(!$this->userId) {
            return null;
        }

        $user = User::query()
            ->where("company_id", $this->companyId)
            ->find($this->userId);

        return $user ? AccessScopeService::allowedIds($user, $type) : [];

    }

    private function customerQuery(): Builder {

        return Customer::query()
                       ->where("company_id", $this->companyId)
                       ->orderBy("name");

    }

}
