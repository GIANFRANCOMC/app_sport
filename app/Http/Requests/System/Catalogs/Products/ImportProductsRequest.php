<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Catalogs\Products;

use App\Http\Requests\System\Base\CompanyFormRequest;
use App\Rules\System\Defaults\BelongsToCompany;

final class ImportProductsRequest extends CompanyFormRequest {

    public function rules(): array {

        return [
            "file" => ["required", "file", "mimes:xlsx,xls,csv", "max:5120"],
            "warehouse_id" => [
                "bail",
                "required",
                "integer",
                new BelongsToCompany("warehouses", ["status" => "active"], "El almacen seleccionado no esta disponible.")
            ]
        ];

    }

    public function messages(): array {

        return array_merge(parent::messages(), [
            "file.file" => "Seleccione un archivo valido.",
            "file.mimes" => "Use un archivo Excel o CSV.",
            "file.max" => "El archivo no debe superar 5 MB."
        ]);

    }

}
