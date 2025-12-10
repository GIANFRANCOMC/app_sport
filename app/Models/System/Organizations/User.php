<?php

namespace App\Models\System\Organizations;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Helpers\System\Utilities;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use App\Models\System\General\{IdentityDocumentType};
use App\Models\System\Organizations\{Company};
use App\Models\System\Sales\{SaleHeader};

class User extends Authenticatable {

    use HasApiTokens, Notifiable;

    protected $table               = "users";
    protected $primaryKey          = "id";
    public $incrementing           = true;
    public $timestamps             = true;
    public static $snakeAttributes = true;

    protected $appends = [
        "formatted_gender",
        "formatted_status",
        "formatted_preferences"
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "company_id",
        "role_id",
        "identity_document_type_id",
        "document_number",
        "name",
        "email",
        "password",
        "phone_number",
        "gender",
        "gender_description",
        "birthdate",
        "status",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by"
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        "password",
        "remember_token",
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        "email_verified_at" => "datetime",
        "password" => "hashed",
    ];

    // Appends
    public function getFormattedGenderAttribute() {

        return self::getGenders("first", $this->attributes["gender"])["label"] ?? "";

    }

    public function getFormattedStatusAttribute() {

        return self::getStatuses("first", $this->attributes["status"])["label"] ?? "";

    }

    public function getFormattedPreferencesAttribute() {

        return $this->preferences
                    ->mapWithKeys(function ($e) {

                        return [$e->slug => json_decode($e->value)];

                    });

    }

    // Functions
    public static function getGenders($type = "all", $code = "") {

        $statuses = [
            ["code" => "male", "label" => "Masculino"],
            ["code" => "female", "label" => "Femenino"],
            ["code" => "other", "label" => "Otro"]
        ];

        return Utilities::getValues($statuses, $type, $code);

    }

    public static function getStatuses($type = "all", $code = "") {

        $statuses = [
            ["code" => "active", "label" => "Activo"],
            ["code" => "inactive", "label" => "Inactivo"]
        ];

        return Utilities::getValues($statuses, $type, $code);

    }

    public static function getAll(string $type = "default", ?int $company_id = null) {

        return User::where("company_id", $company_id)
                    ->when(in_array($type, ["asset_management"]), function($query) {

                        $query->whereIn("status", ["active"]);

                    })
                    ->with(["identityDocumentType"])
                    ->get();

    }

    // Relationships
    public function company() {

        return $this->belongsTo(Company::class, "company_id", "id");

    }

    public function role() {

        return $this->belongsTo(Role::class, "role_id", "id");

    }

    public function identityDocumentType() {

        return $this->belongsTo(IdentityDocumentType::class, "identity_document_type_id", "id");

    }

    public function preferences() {

        return $this->hasMany(UserPreference::class, "user_id", "id")
                    ->whereIn("status", ["active"]);

    }

    public function salesHeader() {

        return $this->hasMany(SaleHeader::class, "seller_id", "id")
                    ->whereIn("status", ["active"]);

    }

}
