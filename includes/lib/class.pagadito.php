<?php

class Pagadito
{

    // <editor-fold defaultstate="collapsed" desc="Atributos">
    //**************************************************************************
    private $debug_mode;
    private $url;
    private $authToken;
    protected $curlObj;
    //**************************************************************************
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Funciones Públicas">
    //**************************************************************************
    /**
     * Es el constructor de la clase.
     * @param   bool $debug_mode Define si operar en modo debug.
     */
    public function __construct($debug_mode = false)
    {
        $this->curlObj = curl_init();
        $this->debug_mode = $debug_mode;
        $this->config();

        $token = $this->checkAndGetToken();
        $this->authToken = $token;
    }

    public function createCustomer($params)
    {
        return $this->callToAPI('customer', $params, "POST");
    }

    public function validateProcessCard($params)
    {
        return $this->callToAPI('3ds/payment-validation', $params, "POST");
    }

    public function validateProcessByToken($params)
    {
        return $this->callToAPI('validate-process-by-token', $params, "POST");
    }

    public function sendPayment($params)
    {

        return $this->callToAPI('payment', $params, "POST");
    }


    public function createSubscription($params)
    {

        return $this->callToAPI('subscription', $params, "POST");
    }

    public function sendSubscriptionToken($params)
    {

        return $this->callToAPI('subscription-by-token', $params, "POST");
    }

    public function cancelSubscription($params)
    {

        return $this->callToAPI('subscription-cancel', $params, "POST");
    }

    public function sendRefund($params)
    {

        return $this->callToAPI('refund', $params, "POST");
    }
    // --
    public function setupPayer($params)
    {
        return $this->callToAPI('3ds/setup-payer', $params, "POST");
    }

    public function setCustomer($params)
    {
        return $this->callToAPI('3ds/customer', $params, "POST");
    }

    public function setupPayerByToken($params)
    {
        return $this->callToAPI('setup-payer-by-token', $params, "POST");
    }


    //**************************************************************************
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Funciones Privadas">
    //**************************************************************************
    private function config()
    {
        $this->url = GATEWAY_URL;
    }

    private function getToken()
    {
        $url = str_replace("v1/", "", $this->url);
        $url = rtrim($url, '/') . '/token';
        $params = [
            "client_id" => CLIENT_ID,
            "client_secret" => CLIENT_SECRET,
        ];

        $request = json_encode($params);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json;charset=UTF-8"));
        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $curl_response = curl_exec($ch);
        $info = curl_getinfo($ch);

        if (curl_errno($ch)) {
            throw new Exception('Error obtaining token: ' . curl_error($ch));
        }

        if ($info["http_code"] == 200) {
            $response = json_decode($curl_response, true);
            if (isset($response['token']) && isset($response['expires'])) {
                file_put_contents('token.txt', $response['token']);
                file_put_contents('token_expiration.txt', $response['expires']);
                return $response['token'];
            } else {
                throw new Exception('Invalid token response');
            }
        } else {
            throw new Exception('Failed to obtain token, HTTP code: ' . $info["http_code"] . ' => ' . $url);
        }
    }


    private function checkAndGetToken()
    {
        if (file_exists('token.txt') && file_exists('token_expiration.txt')) {
            $token = file_get_contents('token.txt');
            $expires = file_get_contents('token_expiration.txt');
            if (new DateTime($expires) > new DateTime()) {
                return $token;
            }
        }
        $token = $this->getToken();
        $this->curlObj = curl_init();
        return $token;
    }

    private function parseRequest($formData)
    {
        $request = "";
        if (count($formData) == 0) {
            return "";
        }
        //$formData = $this->removeEmptyValues($formData);
        $request = json_encode($formData);
        return $request;
    }

    public function removeEmptyValues($array)
    {
        foreach ($array as $i => $value) {
            if (is_array($array[$i])) {
                if (count($array[$i]) == 0) {
                    unset($array[$i]);
                } else {
                    $array[$i] = $this->removeEmptyValues($array[$i]);
                    if (count($array[$i]) == 0) {
                        unset($array[$i]);
                    }
                }
            } else {
                if ($array[$i] == "") {
                    unset($array[$i]);
                }
            }
        }
        return $array;
    }

    private function callToAPI($resource, $params, $method)
    {
        $response = array(
            'pagadito_http_code' => '',
            'pagadito_response' => '',
        );

        $request = $this->parseRequest($params);
        curl_setopt($this->curlObj, CURLOPT_URL, ($this->url . $resource));
        curl_setopt($this->curlObj, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curlObj, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer " . $this->authToken,
            "Content-Type: Application/json;charset=UTF-8",
            "Content-Length: " . strlen($request)
        ));

        if ($method == "GET") {
            curl_setopt($this->curlObj, CURLOPT_HTTPGET, 1);
        } elseif ($method == "PUT") {
            curl_setopt($this->curlObj, CURLOPT_CUSTOMREQUEST, "PUT");
        } elseif ($method == "POST") {
            curl_setopt($this->curlObj, CURLOPT_POST, 1);
            curl_setopt($this->curlObj, CURLOPT_POSTFIELDS, $request);
        }

        if ($this->debug_mode) {
            curl_setopt($this->curlObj, CURLINFO_HEADER_OUT, true);
        }

        $curl_response = curl_exec($this->curlObj);

        $info = curl_getinfo($this->curlObj);

        if (array_key_exists("http_code", $info)) {
            $response['pagadito_http_code'] = $info["http_code"];
        }

        if ($this->debug_mode) {
            if (array_key_exists("request_header", $info)) {
                $response['curl_request_header'] = $info["request_header"];
            }

            if (array_key_exists("content_type", $info)) {
                $response['pagadito_content_type'] = $info["content_type"];
            }

            $response['param_request'] = $request;
            $response['curl_response'] = $curl_response;
        }

        if (curl_error($this->curlObj)) {
            $response['curl_error'] = curl_errno($this->curlObj) . " - " . curl_error($this->curlObj);
        }

        curl_close($this->curlObj);

        if (!empty($curl_response)) {
            $response['pagadito_response'] = json_decode($curl_response, true);
        }
        return $response;
    }


    //**************************************************************************
    // </editor-fold>
}
