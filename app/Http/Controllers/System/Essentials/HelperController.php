<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Essentials;

use App\Helpers\System\{Utilities};
use App\Http\Controllers\System\Base\BaseController;
use App\Mail\SaleMail;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{Mail};
use stdClass;

class HelperController extends BaseController {

    /**
     * Translation namespace for helper module
     */
    private const TRANSLATION_NAMESPACE = "System.Essentials.helper";

    /**
     * Search document number in external API
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function searchDocumentNumber(Request $request): JsonResponse {

        $user    = $this->getAuthUser();
        $company = $user->company;

        $bool = false;
        $msg  = "Datos incompletos.";
        $data = [];

        if(Utilities::isDefined($company->token_api_misc)) {

            if(Utilities::isDefined($request->document_number) && preg_match('/^\d{8,11}$/', $request->document_number) && Utilities::isDefined($request->type)) {

                $type   = "";
                $params = [];

                switch($request->type) {
                    case "dni":
                        $type = "dni";
                        $params = json_encode(["dni" => $request->document_number]);
                        break;

                    case "ruc":
                        $type = "ruc";
                        $params = json_encode(["ruc" => $request->document_number]);
                        break;
                }

                $curlApi = curl_init();

                curl_setopt_array($curlApi, array(
                    CURLOPT_URL => "https://apiperu.dev/api/$type",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_POSTFIELDS => $params,
                    CURLOPT_HTTPHEADER => [
                        "Accept: application/json",
                        "Content-Type: application/json",
                        "Authorization: Bearer ".$company->token_api_misc
                    ],
                ));

                $responseApi = curl_exec($curlApi);
                $errApi = curl_error($curlApi);
                curl_close($curlApi);

                $bool = false;
                $msg  = $errApi;
                $data = [];

                if(!Utilities::isDefined($errApi)) {

                    $dataApi = json_decode($responseApi);

                    $bool = $dataApi->success;
                    $msg  = $bool ? "La búsqueda se ha efectuado correctamente." : $dataApi->message;
                    $data = [];

                    if($bool) {

                        switch($request->type) {
                            case "dni":
                                $data = [
                                    "document_number"   => $dataApi->data->numero,
                                    "first_name"        => $dataApi->data->nombres,
                                    "last_name"         => $dataApi->data->apellido_paterno,
                                    "second_last_name"  => $dataApi->data->apellido_materno,
                                    "verification_code" => $dataApi->data->codigo_verificacion
                                ];
                                break;

                            case "ruc":
                                $data = [
                                    "document_number" => $dataApi->data->ruc,
                                    "legal_name"      => $dataApi->data->nombre_o_razon_social,
                                    "commercial_name" => $dataApi->data->nombre_o_razon_social,
                                    "address"         => $dataApi->data->direccion_completa,
                                ];
                                break;
                        }

                    }

                }

            }

        }else {

            $bool = false;
            $msg  = "Debe de ingresar el Token API - Misc.";

        }

        return $this->successResponse($data, $bool ? "search_success" : "search_failed");

    }

    /**
     * Send email notification
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendEmail(Request $request): JsonResponse {

        try {

            $email   = $request->email;
            $message = $request->message;

            if(!Utilities::isDefined($email) || !Utilities::isDefined($message)) {

                return $this->errorResponse("invalid_data", [], 422);

            }

            $mail = new stdClass();
            $mail->subject = "Venta creada en ".config("APP_NAME");
            $mail->message = $message;

            Mail::to($email)->send(new SaleMail($mail));

            return $this->successResponse(null, "email_sent");

        }catch(\Exception $e) {

            return $this->handleException($e, "send_email");

        }

    }

    /**
     * Get translation namespace for helper module
     *
     * @return string
     */
    protected function getTranslationNamespace(): string {

        return self::TRANSLATION_NAMESPACE;

    }

}
