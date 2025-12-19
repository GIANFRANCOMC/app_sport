<?php

namespace App\Listeners;

use App\Helpers\System\Utilities;
use Illuminate\Auth\Events\Authenticated;
use App\Services\System\Organizations\Companies\CompanySectionService;

class StoreSectionsInCache {

    public function handle(Authenticated $event) {

        $user = $event->user;

        if(Utilities::isDefined($user)) {

            // Call the function to automatically cache sections
            CompanySectionService::getSections($user->company_id);

        }

    }

}
