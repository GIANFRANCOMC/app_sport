<?php

namespace App\Http\Controllers\System;

use App\Helpers\System\Utilities;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use stdClass;

use App\Http\Requests\System\Categories\{StoreCategoryRequest, UpdateCategoryRequest};
use App\Models\System\General\{Category};

class CategoryController extends Controller {

    public function initParams(Request $request) {

        $initParams = new stdClass();

        $config = new stdClass();

        $page = $request->page ?? "";

        if(in_array($page, ["main"])) {

            $config->categories = new stdClass();
            $config->categories->statuses = Category::getStatuses();

        }

        $initParams->config = $config;
        $initParams->bool   = true;

        return $initParams;

    }

    public function list(Request $request) {

        $userAuth = Auth::user();

        $list = Category::when(Utilities::isDefined($request->filter_by), function($query) use($request) {

                        $filter = Utilities::getWordSearch($request->word);

                        if(in_array($request->filter_by, ["all"])) {

                            $query->where(function($query) use($request, $filter) {

                                $query->where("internal_code", "like", $filter)
                                      ->orWhere("name", "like", $filter)
                                      ->orWhere("description", "like", $filter);

                            });

                        }else if(in_array($request->filter_by, ["internal_code", "name", "description"])) {

                            $query->where(function($query) use($request, $filter) {

                                $query->where($request->filter_by, "like", $filter);

                            });

                        }

                    })
                    ->where("company_id", $userAuth->company_id)
                    ->orderBy("name", "ASC")
                    ->paginate($request->per_page ?? Utilities::$per_page_default);

        return $list;

    }

    public function index() {

        return view("System/general/categories/main");

    }

    public function create() {

        //

    }

    public function store(StoreCategoryRequest $request) {

        $userAuth = Auth::user();

        $category = null;

        $categoryExists = Category::where("company_id", $userAuth->company_id)
                                  ->where("internal_code", $request->internal_code)
                                  ->exists();

        if($categoryExists) {

            return response()->json(["bool" => false, "msg" => "El código interno ya ha sido registrado."], 200);

        }

        DB::transaction(function() use($request, $userAuth, &$category) {

            $category = new Category();
            $category->company_id    = $userAuth->company_id;
            $category->internal_code = $request->internal_code;
            $category->name          = $request->name;
            $category->description   = $request->description ?? "";
            $category->status        = $request->status;
            $category->created_at    = now();
            $category->created_by    = $userAuth->id ?? null;
            $category->save();

        });

        $bool = Utilities::isDefined($category);
        $msg  = $bool ? "Categoría creada correctamente." : "No se ha podido crear la categoría.";

        return response()->json(["bool" => $bool, "msg" => $msg, "category" => $category], 200);

    }

    public function show(Category $category) {

        //

    }

    public function edit(Category $category) {

        //

    }

    public function update(UpdateCategoryRequest $request, $id) {

        $userAuth = Auth::user();

        $category = Category::where("id", $id)
                            ->where("company_id", $userAuth->company_id)
                            ->first();

        if(Utilities::isDefined($category)) {

            $categoryExists = Category::where("company_id", $userAuth->company_id)
                                      ->where("internal_code", $request->internal_code)
                                      ->whereNot("id", $category->id)
                                      ->exists();

            if($categoryExists) {

                return response()->json(["bool" => false, "msg" => "El código interno ya ha sido registrado."], 200);

            }

            DB::transaction(function() use($request, $userAuth, &$category) {

                $category->internal_code = $request->internal_code;
                $category->name          = $request->name;
                $category->description   = $request->description ?? "";
                $category->status        = $request->status;
                $category->updated_at    = now();
                $category->updated_by    = $userAuth->id ?? null;
                $category->save();

            });

        }

        $bool = Utilities::isDefined($category);
        $msg  = $bool ? "Categoría editada correctamente." : "No se ha podido editar la categoría.";

        return response()->json(["bool" => $bool, "msg" => $msg, "category" => $category], 200);

    }

    public function destroy(Category $category) {

        //

    }

}
