<?php

namespace App\Models\System\Organizations;

use App\Helpers\System\Utilities;
use Illuminate\Database\Eloquent\Model;
use stdClass;

class UserPreference extends Model {

    protected $table               = "user_preferences";
    protected $primaryKey          = "id";
    public $incrementing           = true;
    public $timestamps             = true;
    public static $snakeAttributes = true;

    protected $appends = [
        "formatted_status"
    ];

    protected $fillable = [
        "user_id",
        "slug",
        "value",
        "status",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by"
    ];

    // Appends
    public function getFormattedStatusAttribute() {

        return self::getStatuses("first", $this->attributes["status"])["label"] ?? "";

    }

    // Functions
    public static function getStatuses($type = "all", $code = "") {

        $statuses = [
            ["code" => "active", "label" => "Activo"],
            ["code" => "inactive", "label" => "Inactivo"]
        ];

        return Utilities::getValues($statuses, $type, $code);

    }

    public static function updateItems($userId, $slug = "", $data = null, $extras = []) {

        $userPreference = UserPreference::where("user_id", $userId)
                                        ->where("slug", $slug)
                                        ->where("status", "active")
                                        ->first();

        if(!Utilities::isDefined($userPreference)) {

            $userPreference = new UserPreference();
            $userPreference->user_id    = $userId;
            $userPreference->slug       = $slug;
            $userPreference->value      = null;
            $userPreference->status     = "active";
            $userPreference->created_at = now();
            $userPreference->created_by = $userId;

        }

        if(in_array($slug, ["config_companies_sub_sections"])) {

            $value = Utilities::isDefined($userPreference->value) ? json_decode($userPreference->value) : new stdClass();
            $subSectionsValue = collect($value->sub_sections ?? []);

            foreach($data["records"] as $record) {

                if(intval($record["sub_section_id"]) > 0) {

                    $actionType = $extras["type"] ?? "store_update";

                    if(in_array($actionType, ["store_update"])) {

                        $index = $subSectionsValue->search(function($item) use($record) {

                            return $item->sub_section_id == $record["sub_section_id"];

                        });

                        if($index !== false && intval($index) >= 0) {

                            $fieldsToChange = ["visible_in_menu", "is_favorite"];

                            foreach($fieldsToChange as $fieldToChange) {

                                if(is_bool($record[$fieldToChange])) {

                                    $subSectionsValue[$index]->{$fieldToChange} = $record[$fieldToChange];

                                }

                            }

                        }else {

                            $subSectionsValue->push((object) [
                                "sub_section_id" => $record["sub_section_id"],
                                "visible_in_menu" => $record["visible_in_menu"] ?? true,
                                "is_favorite" => $record["is_favorite"] ?? false
                            ]);

                        }

                    }

                }

            }

            $userPreference->value = json_encode([
                "show_actions" => $data["show_actions"] ?? false,
                "show_only_favorites" => $data["show_only_favorites"] ?? false,
                "sub_sections" => $subSectionsValue
            ]);

        }

        $userPreference->status     = "active";
        $userPreference->updated_at = now();
        $userPreference->updated_by = $userId;
        $userPreference->save();

        return ["bool" => true];

    }

    // Relationships
    public function user() {

        return $this->belongsTo(User::class, "user_id", "id");

    }

}
