<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Essentials;

use App\Exports\{BranchExport, CustomerExport, ItemExport, SaleExport, UserExport};
use App\Helpers\System\Utilities;
use App\Http\Controllers\System\Base\BaseController;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use stdClass;
use Exception;

use App\Models\System\Organizations\{User};
use App\Models\System\Catalogs\{Item};
use App\Models\System\Customers\{Customer};
use App\Models\System\Organizations\{Branch, Company};
use App\Models\System\Sales\{SaleHeader};

use App\Services\System\Essentials\ReportConfigService;
use App\Services\System\Organizations\AccessScopeService;
use App\Services\System\Organizations\Companies\CompanySettingService;
use App\Services\System\Finance\FinancialSettlementReportService;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends BaseController {

    /**
     * Translation namespace for report module
     */
    private const TRANSLATION_NAMESPACE = "System.Essentials.report";

    private function exportFileName(string $resource, string $extension = "xlsx"): string {

        return sprintf("gympe-%s-%s.%s", $resource, now()->format("Ymd-His"), $extension);

    }

    private function assertExportLimit(Builder $query): void {

        $limit = max(100, (int) CompanySettingService::value(
            $this->getCompanyId(),
            "reports",
            "export_max_rows",
            25000
        ));

        if((clone $query)->limit($limit + 1)->count() > $limit) {

            throw ValidationException::withMessages([
                "filters" => "El reporte supera {$limit} registros. Reduce el rango o aplica más filtros."
            ]);

        }

    }

    private function allowedBranchIds(): ?array {

        return AccessScopeService::allowedIds(auth()->user(), AccessScopeService::BRANCH);

    }

    /**
     * Get initialization parameters for the module
     *
     * @param Request $request
     * @return \stdClass
     */
    public function initParams(Request $request) {

        $page = $this->getPage($request);
        return ReportConfigService::getInitParams($this->getCompanyId(), $page, $this->getUserId());

    }

    /**
     * Display the reports index page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index() {

        return view("System/general/Essentials/reports/main");

    }

    public function sale(Request $request) {

        $message500 = "Por favor, no altere el enlace generado. Cualquier modificación podría invalidarlo. Si tiene algún problema, solicite uno nuevo";
        $message404 = "Información no encontrada";

        if(Utilities::isDefined($request->document)) {

            $document  = base64_decode((string) $request->document, true);
            $printType = base64_decode((string) $request->type, true);
            $encodedExpiration = base64_decode((string) $request->expdt, true);
            $expdt = $encodedExpiration === false ? "" : str_replace("T", " ", $encodedExpiration);

            // Validate params: INIT
            if(!(intval($document) > 0) || !in_array($printType, ["a4", "mm80"]) || !Utilities::isDefined($expdt)) {

                return response()->view("errors.500", ["msg" => $message500], 500);

            }

            // Validate params: EXPIRATION
            try {

                $expirationDate = Carbon::parse($expdt)->startOfDay();
                $currentDate    = Carbon::now()->startOfDay();

            }catch(InvalidFormatException $e) {

                return response()->view("errors.500", ["msg" => $message500." (expdt)"], 500);

            }catch (Exception $e) {

                return response()->view("errors.500", ["msg" => $message500]." (expdt)", 500);

            }

            if($expirationDate->greaterThanOrEqualTo($currentDate)) {

                $saleHeader = SaleHeader::where("company_id", $this->getCompanyId())
                                        ->where("id", $document)
                                        ->when($this->allowedBranchIds() !== null, function($query) {

                                            $query->whereHas("serie", fn($serie) =>
                                                $serie->whereIn("branch_id", $this->allowedBranchIds())
                                            );

                                        })
                                        ->with(["serie.documentType", "holder", "allPositions"])
                                        ->first();

                $company = Company::find($this->getCompanyId());

                if(Utilities::isDefined($saleHeader) && Utilities::isDefined($company)) {

                    try {

                        $logotypeRoute = public_path("storage/".$company->logotype);
                        $logotypeImg   = "data:image/".pathinfo($logotypeRoute, PATHINFO_EXTENSION).";base64,".base64_encode(file_get_contents($logotypeRoute));

                    }catch(\Exception $e) {

                        $logotypeImg = null;

                    }

                    try {

                        $canceledPath = public_path("System/assets/img/utils/sales/canceled.png");
                        $canceledImg  = "data:image/".pathinfo($canceledPath, PATHINFO_EXTENSION).";base64,".base64_encode(file_get_contents($canceledPath));

                        $data = [
                            "saleHeader"  => $saleHeader,
                            "company"     => $company,
                            "extras"      => $saleHeader,
                            // Assets
                            "logotypeImg" => $logotypeImg,
                            "canceledImg" => $canceledImg
                        ];

                        if(in_array($printType, ["a4"])) {

                            // return view("System.pdf.sales.a4", $data);
                            $pdf = Pdf::loadView("System.pdf.sales.a4", $data);
                            return $pdf->stream($this->exportFileName("venta-{$saleHeader->serie_sequential}-a4", "pdf"), ["Attachment" => false]);
                            // return $pdf->download("Comprobante ".$saleHeader->serie_sequential.".pdf");

                        }else if(in_array($printType, ["mm80"])) {

                            // return view("System.pdf.sales.mm80", $data);
                            $pdf = Pdf::loadView("System.pdf.sales.mm80", $data)->setPaper([0, 0, 80 * 2.83, 160 * 2.83]);
                            return $pdf->stream($this->exportFileName("venta-{$saleHeader->serie_sequential}-80mm", "pdf"), ["Attachment" => false]);
                            // return $pdf->download("Comprobante ".$saleHeader->serie_sequential.".pdf");

                        }

                    }catch(\Exception $e) {

                        return response()->view("errors.500", ["msg" => $e->getMessage()], 500);

                    }

                }

            }else {

                return response()->view("errors.500", ["msg" => "El enlace ha caducado. Por favor, solicita uno nuevo."], 500);

            }

        }

        return response()->view("errors.404", ["msg" => $message404], 404);

    }

    public function customers(Request $request) {

        $query = Customer::where("company_id", $this->getCompanyId())
                             ->when(Utilities::isDefined($request->document_number), function($query) use($request) {

                                $filter = "%".trim($request->document_number)."%";

                                $query->where("document_number", "like", $filter);

                             })
                             ->when(Utilities::isDefined($request->name), function($query) use($request) {

                                $filter = "%".trim($request->name)."%";

                                $query->where("name", "like", $filter);

                             })
                             ->with(["identityDocumentType"]);

        $this->assertExportLimit($query);
        $customers = $query->get();

        $data = collect([]);

        foreach($customers as $customer) {

            $record = new stdClass;
            $record->documentType    = $customer->identityDocumentType->name;
            $record->document_number = $customer->document_number;
            $record->name            = $customer->name;
            $record->email           = $customer->email;
            $record->status          = $customer->formatted_status;

            $data->push($record);

        }

        return Excel::download(new CustomerExport($data), $this->exportFileName("clientes"));

    }

    public function users(Request $request) {

        $query = User::where("company_id", $this->getCompanyId())
                     ->when(Utilities::isDefined($request->document_number), function($query) use($request) {

                        $filter = "%".trim($request->document_number)."%";

                        $query->where("document_number", "like", $filter);

                     })
                     ->when(Utilities::isDefined($request->name), function($query) use($request) {

                        $filter = "%".trim($request->name)."%";

                        $query->where("name", "like", $filter);

                     })
                     ->with(["identityDocumentType"]);

        $this->assertExportLimit($query);
        $users = $query->get();

        $data = collect([]);

        foreach($users as $user) {

            $record = new stdClass;
            $record->documentType    = $user->identityDocumentType->name;
            $record->document_number = $user->document_number;
            $record->name            = $user->name;
            $record->email           = $user->email;
            $record->status          = $user->formatted_status;

            $data->push($record);

        }

        return Excel::download(new UserExport($data), $this->exportFileName("colaboradores"));

    }

    public function items(Request $request) {

        $query = Item::where("company_id", $this->getCompanyId())
                     ->when(Utilities::isDefined($request->name), function($query) use($request) {

                        $filter = "%".trim($request->name)."%";

                        $query->where("name", "like", $filter);

                     })
                     ->with(["currency"]);

        $this->assertExportLimit($query);
        $items = $query->get();

        $data = collect([]);

        foreach($items as $item) {

            $record = new stdClass;
            $record->name        = $item->name;
            $record->description = $item->description;
            $record->price       = $item->price;
            $record->currency    = $item->currency->plural_name;
            $record->status      = $item->formatted_status;

            $data->push($record);

        }

        return Excel::download(new ItemExport($data), $this->exportFileName("catalogo-comercial"));

    }

    public function branches(Request $request) {

        $query = Branch::where("company_id", $this->getCompanyId())
                          ->when($this->allowedBranchIds() !== null, fn($query) =>
                              $query->whereIn("id", $this->allowedBranchIds())
                          )
                          ->when(Utilities::isDefined($request->name), function($query) use($request) {

                            $filter = "%".trim($request->name)."%";

                            $query->where("name", "like", $filter);

                          });

        $this->assertExportLimit($query);
        $branches = $query->get();

        $data = collect([]);

        foreach($branches as $branch) {

            $record = new stdClass;
            $record->name   = $branch->name;
            $record->status = $branch->formatted_status;

            $data->push($record);

        }

        return Excel::download(new BranchExport($data), $this->exportFileName("sucursales"));

    }

    public function sales(Request $request) {

        $query = SaleHeader::where("company_id", $this->getCompanyId())
                                 ->when($this->allowedBranchIds() !== null, fn($query) =>
                                     $query->whereHas("serie", fn($serie) =>
                                         $serie->whereIn("branch_id", $this->allowedBranchIds())
                                     )
                                 )
                                 ->when(Utilities::isDefined($request->type), function($query) use($request) {

                                    if(in_array($request->type, ["by_month"])) {

                                        if(Utilities::isDefined($request->start_month)) {

                                            list($year, $month) = explode("-", $request->start_month);

                                            $query->whereYear("issue_date", $year)
                                                  ->whereMonth("issue_date", $month);

                                        }

                                    }else if(in_array($request->type, ["range_months"])) {

                                        if(Utilities::isDefined($request->start_date) && Utilities::isDefined($request->end_date)) {

                                            $start = Carbon::createFromFormat("Y-m", $request->start_date)->startOfMonth();
                                            $end = Carbon::createFromFormat("Y-m", $request->end_date)->endOfMonth();
                                            $query->whereBetween("issue_date", [$start->toDateString(), $end->toDateString()]);

                                        }

                                    }else if(in_array($request->type, ["by_date"])) {

                                        if(Utilities::isDefined($request->start_date)) {

                                            $query->where("issue_date", $request->start_date);

                                        }

                                    }else if(in_array($request->type, ["range_dates"])) {

                                        if(Utilities::isDefined($request->start_date) && Utilities::isDefined($request->end_date)) {

                                            $query->where("issue_date", ">=", $request->start_date)
                                                  ->where("issue_date", "<=", $request->end_date);

                                        }

                                    }

                                 })
                                 ->with(["holder", "currency"]);

        $this->assertExportLimit($query);
        $salesHeader = $query->get();

        $data = collect([]);

        foreach($salesHeader as $saleHeader) {

            $record = new stdClass;
            $record->serie_sequential     = $saleHeader->serie_sequential;
            $record->holder               = $saleHeader->holder->name;
            $record->formatted_issue_date = $saleHeader->formatted_issue_date;
            $record->total                = $saleHeader->total;
            $record->currency             = $saleHeader->currency->plural_name;
            $record->status               = $saleHeader->formatted_status;

            $data->push($record);

        }

        return Excel::download(new SaleExport($data), $this->exportFileName("ventas"));

    }

    public function settlements(Request $request) {

        $validated = $request->validate([
            "type" => "required|in:taxes,payments",
            "scope" => "nullable|in:sale,purchase,both",
            "date_from" => "nullable|date",
            "date_to" => "nullable|date|after_or_equal:date_from"
        ]);

        return response()->json([
            "bool" => true,
            "data" => FinancialSettlementReportService::summarize(
                $this->getCompanyId(),
                $validated["type"],
                $validated["scope"] ?? "both",
                $validated["date_from"] ?? null,
                $validated["date_to"] ?? null
            )
        ]);

    }

    /**
     * Get translation namespace for report module
     *
     * @return string
     */
    protected function getTranslationNamespace(): string {

        return self::TRANSLATION_NAMESPACE;

    }

}
