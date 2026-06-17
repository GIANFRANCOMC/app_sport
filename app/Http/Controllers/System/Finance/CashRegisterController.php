<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Finance;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

use App\Http\Controllers\System\Base\BaseController;
use App\Services\System\Finance\{CashRegisterConfigService, CashRegisterService};

final class CashRegisterController extends BaseController {

    public function __construct(private readonly CashRegisterService $service) {

    }

    public function index() {

        return view("System/general/Finance/cash_registers/main");

    }

    public function initParams(Request $request): JsonResponse {

        return response()->json(
            CashRegisterConfigService::getInitParams($this->getCompanyId(), (string) $request->get("page", "main"))
        );

    }

    public function list(): JsonResponse {

        return response()->json([
            "bool" => true,
            "data" => $this->service->listRegisters($this->getCompanyId())
        ]);

    }

    public function sessions(): JsonResponse {

        return response()->json([
            "bool" => true,
            "data" => $this->service->listSessions(
                $this->getCompanyId(),
                $this->getFilters(),
                $this->getPerPage()
            )
        ]);

    }

    public function movements(): JsonResponse {

        return response()->json([
            "bool" => true,
            "data" => $this->service->listMovements(
                $this->getCompanyId(),
                $this->getFilters(),
                $this->getPerPage()
            )
        ]);

    }

    public function summary(): JsonResponse {

        return response()->json([
            "bool" => true,
            "data" => $this->service->summary($this->getCompanyId(), $this->getFilters())
        ]);

    }

    public function open(Request $request): JsonResponse {

        $validator = Validator::make($request->all(), [
            "cash_register_id" => ["required", "integer"],
            "opening_amount" => ["nullable", "numeric", "min:0"],
            "observation" => ["nullable", "string", "max:300"]
        ], $this->validationMessages());

        if($validator->fails()) {

            return $this->validationResponse($validator->errors()->toArray());

        }

        try {

            $session = $this->service->openSession($this->getCompanyId(), $this->getUserId(), $validator->validated());

            return response()->json([
                "bool" => true,
                "msg" => "Caja aperturada correctamente.",
                "data" => $session
            ]);

        }catch(RuntimeException $exception) {

            return response()->json([
                "bool" => false,
                "msg" => $exception->getMessage()
            ], 422);

        }

    }

    public function close(Request $request): JsonResponse {

        $validator = Validator::make($request->all(), [
            "cash_session_id" => ["required", "integer"],
            "counted_amount" => ["nullable", "numeric", "min:0"],
            "payments" => ["nullable", "array"],
            "payments.*.payment_method_id" => ["nullable", "integer"],
            "payments.*.counted_amount" => ["nullable", "numeric", "min:0"],
            "observation" => ["nullable", "string", "max:300"]
        ], $this->validationMessages());

        if($validator->fails()) {

            return $this->validationResponse($validator->errors()->toArray());

        }

        try {

            $session = $this->service->closeSession($this->getCompanyId(), $this->getUserId(), $validator->validated());

            return response()->json([
                "bool" => true,
                "msg" => "Caja cerrada correctamente. Revisa el arqueo para confirmar diferencias.",
                "data" => $session
            ]);

        }catch(RuntimeException $exception) {

            return response()->json([
                "bool" => false,
                "msg" => $exception->getMessage()
            ], 422);

        }

    }

    private function validationMessages(): array {

        return [
            "required" => "Campo obligatorio.",
            "integer" => "Debe seleccionar un registro válido.",
            "numeric" => "Debe ingresar un importe válido.",
            "min" => "Debe ser mayor o igual a 0.",
            "max" => "No debe superar :max caracteres."
        ];

    }

    private function validationResponse(array $errors): JsonResponse {

        return response()->json([
            "bool" => false,
            "msg" => "Revisa los campos marcados para continuar.",
            "errors" => $errors
        ], 422);

    }

}
