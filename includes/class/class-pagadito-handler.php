<?php

require_once __DIR__ . '/../lib/class.pagadito.php';

class PagaditoHandler
{
  private $testMode;
  private $client_id;
  private $client_secret;
  private $isAdmin;

  public function __construct($isAdmin, $testMode = false)
  {
    $this->isAdmin = $isAdmin;
    $this->testMode = $testMode ? 'yes' : 'no';
    $this->initializeKeys();
  }

  private function initializeKeys()
  {
    if ($this->testMode === 'yes') {
      define("GATEWAY_URL", "https://sandbox-hub.pagadito.com/api/v1/");
      $this->client_id = 'd19e9090-e1d6-4466-9d53-c582afe2bdee';
      $this->client_secret = 'ZGRhNmQ5YjYtOGM5YS00MThiLTgxYzgtOGNmNzQ2YTExZTFm';
    } else {
      define("GATEWAY_URL", "https://sandbox-hub.pagadito.com/api/v1/");
      $this->client_id = '663c2773-a145-4b84-8007-8ff273beec1a';
      $this->client_secret = 'OWU4NzFkNjQtNjdkMC00N2Y2LTgyOGQtZTI5ZjI1MDg2MDIy';
    }
    define("CLIENT_ID", $this->client_id);
    define("CLIENT_SECRET", $this->client_secret);
  }

  public function processTransaction($data)
  {
    $params = $this->prepareTransactionParams($data);
    $Pagadito = new Pagadito();
    return $Pagadito->createCustomer($params);
  }

  public function validateProcessCard($data)
  {
    $params = $this->prepareTransactionParams($data);
    $Pagadito = new Pagadito();
    return $Pagadito->validateProcessCard($params);
  }

  private function prepareTransactionParams($data)
  {
    $cardNumber = $data['cardNumber'];
    $expiryMonth = $data['expiryMonth'];
    $expiryYear = $data['expiryYear'];
    $cvv = $data['cvv'];
    $holderName = $data['holderName'];
    $firstName = $data['firstName'];
    $lastName = $data['lastName'];
    $email = $data['email'];
    $phone = $data['phone'];
    $address_1 = $data['address'];
    $address_2 = "";
    $city = $data['city'];
    $state = $data['state'];
    $country = $data['country'];
    $postalCode = $data['postalCode'];
    $request_id = $data['request_id'];
    $referenceId = $data['referenceId'];
    $returnUrl = $data['returnUrl'];
    $transactionId = $data['TransactionId'];

    $transaction = array(
      'merchantTransactionId' => $data['mechantReferenceId'],
      'currencyId' => $data['currency'],
      'transactionDetails' => array(
        array(
          'quantity' => '1',
          'description' => 'Recarga',
          'amount' => $data['amount'],
        ),
      ),
    );

    return array(
      'card' => array(
        'number' => $cardNumber,
        'expirationDate' => $expiryMonth . '/' . $expiryYear,
        'cvv' => $cvv,
        'cardHolderName' => $holderName,
        'firstName' => $firstName,
        'lastName' => $lastName,
        'billingAddress' => array(
          'city' => $city,
          'state' => $state,
          'zip' => $postalCode,
          'countryId' => $country,
          'line1' => $address_1,
          'line2' => $address_2,
          'phone' => $phone,
        ),
        'email' => $email,
      ),
      'transaction' => $transaction,
      'browserInfo' => array(
        'deviceFingerprintID' => time(),
        'customerIp' => $data['ip'],
      ),
      'consumerAuthenticationInformation' => array(
        'setup_request_id'  => $request_id,
        'referenceId'       => $referenceId,
        "returnUrl"         => $returnUrl,
        "transactionId"     => $transactionId ? $transactionId : null
      )
    );
  }

  public function handleWooCommerce($data)
  {
    if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
      echo 'WooCommerce no está activo. Asegúrate de activarlo para proceder.';
      return;
    }

    $order = wc_create_order();
    $order->set_created_via('store-api');
    //LOCAL 62
    //PRODUCTION 269
    $product = new WC_Product_Variable(269);
    $product->set_regular_price((float)$data['amount']);
    $product->set_price((float)$data['amount']);
    $product->save();
    $order->add_product($product, 1);

    $this->setOrderBilling($order, $data);
    $this->setOrderShipping($order, $data);

