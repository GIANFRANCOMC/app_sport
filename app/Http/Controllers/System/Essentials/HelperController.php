<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Essentials;

use App\Helpers\System\{ApiResponse, Utilities};
use App\Http\Controllers\Controller;
use App\Http\Controllers\System\Concerns\HandlesApiResponses;
use App\Mail\SaleMail;
use Exception;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{Auth, DB, Mail};
use stdClass;

class HelperController extends Controller {

    use HandlesApiResponses;

    /**
     * Translation namespace for helper module
     */
    private const TRANSLATION_NAMESPACE = "System.Essentials.helper";

    public function searchDocumentNumber(Request $request) {

        $user    = Auth::user();
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

        return ApiResponse::success($data, $msg);

    }

    public function sendEmail(Request $request) {

        $bool   = false;
        $msg    = "No ha sido posible enviar el correo.";
        $devMsg = "";

        try {

            $id               = $request->id;
            $serie_sequential = $request->serie_sequential;
            $email            = $request->email;
            $message          = $request->message;

            if(Utilities::isDefined($email) && Utilities::isDefined($message)) {

                $mail = new stdClass();
                $mail->subject = "Venta creada en ".config("APP_NAME");
                $mail->message = $message;

                Mail::to($email)->send(new SaleMail($mail));

                $bool = true;
                $msg  = "Correo enviado con éxito.";

            }

        }catch(Exception $e) {

            $devMsg = $e->getMessage();

        }

        if($bool) {

            return ApiResponse::success(null, $msg);

        }

        return ApiResponse::error($msg, 500);

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
