<?php

declare(strict_types=1);

namespace App\Services\System\Notifications;

use App\Helpers\System\Utilities;
use App\Models\System\Customers\SubscriptionEmail;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Mail;

/**
 * Service class for managing Notification operations
 * Handles business logic for sending subscription emails
 */
class NotificationService {

    /**
     * Send pending subscription emails
     *
     * @return void
     */
    public static function sendSubscriptionEmails(): void {

        $now = Carbon::now();

        $records = SubscriptionEmail::where("status", "pending")
                                    ->get();

        foreach($records as $record) {

            try {

                if(Utilities::isDefined($record->to)) {

                    Mail::html($record->body, function($message) use($record) {

                        $message->to($record->to)
                                ->subject($record->subject);

                    });

                    $record->status     = "sent";
                    $record->updated_at = $now;
                    $record->updated_by = null;
                    $record->save();

                }else {

                    $record->status     = "failed";
                    $record->updated_at = $now;
                    $record->updated_by = null;
                    $record->save();

                }

            }catch(Exception $e) {

                $record->status     = "failed";
                $record->updated_at = $now;
                $record->updated_by = null;
                $record->save();

            }

        }

    }

}

