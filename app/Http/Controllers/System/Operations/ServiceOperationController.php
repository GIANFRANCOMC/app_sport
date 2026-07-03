<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Operations;

use App\Http\Controllers\System\Base\BaseController;
use App\Services\System\Operations\{ServiceOperationConfigService, ServiceOperationService};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\Validator;

final class ServiceOperationController extends BaseController {

    public function index() {

        return view("System/general/Operations/service_operations/main");

    }

    public function initParams(Request $request): JsonResponse {

        return response()->json(
            ServiceOperationConfigService::getInitParams(
                $this->getCompanyId(),
                (string) $request->input("page", "restaurant")
            )
        );

    }

    public function stations(Request $request): JsonResponse {

        $request->validate(["branch_id" => ["required", "integer"]]);

        return response()->json([
            "bool" => true,
            "data" => ServiceOperationService::stations(
                $this->getCompanyId(),
                $this->getUserId(),
                (int) $request->input("branch_id"),
                $request->filled("service_floor_id") ? (int) $request->input("service_floor_id") : null
            )
        ]);

    }

    public function floors(Request $request): JsonResponse {

        $request->validate(["branch_id" => ["required", "integer"]]);

        return response()->json([
            "bool" => true,
            "data" => ServiceOperationService::floors(
                $this->getCompanyId(),
                $this->getUserId(),
                (int) $request->input("branch_id")
            )
        ]);

    }

    public function storeFloor(Request $request): JsonResponse {

        $data = $this->validateData($request, [
            "branch_id" => ["required", "integer"],
            "code" => ["required", "string", "max:50"],
            "name" => ["required", "string", "max:150"],
            "level_number" => ["required", "integer", "min:-20", "max:200"],
            "sort_order" => ["nullable", "integer", "min:1", "max:999"],
            "background_color" => ["nullable", "regex:/^#[0-9a-fA-F]{6}$/"],
            "description" => ["nullable", "string", "max:500"],
            "status" => ["nullable", "in:active,inactive"]
        ]);

        if($data instanceof JsonResponse) return $data;

        return $this->execute(
            fn() => ServiceOperationService::createFloor($this->getCompanyId(), $this->getUserId(), $data),
            "Piso registrado correctamente."
        );

    }

    public function sessions(Request $request): JsonResponse {

        return response()->json([
            "bool" => true,
            "data" => ServiceOperationService::sessions(
                $this->getCompanyId(),
                $this->getUserId(),
                $request->only([
                    "branch_id",
                    "service_station_id",
                    "assigned_user_id",
                    "status",
                    "session_type",
                    "date_from",
                    "date_to"
                ]),
                $this->getPerPage($request)
            )
        ]);

    }

    public function show(int $id): JsonResponse {

        return $this->execute(
            fn() => ServiceOperationService::find($this->getCompanyId(), $id, $this->getUserId())
        );

    }

    public function storeStation(Request $request): JsonResponse {

        $data = $this->validateData($request, [
            "branch_id" => ["required", "integer"],
            "service_floor_id" => ["nullable", "integer"],
            "code" => ["required", "string", "max:50"],
            "name" => ["required", "string", "max:150"],
            "station_type" => ["required", "string", "max:30"],
            "capacity" => ["required", "integer", "min:1", "max:999"],
            "position_x" => ["nullable", "numeric", "min:0", "max:100"],
            "position_y" => ["nullable", "numeric", "min:0", "max:100"],
            "color" => ["nullable", "regex:/^#[0-9a-fA-F]{6}$/"],
            "shape" => ["nullable", "in:round,square,rectangle"],
            "description" => ["nullable", "string", "max:500"],
            "status" => ["nullable", "in:active,inactive"]
        ]);

        if($data instanceof JsonResponse) return $data;

        return $this->execute(
            fn() => ServiceOperationService::createStation($this->getCompanyId(), $this->getUserId(), $data),
            "Estación registrada correctamente."
        );

    }

    public function updateStationLayout(Request $request, int $id): JsonResponse {

        $data = $this->validateData($request, [
            "service_floor_id" => ["nullable", "integer"],
            "position_x" => ["nullable", "numeric", "min:0", "max:100"],
            "position_y" => ["nullable", "numeric", "min:0", "max:100"],
            "color" => ["nullable", "regex:/^#[0-9a-fA-F]{6}$/"],
            "shape" => ["nullable", "in:round,square,rectangle"]
        ]);

        if($data instanceof JsonResponse) return $data;

        return $this->execute(
            fn() => ServiceOperationService::updateStationLayout(
                $this->getCompanyId(),
                $this->getUserId(),
                $id,
                $data
            ),
            "Distribución actualizada correctamente."
        );

    }

