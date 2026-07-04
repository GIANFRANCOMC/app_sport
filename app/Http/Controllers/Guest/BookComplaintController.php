<?php

declare(strict_types=1);

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guest\StoreBookComplaintRequest;
use App\Models\Guest\{BookComplaint, IdentityDocumentType};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use stdClass;

final class BookComplaintController extends Controller {

    public function initParams(Request $request) {

        $initParams = new stdClass();
        $config = new stdClass();

        if(in_array((string) $request->input("page"), ["main"], true)) {
            $config->bookComplaints = (object) [
                "types" => BookComplaint::getTypes(),
                "statuses" => BookComplaint::getStatuses()
            ];
            $config->identityDocumentTypes = (object) [
                "records" => IdentityDocumentType::query()
                    ->where("company_id", $request->get("company")->id)
                    ->whereIn("id", [1, 2, 4])
                    ->get()
            ];
        }

        $initParams->config = $config;
        $initParams->bool = true;

        return $initParams;

    }

    public function index(Request $request) {

        return view("Guest/general/book_complaints/main", [
            "company" => $request->get("company")
        ]);

    }

    public function store(StoreBookComplaintRequest $request) {

        $company = $request->get("company");
        $agent = new Agent();
        $todaySubmissions = BookComplaint::query()
            ->where("company_id", $company->id)
            ->where("submitted_ip", $request->ip())
            ->whereDate("created_at", Carbon::today())
            ->count();

        if($todaySubmissions >= (int) config('public_access.complaints.per_day')) {
            return response()->json([
                "bool" => false,
                "msg" => "Alcanzaste el límite diario de solicitudes desde este dispositivo."
            ], 429);
        }

        $bookComplaint = DB::transaction(function() use($request, $company, $agent) {
            $record = BookComplaint::create([
                ...$request->validated(),
                "company_id" => $company->id,
                "admin_response" => null,
                "public_response" => null,
                "tracking_code" => $this->uniqueTrackingCode((int) $company->id),
                "submitted_ip" => $request->ip(),
                "submitted_user_agent" => $request->userAgent(),
                "submitted_platform" => $agent->platform(),
                "submitted_browser" => $agent->browser(),
                "status" => "pending",
                "created_at" => now(),
                "created_by" => null
            ]);

            DB::table("book_complaint_status_histories")->insert([
                "company_id" => $company->id,
                "book_complaint_id" => $record->id,
                "changed_by" => null,
                "previous_status" => null,
                "new_status" => "pending",
                "note" => "Solicitud recibida desde el canal público.",
                "changed_at" => now()
            ]);

            return $record;
        });

        return response()->json([
            "bool" => true,
            "msg" => "Tu solicitud fue registrada correctamente.",
            "tracking_code" => $bookComplaint->tracking_code
        ], 201);

    }

    public function status(Request $request, string $trackingCode) {

        $company = $request->get("company");
        $complaint = BookComplaint::query()
            ->where("company_id", $company->id)
            ->where("tracking_code", Str::upper($trackingCode))
            ->firstOrFail();

        return response()->json([
            "bool" => true,
            "data" => [
                "tracking_code" => $complaint->tracking_code,
                "type" => $complaint->formatted_type,
                "status" => $complaint->formatted_status,
                "public_response" => $complaint->public_response,
                "responded_at" => $complaint->responded_at,
                "created_at" => $complaint->created_at
            ]
        ]);

    }

    private function uniqueTrackingCode(int $companyId): string {

        do {
            $code = Str::upper(Str::random(12));
        }while(BookComplaint::query()
            ->where("company_id", $companyId)
            ->where("tracking_code", $code)
            ->exists());

        return $code;

    }

}
