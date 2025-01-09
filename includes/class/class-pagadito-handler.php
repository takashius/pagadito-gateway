<?php

require_once __DIR__ . '/../lib/class.pagadito.php';

class PagaditoHandler
{
  private $testmode;
  private $client_id;
  private $client_secret;

  public function __construct($testmode = false)
  {
    $this->testmode = $testmode ? 'yes' : 'no';
    $this->initializeKeys();
  }

  private function initializeKeys()
  {
    if ($this->testmode === 'yes') {
      define("GATEWAY_URL", "https://sandbox-hub.pagadito.com/api/v1/");
      $this->client_id = '663c2773-a145-4b84-8007-8ff273beec1a';
      $this->client_secret = 'OWU4NzFkNjQtNjdkMC00N2Y2LTgyOGQtZTI5ZjI1MDg2MDIy';
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
    $Pagadito = new Pagadito(false);
    return $Pagadito->createCustomer($params);
  }

  public function validateProcessCard($data)
  {
    $params = $this->prepareTransactionParams($data);
    $Pagadito = new Pagadito(false);
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

  public function handleWooCommerce($data, $validate = false)
  {
    if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
      echo 'WooCommerce no está activo. Asegúrate de activarlo para proceder.';
      return;
    }

    global $wpdb;
    if ($validate) {
      $res = $this->validateProcessCard($data);
    } else {
      $res = $this->processTransaction($data);
    }
    $environment = $this->testmode === 'yes' ? 'sandbox' : 'production';

    $order_data = [
      'client_id' => $data['client_id'],
      'amount' => (float)$data['amount'],
      'currency' => $data['currency'],
      'merchantReferenceId' => $data['mechantReferenceId'],
      'firstName' => $data['firstName'],
      'lastName' => $data['lastName'],
      'ip' => $data['ip'],
      'cod_country' => $data['country'],
      'environment' => $environment,
      'http_code' => $res['pagadito_http_code']
    ];

    if ($res['pagadito_http_code'] === 200) {
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
      $order->add_meta_data('request_id', $res['pagadito_response']['request_id']);
      $order->add_meta_data('authorization', $res['pagadito_response']['customer_reply']['authorization']);
      $order->set_payment_method('er_pagadito');
      $order->payment_complete();
      $order->calculate_totals();
      $order->save();

      $order_data['authorization'] = $res['pagadito_response']['customer_reply']['authorization'];
      $order_data['paymentDate'] = $res['pagadito_response']['customer_reply']['paymentDate'];
    }

    if ($res['pagadito_response']) {
      $order_data['response_code'] = $res['pagadito_response']['response_code'];
      $order_data['response_message'] = $res['pagadito_response']['response_message'];
      $order_data['request_date'] = $res['pagadito_response']['request_date'];
    }

    $wpdb->insert($wpdb->prefix . "er_pagadito_operations", $order_data);

    return $res;
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
    $order->set_billing_country($data['country']);
  }

  private function setOrderShipping($order, $data)
  {
    $order->set_shipping_first_name($data['firstName']);
    $order->set_shipping_last_name($data['lastName']);
    $order->set_shipping_address_1($data['address']);
    $order->set_shipping_address_2('');
    $order->set_shipping_city($data['city']);
    $order->set_shipping_postcode($data['postalCode']);
    $order->set_shipping_country($data['country']);
  }

  public function setupPayer($params)
  {
    $Pagadito = new Pagadito(false);
    $res = $Pagadito->setupPayer($params);

    return $res;
  }
}
