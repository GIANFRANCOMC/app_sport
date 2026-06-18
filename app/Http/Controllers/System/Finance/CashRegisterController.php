<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Finance;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

use App\Http\Controllers\System\Base\BaseController;
use App\Services\System\Finance\{CashRegisterConfigService, CashRegisterService};

final class CashRegisterController extends BaseController {

    public function __construct(private readonly CashRegisterService $service) {

    }

    public function getTranslationNamespace(): string {

        return "cash_registers";

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

    public function export(): Response {

        $rows = $this->service->movementsForExport($this->getCompanyId(), $this->getFilters());
        $handle = fopen("php://temp", "r+");

        fputcsv($handle, [
            "Fecha",
            "Caja",
            "Sucursal",
            "Tipo",
            "Metodo de pago",
            "Referencia",
            "Responsable",
            "Importe"
        ], ";");

        foreach($rows as $row) {

            fputcsv($handle, [
                $row->occurred_at,
                $row->cashSession?->register?->name,
                $row->branch?->name,
                $row->movement_type,
                $row->paymentMethod?->name ?? "Efectivo / caja",
                $row->reference,
                $row->user?->name,
                number_format((float) $row->amount, 2, ".", "")
            ], ";");

        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response("\xEF\xBB\xBF".$csv, 200, [
            "Content-Type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=caja_movimientos.csv"
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

    public function movement(Request $request): JsonResponse {

        $validator = Validator::make($request->all(), [
            "cash_session_id" => ["required", "integer"],
            "payment_method_id" => ["nullable", "integer"],
            "movement_type" => ["required", "in:income,expense,adjustment"],
            "amount" => ["required", "numeric", "min:0.01"],
            "reference" => ["nullable", "string", "max:120"],
            "note" => ["nullable", "string", "max:300"]
        ], $this->validationMessages());

        if($validator->fails()) {

            return $this->validationResponse($validator->errors()->toArray());

        }

        try {

            $movement = $this->service->registerMovement($this->getCompanyId(), $this->getUserId(), $validator->validated());

            return response()->json([
                "bool" => true,
                "msg" => "Movimiento registrado correctamente.",
                "data" => $movement
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
