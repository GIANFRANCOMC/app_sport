<?php

declare(strict_types=1);

namespace App\Models\System\Tenancy;

use Illuminate\Database\Eloquent\Model;

final class TenantDatabase extends Model {

    protected $connection = 'landlord';

    protected $table = 'tenant_databases';

    protected $fillable = [
        'slug',
        'company_id',
        'database_name',
        'status',
        'last_resolved_at',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by'
    ];

    protected $casts = [
        'company_id' => 'integer',
        'last_resolved_at' => 'datetime'
    ];

    public function domains() {

        return $this->hasMany(TenantDomain::class, 'tenant_database_id');

    }

}