    $order->set_status('wc-completed', 'Order is created programmatically');
    $order->add_meta_data('request_id', $data['request_id']);
    $order->add_meta_data('authorization', $data['authorization']);
    $order->set_payment_method('er_pagadito');
    $order->payment_complete();
    $order->calculate_totals();
    $order->save();
  }

  public function handleSaveData($res, $token, $customerReply = true)
  {
    global $wpdb;

    if ($res['pagadito_http_code'] === 200) {
      if ($customerReply) {
        $update_data = array(
          'http_code' => 200,
          'response_code' => isset($res['pagadito_response']['data']) ? $res['pagadito_response']['data']['response_code'] : $res['pagadito_response']['response_code'],
          'response_message' => isset($res['pagadito_response']['data']) ? $res['pagadito_response']['data']['response_message'] : $res['pagadito_response']['response_message'],
          'request_date' => isset($res['pagadito_response']['data']) ? $res['pagadito_response']['data']['request_date'] : $res['pagadito_response']['request_date'],
          'paymentDate' => isset($res['pagadito_response']['customer_reply']) ? $res['pagadito_response']['customer_reply']['paymentDate'] : (isset($res['pagadito_response']['data']) ? $res['pagadito_response']['data']['paymentDate'] : $res['pagadito_response']['paymentDate']),
          'authorization' => isset($res['pagadito_response']['customer_reply']) ? $res['pagadito_response']['customer_reply']['authorization'] : (isset($res['pagadito_response']['data']) ? $res['pagadito_response']['data']['authorization'] : $res['pagadito_response']['authorization'])
        );
        $updateSymbol = ['%d', '%s', '%s', '%s', '%s', '%s'];
      } else {
        $update_data = array(
          'http_code' => 200,
          'response_code' => isset($res['pagadito_response']['data']) ? $res['pagadito_response']['data']['response_code'] : $res['pagadito_response']['response_code'],
          'response_message' => isset($res['pagadito_response']['data']) ? $res['pagadito_response']['data']['response_message'] : $res['pagadito_response']['response_message'],
          'request_date' => isset($res['pagadito_response']['data']) ? $res['pagadito_response']['data']['request_date'] : $res['pagadito_response']['request_date'],
          'paymentDate' => isset($res['pagadito_response']['data']) ? $res['pagadito_response']['data']['request_date'] : $res['pagadito_response']['request_date']
        );
        $updateSymbol = ['%d', '%s', '%s', '%s', '%s'];
      }
    } else {
      $update_data = array(
        'http_code' => $res['pagadito_http_code'],
        'response_code' => isset($res['pagadito_response']['data']) ? $res['pagadito_response']['data']['response_code'] : $res['pagadito_response']['response_code'],
        'response_message' => isset($res['pagadito_response']['data']) ? $res['pagadito_response']['data']['response_message'] : $res['pagadito_response']['response_message'],
        'request_date' => isset($res['pagadito_response']['data']) ? $res['pagadito_response']['data']['request_date'] : $res['pagadito_response']['request_date']
      );
      $updateSymbol = ['%d', '%s', '%s', '%s'];
    }

    $where = array(
      'token' => $token
    );

    $wpdb->update(
      $wpdb->prefix . "er_pagadito_operations",
      $update_data,
      $where,
      $updateSymbol,
      array('%s')
    );

    $query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}er_pagadito_operations WHERE token = %s", $token);
    $updated_record = $wpdb->get_row($query, ARRAY_A);
    if (!$this->isAdmin) {
      $this->handleWooCommerce($updated_record);
    }
  }

  private function setOrderBilling($order, $data)
  {
    $order->set_billing_first_name($data['firstName']);
    $order->set_billing_last_name($data['lastName']);
    $order->set_billing_email($data['email']);
    $order->set_billing_phone($data['phone']);
    $order->set_billing_address_1($data['address']);
    $order->set_billing_address_2('');
    $order->set_billing_city($data['city']);
    $order->set_billing_postcode($data['postalCode']);
    $order->set_billing_country($data['cod_country']);
  }

  private function setOrderShipping($order, $data)
  {
    $order->set_shipping_first_name($data['firstName']);
    $order->set_shipping_last_name($data['lastName']);
    $order->set_shipping_address_1($data['address']);
    $order->set_shipping_address_2('');
    $order->set_shipping_city($data['city']);
    $order->set_shipping_postcode($data['postalCode']);
    $order->set_shipping_country($data['cod_country']);
  }

  public function setupPayer($params, $client_id, $ip)
  {
    global $wpdb;
    $Pagadito = new Pagadito();
    $res = $Pagadito->setupPayer($params);
    $environment = $this->testMode === 'yes' ? 'sandbox' : 'production';

    if ($res['pagadito_http_code'] == 200) {
      $order_data = [
        'client_id' => $client_id,
        'amount' => $params['transaction']['transactionDetails'][0]['amount'],
        'currency' => $params['transaction']['currencyId'],
        'merchantReferenceId' => $params['transaction']['merchantTransactionId'],
        'firstName' => $params['card']['firstName'],
        'lastName' => $params['card']['lastName'],
        'ip' => $ip,
        'cod_country' => $params['card']['billingAddress']['countryId'],
        'environment' => $environment,
        'token' => isset($res['pagadito_response']['data']) ? $res['pagadito_response']['data']['token'] : $res['pagadito_response']['token'],
        'request_id' => isset($res['pagadito_response']['data']) ? $res['pagadito_response']['data']['request_id'] : $res['pagadito_response']['request_id'],
        'referenceId' => isset($res['pagadito_response']['data']) ? $res['pagadito_response']['data']['referenceId'] : $res['pagadito_response']['referenceId'],
        'email' => $params['card']['email'],
        'origin' => $this->isAdmin ? 'web' : 'api',
        'phone' => $params['card']['billingAddress']['phone'],
        'address' => $params['card']['billingAddress']['line1'],
        'city' => $params['card']['billingAddress']['city'],
        'postalCode' => $params['card']['billingAddress']['zip']
      ];
      $wpdb->insert($wpdb->prefix . "er_pagadito_operations", $order_data);
    }

    return $res;
  }

  public function setCustomer($params)
  {
    $Pagadito = new Pagadito();
    $res = $Pagadito->setCustomer($params);
    $this->handleSaveData($res, $params['token'], false);

    return $res;
  }

  public function setValidateCard($params)
  {
    $Pagadito = new Pagadito();
    $res = $Pagadito->validateProcessCard($params);
    $this->handleSaveData($res, $params['token']);

    return $res;
  }
}
