<?php

namespace App\Http\Controllers\System;

use App\Helpers\System\Utilities;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Mail};
use stdClass;

use App\Models\System\Customers\{SubscriptionEmail};

use Carbon\Carbon;
use Exception;

class NotificationController extends Controller {

    public function sendSubscriptionEmails() {

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

                    $record->status = "sent";
                    $record->updated_at = $now;
                    $record->updated_by = null;
                    $record->save();

                }else {

                    $record->status = "failed";
                    $record->updated_at = $now;
                    $record->updated_by = null;
                    $record->save();

                }

            }catch(Exception $e) {

                $record->status = "failed";
                $record->updated_at = $now;
                $record->updated_by = null;
                $record->save();

            }

        }

    }

}
