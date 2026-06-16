<?php

namespace App\Http\Middleware;

use App\Services\System\Organizations\Roles\RolePermissionService;
use Closure;
use Illuminate\Http\Request;

class EnsureModulePermission {

    public function handle(Request $request, Closure $next) {

        $user = $request->user();

        if(!$user) {

            return $next($request);

        }

        if(!RolePermissionService::canAccessRoute($user, $request->route()?->getName())) {

            if($request->expectsJson()) {

                return response()->json([
                    "bool" => false,
                    "msg" => "No tienes permiso para acceder a este módulo."
                ], 403);

            }

            abort(403, "No tienes permiso para acceder a este módulo.");

        }

        return $next($request);

    }

}