    public function openSession(Request $request): JsonResponse {

        $data = $this->validateData($request, [
            "branch_id" => ["required", "integer"],
            "service_station_id" => ["nullable", "integer"],
            "customer_id" => ["nullable", "integer"],
            "assigned_user_id" => ["nullable", "integer"],
            "item_id" => ["nullable", "integer"],
            "quantity" => ["nullable", "numeric", "min:0.0001"],
            "session_type" => ["required", "string", "max:30"],
            "start_immediately" => ["nullable", "boolean"],
            "started_at" => ["nullable", "date"],
            "scheduled_at" => ["nullable", "date"],
            "expected_end_at" => ["nullable", "date", "after:scheduled_at"],
            "tolerance_minutes" => ["nullable", "integer", "min:0", "max:1440"],
            "queue_code" => ["nullable", "string", "max:30"],
            "observation" => ["nullable", "string", "max:500"]
        ]);

        if($data instanceof JsonResponse) return $data;

        return $this->execute(
            fn() => ServiceOperationService::open($this->getCompanyId(), $this->getUserId(), $data),
            "Atención iniciada correctamente."
        );

    }

    public function addItem(Request $request, int $id): JsonResponse {

        $data = $this->validateData($request, [
            "item_id" => ["required", "integer"],
            "assigned_user_id" => ["nullable", "integer"],
            "quantity" => ["required", "numeric", "min:0.0001"],
            "start_immediately" => ["nullable", "boolean"],
            "observation" => ["nullable", "string", "max:500"]
        ]);

        if($data instanceof JsonResponse) return $data;

        return $this->execute(
            fn() => ServiceOperationService::addItem($this->getCompanyId(), $this->getUserId(), $id, $data),
            "Detalle agregado a la atención."
        );

    }

    public function startSession(int $id): JsonResponse {

        return $this->execute(
            fn() => ServiceOperationService::start($this->getCompanyId(), $this->getUserId(), $id),
            "Servicio iniciado."
        );

    }

    public function completeSession(int $id): JsonResponse {

        return $this->execute(
            fn() => ServiceOperationService::complete($this->getCompanyId(), $this->getUserId(), $id),
            "Servicio finalizado correctamente."
        );

    }

    public function startItem(int $id): JsonResponse {

        return $this->execute(
            fn() => ServiceOperationService::startItem($this->getCompanyId(), $this->getUserId(), $id),
            "Detalle iniciado."
        );

    }

    public function completeItem(int $id): JsonResponse {

        return $this->execute(
            fn() => ServiceOperationService::completeItem($this->getCompanyId(), $this->getUserId(), $id),
            "Detalle finalizado."
        );

    }

    public function reassignSession(Request $request, int $id): JsonResponse {

        $data = $this->validateData($request, [
            "assigned_user_id" => ["required", "integer"],
            "note" => ["nullable", "string", "max:500"]
        ]);
        if($data instanceof JsonResponse) return $data;

        return $this->execute(
            fn() => ServiceOperationService::reassign(
                $this->getCompanyId(),
                $this->getUserId(),
                $id,
                (int) $data["assigned_user_id"],
                $data["note"] ?? null
            ),
            "Responsable actualizado correctamente."
        );

    }

    public function pauseSession(Request $request, int $id): JsonResponse {

        $data = $this->validateData($request, [
            "service_session_item_id" => ["nullable", "integer"],
            "reason" => ["nullable", "string", "max:500"]
        ]);
        if($data instanceof JsonResponse) return $data;

        return $this->execute(
            fn() => ServiceOperationService::pause(
                $this->getCompanyId(),
                $this->getUserId(),
                $id,
                isset($data["service_session_item_id"]) ? (int) $data["service_session_item_id"] : null,
                $data["reason"] ?? null
            ),
            "Pausa registrada correctamente."
        );

    }

    public function resumeSession(int $id): JsonResponse {

        return $this->execute(
            fn() => ServiceOperationService::resume($this->getCompanyId(), $this->getUserId(), $id),
            "Atención reanudada correctamente."
        );

    }

    public function cancelSession(Request $request, int $id): JsonResponse {

        $data = $this->validateData($request, ["reason" => ["required", "string", "max:500"]]);
        if($data instanceof JsonResponse) return $data;

        return $this->execute(
            fn() => ServiceOperationService::cancel(
                $this->getCompanyId(),
                $this->getUserId(),
                $id,
                $data["reason"]
            ),
            "Atención cancelada correctamente."
        );

    }

    private function validateData(Request $request, array $rules): array|JsonResponse {

        $validator = Validator::make($request->all(), $rules, [
            "required" => "Campo obligatorio.",
            "integer" => "Debe ser un número entero.",
            "numeric" => "Debe ser un valor numérico.",
            "min" => "El valor es menor al permitido.",
            "max" => "El valor supera el límite permitido.",
            "date" => "La fecha no es válida."
        ]);

        return $validator->fails()
            ? $this->validationResponse($validator->errors()->toArray())
            : $validator->validated();

    }

    private function execute(callable $callback, string $message = "Operación completada."): JsonResponse {

        try {
            return response()->json([
                "bool" => true,
                "msg" => $message,
                "data" => $callback()
            ]);
        }catch(\Throwable $exception) {
            return response()->json([
                "bool" => false,
                "msg" => $exception->getMessage()
            ], 422);
        }

    }

    protected function getTranslationNamespace(): string {

        return "System.Operations.service_operation";

    }

}
