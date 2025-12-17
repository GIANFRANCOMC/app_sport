<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Notifications;

use App\Http\Controllers\{Controller};
use Illuminate\Http\Request;

use App\Services\System\Notifications\NotificationService;

class NotificationController extends Controller {

    /**
     * Send pending subscription emails
     *
     * @return void
     */
    public function sendSubscriptionEmails(): void {

        NotificationService::sendSubscriptionEmails();

    }

}
