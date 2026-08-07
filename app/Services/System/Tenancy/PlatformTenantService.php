<?php

declare(strict_types=1);

namespace App\Services\System\Tenancy;

use App\Models\System\Tenancy\TenantDatabase;
use App\Services\System\Organizations\Companies\CompanySectionService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class PlatformTenantService
{
    public function __construct(private readonly TenantConnectionManager $connections) {}

    public function modules(TenantDatabase $tenant): Collection
    {
        $this->connections->connect($tenant);

        try {
            return DB::table('sub_sections as ss')
                ->join('sections as s', 's.id', '=', 'ss.section_id')
                ->join('menu_categories as mc', 'mc.id', '=', 's.menu_category_id')
                ->leftJoin('menu_groups as mg', 'mg.id', '=', 'ss.menu_group_id')
                ->leftJoin('companies_sub_sections as css', function($join) use($tenant): void {
                    $join->on('css.sub_section_id', '=', 'ss.id')
                        ->where('css.company_id', '=', $tenant->company_id);
                })
                ->where('ss.status', 'active')
                ->orderBy('mc.order')
                ->orderBy('s.order')
                ->orderByRaw('COALESCE(mg.`order`, 0)')
                ->orderBy('ss.order')
                ->get([
                    'ss.id', 'ss.dom_label', 'ss.dom_route', 'ss.order',
                    's.id as section_id', 's.dom_label as section_name', 's.order as section_order',
                    'mc.name as category_name', 'mc.order as category_order',
                    'mg.name as group_name',
                    'css.status as company_status',
                ]);
        } finally {
            $this->connections->disconnect();
        }
    }

    public function updateModules(TenantDatabase $tenant, array $enabledModuleIds): int
    {
        $this->connections->connect($tenant);

        try {
            $companyId = (int) $tenant->company_id;
            if($companyId <= 0 || !DB::table('companies')->where('id', $companyId)->exists()) {
                throw new RuntimeException('El tenant no tiene una empresa raíz válida.');
            }

            $enabled = collect($enabledModuleIds)->map(fn($id) => (int) $id)->unique();
            $modules = DB::table('sub_sections as ss')
                ->join('sections as s', 's.id', '=', 'ss.section_id')
                ->join('menu_categories as mc', 'mc.id', '=', 's.menu_category_id')
                ->where('ss.status', 'active')
                ->get([
                    'ss.id', 'ss.order as item_order', 's.order as section_order',
                    'mc.order as category_order',
                ]);

            DB::transaction(function() use($companyId, $enabled, $modules): void {
                foreach($modules as $module) {
                    DB::table('companies_sub_sections')->updateOrInsert(
                        ['company_id' => $companyId, 'sub_section_id' => $module->id],
                        [
                            'section_order' => ((int) $module->category_order * 100) + (int) $module->section_order,
                            'sub_section_order' => (int) $module->item_order,
                            'status' => $enabled->contains((int) $module->id) ? 'active' : 'inactive',
                            'updated_at' => now(),
                        ]
                    );
                }
            });

            CompanySectionService::clearCompanyCache($companyId);

            return $enabled->intersect($modules->pluck('id')->map(fn($id) => (int) $id))->count();
        } finally {
            $this->connections->disconnect();
        }
    }
}
