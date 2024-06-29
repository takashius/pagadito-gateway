<?php

class Pagadito {
    
    // <editor-fold defaultstate="collapsed" desc="Atributos">
    //**************************************************************************
    private $debug_mode;
    private $key_uid;
    private $key_wsk;
    private $url;
    protected $curlObj;
    //**************************************************************************
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Funciones Públicas">
    //**************************************************************************
    /**
     * Es el constructor de la clase.
     * @param   bool $debug_mode Define si operar en modo debug.
     */
    public function __construct($debug_mode = false) {
        $this->curlObj = curl_init();
        $this->debug_mode = $debug_mode;
        $this->config();
    }
    
    public function createCustomer($params)
    {
        
        return $this->callToAPI('customer', $params, "POST");
    }

    public function sendPayment($params) {
        
        return $this->callToAPI('payment', $params, "POST");
    }

    public function createSubscription($params) {
        
        return $this->callToAPI('subscription', $params, "POST");
    }

    public function sendSubscriptionToken($params) {
        
        return $this->callToAPI('subscription-by-token', $params, "POST");
    }

    public function cancelSubscription($params) {
        
        return $this->callToAPI('subscription-cancel', $params, "POST");
    }

    public function sendRefund($params) {
        
        return $this->callToAPI('refund', $params, "POST");
    }

    
    //**************************************************************************
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Funciones Privadas">
    //**************************************************************************
    private function config()
    {
        
        $this->key_uid = KEY_UID;
        $this->key_wsk = KEY_WSK;
        $this->url = GATEWAY_URL;
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
        foreach ($array as $i => $value ) {
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
        curl_setopt($this->curlObj, CURLOPT_USERPWD, $this->key_uid . ":" . $this->key_wsk);
        curl_setopt($this->curlObj, CURLOPT_RETURNTRANSFER, true);

        if ($method == "GET") {
            curl_setopt($this->curlObj, CURLOPT_HTTPGET, 1);
        } elseif ($method == "PUT") {
            curl_setopt($this->curlObj, CURLOPT_CUSTOMREQUEST, "PUT");
        } elseif ($method == "POST") {
            curl_setopt($this->curlObj, CURLOPT_POST, 1);
            curl_setopt($this->curlObj, CURLOPT_POSTFIELDS, $request);
            curl_setopt($this->curlObj, CURLOPT_HTTPHEADER, array("Content-Length: " . strlen($request)));
            curl_setopt($this->curlObj, CURLOPT_HTTPHEADER, array("Content-Type: Application/json;charset=UTF-8"));
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
