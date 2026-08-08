<?php

declare(strict_types=1);

namespace App\Models\System\Organizations;

use Illuminate\Database\Eloquent\Model;

final class AuthenticationEvent extends Model {
    protected $table = "authentication_events";

    public $timestamps = false;

    protected $fillable = [
        "company_id",
        "user_id",
        "tenant_slug",
        "event_type",
        "result",
        "email",
        "ip_address",
        "user_agent",
        "session_hash",
        "reason",
        "occurred_at",
    ];

    protected $casts = ["occurred_at" => "datetime"];

    public function company() {
        return $this->belongsTo(Company::class, "company_id");
    }

    public function user() {
        return $this->belongsTo(User::class, "user_id");
    }
}
