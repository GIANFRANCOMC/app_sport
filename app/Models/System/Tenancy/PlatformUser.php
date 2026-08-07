<?php

declare(strict_types=1);

namespace App\Models\System\Tenancy;

use Illuminate\Database\Eloquent\Model;

final class PlatformUser extends Model
{
    protected $connection = 'landlord';
    protected $table = 'platform_users';
    protected $fillable = ['name', 'email', 'password', 'status', 'last_login_at'];
    protected $hidden = ['password'];
    protected $casts = ['last_login_at' => 'datetime'];
}
