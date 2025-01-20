<?php

require_once __DIR__ . '/../lib/class.pagadito.php';
require_once __DIR__ . '/class-clients.php';

class PagaditoHandler
{
  private $testMode;
  private $client;
  private $client_id;
  private $client_secret;
  private $pagadito_token;
  private $token_expiration;
  private $isAdmin;
  private $Pagadito;

  public function __construct($client, $testMode = false)
  {
    $this->client = $client;
    $this->testMode = $testMode ? 'yes' : 'no';
    $this->initializeKeys();
  }

  private function initializeKeys()
  {
    $this->isAdmin = $this->client->role == 'admin' ? true : false;
    $this->client_id = $this->client->client_id;
    $this->client_secret = $this->client->client_secret;
    $this->pagadito_token = $this->client->pagadito_token;
    $this->token_expiration = $this->client->token_expiration;

    if ($this->testMode === 'yes') {
      define("GATEWAY_URL", "https://sandbox-hub.pagadito.com/api/v1/");
    } else {
      define("GATEWAY_URL", "https://hub.pagadito.com");
    }
    define("CLIENT_ID", $this->client_id);
    define("CLIENT_SECRET", $this->client_secret);
    define("CLIENT_TOKEN", $this->pagadito_token);
    define("CLIENT_TOKEN_EXPIRATION", $this->token_expiration);

    $this->Pagadito = new Pagadito();

    if ($this->Pagadito->getAuthToken() != $this->pagadito_token) {
      $clientUpdate = new Clients();
      $data = [
        'pagadito_token' => $this->Pagadito->getAuthToken(),
        'token_expiration' => $this->Pagadito->getExpiresToken()
      ];
      $clientUpdate->setClientToken($this->client->ID, $data);
    }
  }

  public function handleWooCommerce($data)
  {
    if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
      echo 'WooCommerce no está activo. Asegúrate de activarlo para proceder.';
      return;
    }

    $order = wc_create_order();
    $order->set_created_via('store-api');
    $local_product_id = 284;
    $production_product_id = 269;
    $is_local = (get_site_url() == 'http://testwoocommerce.local');
    $product_id = $is_local ? $local_product_id : $production_product_id;

    $product = new WC_Product_Variable($product_id);
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
    $res = $this->Pagadito->setupPayer($params);
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
    $res = $this->Pagadito->setCustomer($params);
    $this->handleSaveData($res, $params['token']);

    return $res;
  }

  public function setValidateCard($params)
  {
    $res = $this->Pagadito->validateProcessCard($params);
    $this->handleSaveData($res, $params['token']);

    return $res;
  }
}
