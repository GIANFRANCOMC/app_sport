<?php

declare(strict_types=1);

namespace App\Services\System\Operations;

use App\Helpers\System\Utilities;
use App\Models\System\Catalogs\Item;
use App\Models\System\Customers\Customer;
use App\Models\System\Operations\{ServiceFloor, ServiceSession, ServiceSessionItem, ServiceStation};
use App\Models\System\Organizations\{Branch, User};
use App\Services\System\Base\CompanyReferenceDataService;
use App\Services\System\Organizations\AccessScopeService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class ServiceOperationService {

    public const STATUS_PENDING = "pending";
    public const STATUS_IN_PROGRESS = "in_progress";
    public const STATUS_COMPLETED = "completed";
    public const STATUS_CANCELED = "canceled";

    public static function stationTypes(): array {

        return [
            ["code" => "table", "label" => "Mesa"],
            ["code" => "chair", "label" => "Sillón"],
            ["code" => "booth", "label" => "Cabina"],
            ["code" => "room", "label" => "Habitación"],
            ["code" => "court", "label" => "Cancha"],
            ["code" => "bay", "label" => "Bahía de atención"],
            ["code" => "other", "label" => "Otro"]
        ];

    }

    public static function sessionTypes(): array {

        return [
            ["code" => "restaurant", "label" => "Atención en mesa"],
            ["code" => "catalog_service", "label" => "Servicio del catálogo"],
            ["code" => "appointment", "label" => "Cita"],
            ["code" => "rental", "label" => "Alquiler"],
            ["code" => "other", "label" => "Otra operación"]
        ];

    }

    public static function sessionStatuses(): array {

        return [
            ["code" => self::STATUS_PENDING, "label" => "Pendiente"],
            ["code" => self::STATUS_IN_PROGRESS, "label" => "En curso"],
            ["code" => self::STATUS_COMPLETED, "label" => "Finalizada"],
            ["code" => self::STATUS_CANCELED, "label" => "Cancelada"]
        ];

    }

    public static function stationColors(): array {

        return ["#2899e5", "#1a1a35", "#10b981", "#d97706", "#dc2626", "#7c3aed"];

    }

    public static function stationShapes(): array {

        return [
            ["code" => "round", "label" => "Redonda"],
            ["code" => "square", "label" => "Cuadrada"],
            ["code" => "rectangle", "label" => "Rectangular"]
        ];

    }

    public static function createFloor(int $companyId, int $actorId, array $data): ServiceFloor {

        self::requireBranch($companyId, (int) $data["branch_id"], $actorId);

        $duplicate = ServiceFloor::query()
            ->where("company_id", $companyId)
            ->where("branch_id", (int) $data["branch_id"])
            ->where("code", trim((string) $data["code"]))
            ->exists();

        if($duplicate) {
            throw new DomainException("Ya existe un piso con ese código en la sucursal.");
        }

        return ServiceFloor::create([
            "company_id" => $companyId,
            "branch_id" => (int) $data["branch_id"],
            "code" => trim((string) $data["code"]),
            "name" => trim((string) $data["name"]),
            "level_number" => (int) ($data["level_number"] ?? 1),
            "sort_order" => (int) ($data["sort_order"] ?? 1),
            "background_color" => (string) ($data["background_color"] ?? "#f7f8fa"),
            "description" => $data["description"] ?? null,
            "status" => (string) ($data["status"] ?? "active"),
            "created_at" => now(),
            "created_by" => $actorId
        ]);

    }

    public static function floors(int $companyId, int $actorId, int $branchId) {

        self::requireBranch($companyId, $branchId, $actorId);

        return ServiceFloor::query()
            ->where("company_id", $companyId)
            ->where("branch_id", $branchId)
            ->where("status", "active")
            ->withCount(["stations" => fn($query) => $query->where("status", "active")])
            ->orderBy("sort_order")
            ->orderBy("level_number")
            ->orderBy("name")
            ->get();

    }

    public static function createStation(int $companyId, int $actorId, array $data): ServiceStation {

        self::requireBranch($companyId, (int) $data["branch_id"], $actorId);
        $floor = self::requireOptionalFloor(
            $companyId,
            (int) $data["branch_id"],
            $data["service_floor_id"] ?? null
        );

        $duplicate = ServiceStation::query()
            ->where("company_id", $companyId)
            ->where("branch_id", (int) $data["branch_id"])
            ->where("code", trim((string) $data["code"]))
            ->exists();

        if($duplicate) {
            throw new DomainException("Ya existe una mesa o estación con ese código en la sucursal.");
        }

        $position = self::nextStationPosition(
            $companyId,
            (int) $data["branch_id"],
            $floor?->id
        );

        return ServiceStation::create([
            "company_id" => $companyId,
            "branch_id" => (int) $data["branch_id"],
            "service_floor_id" => $floor?->id,
            "code" => trim((string) $data["code"]),
            "name" => trim((string) $data["name"]),
            "station_type" => (string) ($data["station_type"] ?? "table"),
            "capacity" => (int) ($data["capacity"] ?? 1),
            "position_x" => (float) ($data["position_x"] ?? $position["x"]),
            "position_y" => (float) ($data["position_y"] ?? $position["y"]),
            "color" => (string) ($data["color"] ?? "#2899e5"),
            "shape" => (string) ($data["shape"] ?? "round"),
            "description" => $data["description"] ?? null,
            "status" => (string) ($data["status"] ?? "active"),
            "created_at" => now(),
            "created_by" => $actorId
        ]);

    }

    public static function stations(int $companyId, int $actorId, int $branchId, ?int $floorId = null) {

        self::requireBranch($companyId, $branchId, $actorId);

        return ServiceStation::query()
            ->where("company_id", $companyId)
            ->where("branch_id", $branchId)
            ->where("status", "active")
            ->when($floorId, fn($query) => $query->where("service_floor_id", $floorId))
            ->with([
                "floor",
                "activeSession.customer",
                "activeSession.assignedUser",
                "activeSession.items.assignedUser"
            ])
            ->orderBy("name")
            ->get();

    }

    public static function updateStationLayout(
        int $companyId,
        int $actorId,
        int $stationId,
        array $data
    ): ServiceStation {

        return DB::transaction(function() use($companyId, $actorId, $stationId, $data) {
            $station = ServiceStation::query()
                ->where("company_id", $companyId)
                ->lockForUpdate()
                ->find($stationId);

            if(!$station) {
                throw new DomainException("La mesa o estación no está disponible.");
            }

            self::requireBranch($companyId, (int) $station->branch_id, $actorId);
            $floor = self::requireOptionalFloor(
                $companyId,
                (int) $station->branch_id,
                $data["service_floor_id"] ?? $station->service_floor_id
            );

            $station->service_floor_id = $floor?->id;
            $station->position_x = self::percentage($data["position_x"] ?? $station->position_x);
            $station->position_y = self::percentage($data["position_y"] ?? $station->position_y);
            $station->color = (string) ($data["color"] ?? $station->color);
            $station->shape = (string) ($data["shape"] ?? $station->shape);
            $station->updated_at = now();
            $station->updated_by = $actorId;
            $station->save();

            return $station->fresh(["floor", "activeSession.customer", "activeSession.items"]);
        });

    }

    public static function sessions(
        int $companyId,
        int $actorId,
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {

        $query = ServiceSession::query()
            ->where("company_id", $companyId)
            ->with(["branch", "station", "customer", "assignedUser", "items.assignedUser", "sale"]);

        $branchIds = CompanyReferenceDataService::for($companyId, $actorId)->allowedBranchIds();
        if($branchIds !== null) {
            $query->whereIn("branch_id", $branchIds);
        }

        foreach(["branch_id", "service_station_id", "assigned_user_id", "session_type"] as $field) {
            if(!empty($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }

        if(($filters["status"] ?? null) === "open") {
            $query->whereIn("status", [self::STATUS_PENDING, self::STATUS_IN_PROGRESS]);
        }elseif(!empty($filters["status"])) {
            $query->where("status", $filters["status"]);
        }

        if(!empty($filters["date_from"])) {
            $query->whereDate("created_at", ">=", $filters["date_from"]);
        }

        if(!empty($filters["date_to"])) {
            $query->whereDate("created_at", "<=", $filters["date_to"]);
        }

        return $query->orderByDesc("id")->paginate($perPage);

    }

    public static function find(int $companyId, int $sessionId, ?int $actorId = null): ServiceSession {

        $session = ServiceSession::query()
            ->where("company_id", $companyId)
            ->with(["branch", "station", "customer", "assignedUser", "items.item", "items.assignedUser", "sale"])
            ->find($sessionId);

        if(!$session) {
            throw new DomainException("La sesión de servicio no está disponible.");
        }

        if($actorId) {
            self::requireBranch($companyId, (int) $session->branch_id, $actorId);
        }

        return $session;

    }

    public static function open(int $companyId, int $actorId, array $data): ServiceSession {

        return DB::transaction(function() use($companyId, $actorId, $data) {

            $branchId = (int) $data["branch_id"];
            $stationId = !empty($data["service_station_id"])
                ? (int) $data["service_station_id"]
                : null;

            self::requireBranch($companyId, $branchId, $actorId);
            self::requireOptionalUser($companyId, $data["assigned_user_id"] ?? null);
            self::requireOptionalCustomer($companyId, $data["customer_id"] ?? null);

            if($stationId) {
                $station = ServiceStation::query()
                    ->where("company_id", $companyId)
                    ->where("branch_id", $branchId)
                    ->where("status", "active")
                    ->lockForUpdate()
                    ->find($stationId);

                if(!$station) {
                    throw new DomainException("La estación no está activa o no pertenece a la sucursal.");
                }

                $occupied = ServiceSession::query()
                    ->where("company_id", $companyId)
                    ->where("service_station_id", $stationId)
                    ->whereIn("status", [self::STATUS_PENDING, self::STATUS_IN_PROGRESS])
                    ->exists();

                if($occupied) {
                    throw new DomainException("La estación ya tiene una atención en curso.");
                }
            }

            $startedAt = !empty($data["start_immediately"])
                ? Carbon::parse($data["started_at"] ?? now())
                : null;

            $session = ServiceSession::create([
                "company_id" => $companyId,
                "branch_id" => $branchId,
                "service_station_id" => $stationId,
                "customer_id" => $data["customer_id"] ?? null,
                "assigned_user_id" => $data["assigned_user_id"] ?? null,
                "opened_by" => $actorId,
                "reference" => self::nextReference(),
                "session_type" => (string) ($data["session_type"] ?? "catalog_service"),
                "status" => $startedAt ? self::STATUS_IN_PROGRESS : self::STATUS_PENDING,
                "started_at" => $startedAt,
                "observation" => $data["observation"] ?? null,
                "created_at" => now(),
                "created_by" => $actorId
            ]);

            if(!empty($data["item_id"])) {
                self::addItem($companyId, $actorId, $session->id, [
                    "item_id" => (int) $data["item_id"],
                    "assigned_user_id" => $data["assigned_user_id"] ?? null,
                    "quantity" => $data["quantity"] ?? 1,
                    "start_immediately" => (bool) $startedAt
                ]);
            }

            return self::find($companyId, (int) $session->id, $actorId);

        });

    }

    public static function addItem(int $companyId, int $actorId, int $sessionId, array $data): ServiceSessionItem {

        return DB::transaction(function() use($companyId, $actorId, $sessionId, $data) {

            $session = self::lockOpenSession($companyId, $sessionId);
            self::requireBranch($companyId, (int) $session->branch_id, $actorId);
            $item = Item::query()
                ->where("company_id", $companyId)
                ->where("status", "active")
                ->find((int) $data["item_id"]);

            if(!$item) {
                throw new DomainException("El producto o servicio no está disponible.");
            }

            self::requireOptionalUser($companyId, $data["assigned_user_id"] ?? null);
            $startedAt = !empty($data["start_immediately"]) ? now() : null;

            return ServiceSessionItem::create([
                "company_id" => $companyId,
                "service_session_id" => $session->id,
                "item_id" => $item->id,
                "assigned_user_id" => $data["assigned_user_id"] ?? $session->assigned_user_id,
                "name" => $item->name,
                "item_type" => $item->type,
                "quantity" => (float) ($data["quantity"] ?? 1),
                "unit_price" => (float) $item->price,
                "status" => $startedAt ? self::STATUS_IN_PROGRESS : self::STATUS_PENDING,
                "started_at" => $startedAt,
                "observation" => $data["observation"] ?? null,
                "created_at" => now(),
                "created_by" => $actorId
            ]);

        });

    }

    public static function start(int $companyId, int $actorId, int $sessionId): ServiceSession {

        return DB::transaction(function() use($companyId, $actorId, $sessionId) {
            $session = self::lockOpenSession($companyId, $sessionId);
            self::requireBranch($companyId, (int) $session->branch_id, $actorId);

            if($session->status === self::STATUS_IN_PROGRESS) {
                return self::find($companyId, $sessionId, $actorId);
            }

            $session->status = self::STATUS_IN_PROGRESS;
            $session->started_at = now();
            $session->updated_at = now();
            $session->updated_by = $actorId;
            $session->save();

            return self::find($companyId, $sessionId, $actorId);
        });

    }

    public static function startItem(int $companyId, int $actorId, int $itemId): ServiceSessionItem {

        return self::changeItemTiming($companyId, $actorId, $itemId, false);

    }

    public static function completeItem(int $companyId, int $actorId, int $itemId): ServiceSessionItem {

        return self::changeItemTiming($companyId, $actorId, $itemId, true);

    }

    public static function complete(
        int $companyId,
        int $actorId,
        int $sessionId,
        ?int $saleHeaderId = null
    ): ServiceSession {

        return DB::transaction(function() use($companyId, $actorId, $sessionId, $saleHeaderId) {
            $session = self::lockOpenSession($companyId, $sessionId);
            self::requireBranch($companyId, (int) $session->branch_id, $actorId);
            $endedAt = now();
            $startedAt = $session->started_at ? Carbon::parse($session->started_at) : Carbon::parse($session->created_at);

            ServiceSessionItem::query()
                ->where("company_id", $companyId)
                ->where("service_session_id", $session->id)
                ->whereIn("status", [self::STATUS_PENDING, self::STATUS_IN_PROGRESS])
                ->get()
                ->each(function(ServiceSessionItem $item) use($endedAt, $actorId) {
                    $itemStart = $item->started_at ? Carbon::parse($item->started_at) : Carbon::parse($item->created_at);
                    $item->status = self::STATUS_COMPLETED;
                    $item->started_at = $item->started_at ?? $itemStart;
                    $item->ended_at = $endedAt;
                    $item->duration_minutes = $itemStart->diffInMinutes($endedAt);
                    $item->updated_at = now();
                    $item->updated_by = $actorId;
                    $item->save();
                });

            $session->sale_header_id = $saleHeaderId ?? $session->sale_header_id;
            $session->status = self::STATUS_COMPLETED;
            $session->started_at = $session->started_at ?? $startedAt;
            $session->ended_at = $endedAt;
            $session->duration_minutes = $startedAt->diffInMinutes($endedAt);
            $session->closed_by = $actorId;
            $session->updated_at = now();
            $session->updated_by = $actorId;
            $session->save();

            return self::find($companyId, $sessionId, $actorId);
        });

    }

    public static function attachSale(int $companyId, int $actorId, int $sessionId, int $saleHeaderId): ServiceSession {

        return self::complete($companyId, $actorId, $sessionId, $saleHeaderId);

    }

    private static function changeItemTiming(
        int $companyId,
        int $actorId,
        int $itemId,
        bool $complete
    ): ServiceSessionItem {

        return DB::transaction(function() use($companyId, $actorId, $itemId, $complete) {
            $item = ServiceSessionItem::query()
                ->where("company_id", $companyId)
                ->with("session")
                ->lockForUpdate()
                ->find($itemId);

            if(!$item || in_array($item->status, [self::STATUS_COMPLETED, self::STATUS_CANCELED], true)) {
                throw new DomainException("El detalle de servicio no está disponible para esta acción.");
            }

            if(!$item->session || !in_array($item->session->status, [self::STATUS_PENDING, self::STATUS_IN_PROGRESS], true)) {
                throw new DomainException("La atención ya terminó o no está disponible.");
            }

            self::requireBranch($companyId, (int) $item->session->branch_id, $actorId);

            if($complete) {
                $endedAt = now();
                $startedAt = $item->started_at ? Carbon::parse($item->started_at) : Carbon::parse($item->created_at);
                $item->status = self::STATUS_COMPLETED;
                $item->started_at = $item->started_at ?? $startedAt;
                $item->ended_at = $endedAt;
                $item->duration_minutes = $startedAt->diffInMinutes($endedAt);
            }else {
                $item->status = self::STATUS_IN_PROGRESS;
                $item->started_at = now();

                if($item->session->status === self::STATUS_PENDING) {
                    $item->session->status = self::STATUS_IN_PROGRESS;
                    $item->session->started_at = now();
                    $item->session->updated_at = now();
                    $item->session->updated_by = $actorId;
                    $item->session->save();
                }
            }

            $item->updated_at = now();
            $item->updated_by = $actorId;
            $item->save();

            return $item->fresh(["item", "assignedUser"]);
        });

    }

    private static function lockOpenSession(int $companyId, int $sessionId): ServiceSession {

        $session = ServiceSession::query()
            ->where("company_id", $companyId)
            ->whereIn("status", [self::STATUS_PENDING, self::STATUS_IN_PROGRESS])
            ->lockForUpdate()
            ->find($sessionId);

        if(!$session) {
            throw new DomainException("La sesión ya terminó o no está disponible.");
        }

        return $session;

    }

    private static function requireBranch(int $companyId, int $branchId, ?int $actorId = null): Branch {

        $branch = Branch::query()
            ->where("company_id", $companyId)
            ->where("status", "active")
            ->find($branchId);

        if(!$branch) {
            throw new DomainException("La sucursal no está activa o no pertenece a la empresa.");
        }

        if($actorId) {
            $actor = User::query()
                ->where("company_id", $companyId)
                ->where("status", "active")
                ->find($actorId);

            if(!$actor || !AccessScopeService::canAccess($actor, AccessScopeService::BRANCH, $branchId)) {
                throw new DomainException("No tienes acceso operativo a esta sucursal.");
            }
        }

        return $branch;

    }

    private static function requireOptionalUser(int $companyId, mixed $userId): ?User {

        if(empty($userId)) return null;

        $user = User::query()
            ->where("company_id", $companyId)
            ->where("status", "active")
            ->find((int) $userId);

        if(!$user) {
            throw new DomainException("El responsable no está activo o no pertenece a la empresa.");
        }

        return $user;

    }

    private static function requireOptionalCustomer(int $companyId, mixed $customerId): ?Customer {

        if(empty($customerId)) return null;

        $customer = Customer::query()
            ->where("company_id", $companyId)
            ->where("status", "active")
            ->find((int) $customerId);

        if(!$customer) {
            throw new DomainException("El cliente no está activo o no pertenece a la empresa.");
        }

        return $customer;

    }

    private static function requireOptionalFloor(int $companyId, int $branchId, mixed $floorId): ?ServiceFloor {

        if(empty($floorId)) return null;

        $floor = ServiceFloor::query()
            ->where("company_id", $companyId)
            ->where("branch_id", $branchId)
            ->where("status", "active")
            ->find((int) $floorId);

        if(!$floor) {
            throw new DomainException("El piso no está activo o no pertenece a la sucursal.");
        }

        return $floor;

    }

    private static function nextStationPosition(int $companyId, int $branchId, ?int $floorId): array {

        $position = ServiceStation::query()
            ->where("company_id", $companyId)
            ->where("branch_id", $branchId)
            ->where("service_floor_id", $floorId)
            ->count();

        return [
            "x" => 8 + (($position % 6) * 16.8),
            "y" => min(11 + (intdiv($position, 6) * 19.5), 89)
        ];

    }

    private static function percentage(mixed $value): float {

        return min(95, max(5, round((float) $value, 4)));

    }

    private static function nextReference(): string {

        return "SRV-" . Utilities::generateCode(10);

    }

}
