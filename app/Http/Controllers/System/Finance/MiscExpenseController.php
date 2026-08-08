<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Finance;

use App\Helpers\System\{Utilities};
use App\Http\Controllers\System\Base\{BaseController};
use App\Services\System\Finance\{MiscExpenseService};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{Validator};

final class MiscExpenseController extends BaseController {
    private const TRANSLATION_NAMESPACE = "System.Finance.misc_expenses";

    public function index() {

        return view("System/general/Finance/misc_expenses/main");

    }

    public function list(Request $request) {

        return MiscExpenseService::query(
            $this->getCompanyId(),
            [
                "word" => $request->input("word"),
                "status" => $request->input("status"),
                "branch_id" => $request->input("branch_id"),
            ],
            $this->getUserId()
        )->paginate($this->getPerPage($request, Utilities::$per_page_default));

    }

    public function store(Request $request): JsonResponse {

        $validator = Validator::make($request->all(), [
            "branch_id" => ["nullable", "integer"],
            "cash_session_id" => ["nullable", "integer"],
            "payment_method_id" => ["nullable", "integer"],
            "currency_id" => ["required", "integer"],
            "misc_expense_category_id" => ["nullable", "integer"],
            "responsible_user_id" => ["nullable", "integer"],
            "expense_date" => ["required", "date"],
            "reference" => ["nullable", "string", "max:100"],
            "concept" => ["required", "string", "max:255"],
            "amount" => ["required", "numeric", "gt:0", "decimal:0,".Utilities::$inputs["round"]],
            "description" => ["nullable", "string", "max:2000"],
            "observation" => ["nullable", "string", "max:2000"],
        ], [
            "required" => "Campo obligatorio.",
            "numeric" => "Ingresa un número válido.",
            "gt" => "Debe ser mayor que cero.",
            "decimal" => "Usa hasta ".Utilities::$inputs["round"]." decimales.",
            "max" => "Supera la longitud permitida.",
        ]);

        if($validator->fails()) {

            return response()->json(["bool" => false, "errors" => $validator->errors()], 422);

        }

        try {

            return response()->json([
                "bool" => true,
                "msg" => "Gasto registrado correctamente.",
                "data" => MiscExpenseService::create($this->getCompanyId(), $this->getUserId(), $validator->validated()),
            ], 201);

        } catch(\Throwable $exception) {

            return response()->json(["bool" => false, "msg" => $exception->getMessage()], 422);

        }

    }

    public function cancel(int $id): JsonResponse {

        try {

            return response()->json([
                "bool" => true,
                "msg" => "Gasto anulado correctamente.",
                "data" => MiscExpenseService::cancel($this->getCompanyId(), $id, $this->getUserId()),
            ]);

        } catch(\Throwable $exception) {

            return response()->json(["bool" => false, "msg" => $exception->getMessage()], 422);

        }

    }

    protected function getTranslationNamespace(): string {

        return self::TRANSLATION_NAMESPACE;

    }
}
