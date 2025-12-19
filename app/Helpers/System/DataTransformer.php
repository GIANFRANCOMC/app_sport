<?php

declare(strict_types=1);

namespace App\Helpers\System;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use stdClass;

/**
 * Data Transformer Helper
 * Provides common data transformation operations
 */
class DataTransformer {

    /**
     * Transform model to array with only specified fields
     *
     * @param Model|null $model Model instance
     * @param array $fields Fields to include
     * @return array|null
     */
    public static function transformModel(?Model $model, array $fields): ?array {

        if($model === null) {

            return null;

        }

        $data = [];

        foreach($fields as $field) {

            if(isset($model->$field)) {

                $data[$field] = $model->$field;

            }

        }

        return $data;

    }

    /**
     * Transform collection to array
     *
     * @param Collection $collection
     * @param callable|null $callback Transform callback
     * @return array
     */
    public static function transformCollection(Collection $collection, ?callable $callback = null): array {

        if($callback === null) {

            return $collection->toArray();

        }

        return $collection->map($callback)->toArray();

    }

    /**
     * Transform paginated results
     *
     * @param LengthAwarePaginator $paginator
     * @param callable|null $callback Transform callback
     * @return array
     */
    public static function transformPaginated(LengthAwarePaginator $paginator, ?callable $callback = null): array {

        $items = $paginator->items();

        if($callback !== null) {

            $items = array_map($callback, $items);

        }

        $data = [
            "data"         => $items,
            "current_page" => $paginator->currentPage(),
            "per_page"     => $paginator->perPage(),
            "total"        => $paginator->total(),
            "last_page"    => $paginator->lastPage(),
            "from"         => $paginator->firstItem(),
            "to"           => $paginator->lastItem(),
            "links"        => [
                "first" => $paginator->url(1),
                "last"  => $paginator->url($paginator->lastPage()),
                "prev"  => $paginator->previousPageUrl(),
                "next"  => $paginator->nextPageUrl()
            ]
        ];

        return $data;

    }

    /**
     * Add formatted status to model
     *
     * @param Model $model
     * @return Model
     */
    public static function addFormattedStatus(Model $model): Model {

        if(isset($model->status)) {

            $status = $model->status;
            $model->formatted_status = ucfirst($status);

        }

        return $model;

    }

    /**
     * Add formatted dates to model
     *
     * @param Model $model
     * @param array $dateFields Date field names
     * @return Model
     */
    public static function addFormattedDates(Model $model, array $dateFields = ["created_at", "updated_at"]): Model {

        foreach($dateFields as $field) {

            if(isset($model->$field)) {

                $formattedField = "formatted_{$field}";
                $model->$formattedField = $model->$field ? date("d/m/Y H:i", strtotime($model->$field)) : null;

            }

        }

        return $model;

    }

    /**
     * Convert array to stdClass recursively
     *
     * @param array $data
     * @return stdClass
     */
    public static function arrayToObject(array $data): stdClass {

        return json_decode(json_encode($data));

    }

    /**
     * Convert stdClass to array recursively
     *
     * @param stdClass $object
     * @return array
     */
    public static function objectToArray(stdClass $object): array {

        return json_decode(json_encode($object), true);

    }

}

