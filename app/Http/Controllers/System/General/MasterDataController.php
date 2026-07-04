<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\General;

use App\Http\Controllers\System\Base\BaseController;
use App\Services\System\General\MasterDataService;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\Validator;

final class MasterDataController extends BaseController {

    public function list(string $resource): JsonResponse {

        try {

            return response()->json([
                "bool" => true,
                "data" => MasterDataService::list($this->getCompanyId(), $resource)
            ]);

        }catch(\Throwable $e) {

            return $this->handleException($e, "retrieve");

        }

    }

    public function store(Request $request, string $resource): JsonResponse {

        return $this->save($request, $resource);

    }

    public function update(Request $request, string $resource, int $id): JsonResponse {

        return $this->save($request, $resource, $id);

    }

    protected function getTranslationNamespace(): string {

        return "System.General.master_data";

    }

    private function save(Request $request, string $resource, ?int $id = null): JsonResponse {

        $rules = match($resource) {
            "identity-documents" => [
                "code" => "required|string|max:50",
                "name" => "required|string|max:100",
                "is_searchable" => "required|boolean",
                "min_length" => "required|integer|min:1|max:100",
                "max_length" => "required|integer|gte:min_length|max:100",
                "status" => "required|in:active,inactive"
            ],
            "currencies" => [
                "code" => "required|string|max:10",
                "sign" => "required|string|max:10",
                "singular_name" => "required|string|max:50",
                "plural_name" => "required|string|max:50",
                "status" => "required|in:active,inactive"
            ],
            default => [
                "code" => "required|string|max:50",
                "name" => "required|string|max:100",
                "status" => "required|in:active,inactive"
            ]
        };
        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()) {

            return response()->json([
                "bool" => false,
                "msg" => "Revisa los campos marcados para continuar.",
                "errors" => $validator->errors()
            ], 422);

        }

        try {

            $record = MasterDataService::save(
                $this->getCompanyId(),
                $this->getUserId(),
                $resource,
                $validator->validated(),
                $id
            );

            return $id
                ? $this->updatedResponse($record, "updated", "masterData")
                : $this->createdResponse($record, "created", "masterData");

        }catch(\Throwable $e) {

            return $this->handleException($e, $id ? "update" : "create");

        }

    }

}
