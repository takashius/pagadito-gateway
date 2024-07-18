<?php

/**
 * Additional elements of mail sending
 *
 * @link       https://erdesarrollo.com.ve
 * @since      1.0.1
 *
 * @package    ErPagadito_gateway
 * @subpackage ErPagadito_gateway/includes
 */


add_action('rest_api_init', function () {
  register_rest_route('pagadito/v1', '/transactions', array(
    'methods' => 'GET',
    'callback' => 'get_transactions',
  ));
});

add_action('rest_api_init', function () {
  register_rest_route('pagadito/v1', '/cobro', array(
    'methods' => 'POST',
    'callback' => 'save_product',
  ));
});

function get_transactions($data)
{
  global $wpdb;
  $tablaOperations = $wpdb->prefix . "er_pagadito_operations";
  $where = "";

  if ($data['date_to'] && $data['date_from']) {
    $date_to = $data['date_to'];
    $date_from = $data['date_from'];
    $where .= " `date` >= '" . $date_to . "' AND `date` <= '" . $date_from . "'";
  }

  if ($data['origin']) {
    $origin = $data['origin'];
    if ($where !== '') $where .= " AND";
    $where .= " `origin` = '" . $origin . "'";
  }

  if ($data['http_code']) {
    $http_code = $data['http_code'];
    if ($where !== '') $where .= " AND";
    $where .= " `http_code` = '" . $http_code . "'";
  }

  $sql = "SELECT * FROM `" . $tablaOperations . "`";
  if ($where !== '') {
    $sql .= " WHERE " . $where;
  }
  $query = $wpdb->prepare($sql);
  $result = $wpdb->get_results($query);
  return $result;
}


function save_product($data)
{

  if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    global $wpdb;
    $amount = $data['amount'];
    $ip = $data['ip'];
    $merchantTransactionId = $data['merchantReferenceId'];
    $currency = $data['currency'];
    $firstName = $data['firstName'];
    $lastName = $data['lastName'];
    $holderName = $data['holderName'];
    $email = $data['email'];
    $phone = $data['phone'];
    $address_1 = $data['address'];
    $address_2 = "";
    $city = $data['city'];
    $state = $data['state'];
    $country = $data['country'];
    $postalCode = $data['postalCode'];

    $cardNumber = $data['cardNumber'];
    $cvv = $data['cvv'];
    $expiryMonth = $data['expiryMonth'];
    $expiryYear = $data['expiryYear'];

    $transaction = array(
      'merchantTransactionId' => $merchantTransactionId,
      'currencyId' => $currency,
      /* transactionDetails Object */
      'transactionDetails' => array(
        array(
          'quantity' => '1',
          'description' => 'Recarga',
          'amount' => $amount,
        ),
      ),
    );

    $validateData = validateRequest($data);
    if (count($validateData) > 0) {
      return array("pagadito_http_code" => 400, "pagadito_response" => $validateData);
    }

    require_once __DIR__ . '/pagadito-call.php';
    if ($res['pagadito_http_code'] === 200) {
      $order = wc_create_order();
      $order->set_created_via('store-api');
      //57 -> LOCAL
      //269 -> PROD
      $product = new WC_Product_Variable(57);
      $product->set_regular_price((float)$amount);
      $product->set_price((float)$amount);
      $product->save();

      $quantity = 1;
      $order->add_product($product, $quantity);

      $order->set_billing_first_name($firstName);
      $order->set_billing_last_name($lastName);
      $order->set_billing_email($email);
      $order->set_billing_phone($phone);
      $order->set_billing_address_1($address_1);
      $order->set_billing_address_2('');
      $order->set_billing_city($city);
      $order->set_billing_postcode($postalCode);
      $order->set_billing_country($country);
      // Si la dirección de envío es diferente a la de facturación, establece la dirección de envío aquí
      $order->set_shipping_first_name($firstName);
      $order->set_shipping_last_name($lastName);
      $order->set_shipping_address_1($address_1);
      $order->set_shipping_address_2('');
      $order->set_shipping_city($city);
      $order->set_shipping_postcode($postalCode);
      $order->set_shipping_country($country);

      $order->set_status('wc-completed', 'Order is created programmatically');
      $order->add_meta_data('request_id', $res['pagadito_response']['request_id']);
      $order->add_meta_data('authorization', $res['pagadito_response']['customer_reply']['authorization']);
      $order->set_payment_method('er_pagadito');
      $order->payment_complete();
      $order->calculate_totals();
      $order->save();

      $array = array(
        "amount" => (float)$amount,
        "currency" => $currency,
        "merchantReferenceId" => $merchantTransactionId,
        "firstName" => $firstName,
        "lastName" => $lastName,
        "ip" => $ip,
        "authorization" => $res['pagadito_response']['customer_reply']['authorization'],
        "http_code" => $res['pagadito_http_code'],
        "response_code" => $res['pagadito_response']['response_code'],
        "response_message" => $res['pagadito_response']['response_message'],
        "request_date" => $res['pagadito_response']['request_date'],
        "paymentDate" => $res['pagadito_response']['customer_reply']['paymentDate'],
        "origin" => "api"
      );
      $wpdb->insert($wpdb->prefix . "er_pagadito_operations", $array);
    } else {
      $array = array(
        "amount" => (float)$amount,
        "currency" => $currency,
        "merchantReferenceId" => $merchantTransactionId,
        "firstName" => $firstName,
        "lastName" => $lastName,
        "ip" => $ip,
        "http_code" => $res['pagadito_http_code'],
        "response_code" => $res['pagadito_response']['response_code'],
        "response_message" => $res['pagadito_response']['response_message'],
        "request_date" => $res['pagadito_response']['request_date'],
        "origin" => "api"
      );
      $wpdb->insert($wpdb->prefix . "er_pagadito_operations", $array);
    }

    return $res;
  } else {
    echo 'WooCommerce no está activo. Asegúrate de activarlo para proceder.';
  }
}

function validateRequest($data)
{
  $messages = array();
  if (!is_numeric($data['cardNumber']) || strlen($data['cardNumber']) != 16) {
    $messages['cardNumber'] = 'El número de tarjeta es inválido.';
  }
  if (!is_numeric($data['expiryMonth']) || strlen($data['expiryMonth']) != 2) {
    $messages['expiryMonth'] = 'El formato de mes es inválido.';
  }
  if (!is_numeric($data['expiryYear']) || strlen($data['expiryYear']) != 4) {
    $messages['expiryYear'] = 'El formato de año es inválido.';
  }
  if (!is_numeric($data['cvv']) || strlen($data['cvv']) != 3) {
    $messages['cvv'] = 'El código de seguridad de la tarjeta es inválido.';
  }
  if (!preg_match("/^[a-zA-Z0-9 .'-]{1,26}$/", $data['holderName'])) {
    $messages['holderName'] = 'El nombre es invalido, maximo 26 caracteres y se permiten solo letras y los sguientes caracteres especiales: Punto ( . ), Guión ( - ), Apóstrofe ( ’ ) y Comilla simple ( \' ).';
  }
  if (!preg_match("/^[a-zA-Z0-9 .'´]{1,30}$/", $data['firstName'])) {
    $messages['firstName'] = 'El nombre es invalido, maximo 30 caracteres y se permiten solo Punto ( . ) y Apóstrofe ( ’ )';
  }
  if (!preg_match("/^[a-zA-Z0-9 .'´]{1,30}$/", $data['lastName'])) {
    $messages['lastName'] = 'El apellido es invalido, maximo 30 caracteres y se permiten solo Punto ( . ) y Apóstrofe ( ’ )';
  }
  if (!preg_match("/^[a-zA-Z0-9 .'´]{1,30}$/", $data['city'])) {
    $messages['city'] = 'La ciudad es invalida, maximo 30 caracteres y se permiten solo Punto ( . ) y Apóstrofe ( ’ )';
  }
  if (strlen($data['email']) > 60) {
    $messages['email'] = "El correo no puede tener más de 60 caracteres.";
  }
  if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    $messages['email'] = "El correo no es válido.";
  }
  if (strlen($data['state']) > 80) {
    $messages['state'] = "El estado no puede tener más de 80 caracteres.";
  }
  if (!preg_match("/^[a-zA-Z0-9 .’()-]*$/", $data['state'])) {
    $messages['state'] = "El estado contiene caracteres no permitidos. Se permiten solo Paréntesis ( ), Guión ( - ), Punto ( . ) y Apóstrofe ( ’ )";
  }
  if (strlen($data['postalCode']) > 15) {
    $messages['postalCode'] = "El código postal no puede tener más de 15 caracteres.";
  }
  if (!preg_match("/^[a-zA-Z0-9 .-]*$/", $data['postalCode'])) {
    $messages['postalCode'] = "El código postal contiene caracteres no permitidos. Se permiten solo Punto ( . ) y Guión ( - )";
  }
  if (strlen($data['address']) > 60) {
    $messages['address'] = "La dirección no puede tener más de 60 caracteres.";
  }
  if (!preg_match("/^[a-zA-Z0-9 .,#;°&*-]*$/", $data['address'])) {
    $messages['address'] = "La dirección contiene caracteres no permitidos. Se permiten solo Punto ( . ), Coma ( , ), Numeral ( # ), Punto y coma ( ; ), Guión ( - ), Símbolo de grado ( ° ), Ampersand ( & ) y Asterísco ( * )";
  }
  if (strlen($data['phone']) > 15) {
    $messages['phone'] = "El telefono no puede tener más de 15 caracteres.";
  }
  if (!preg_match("/^[a-zA-Z0-9 ()+*-]*$/", $data['phone'])) {
    $messages['phone'] = "El telefono contiene caracteres no permitidos. Se permiten solo Paréntesis ( ), Signo Más ( + ), Guión ( - ) y Asterísco ( * )";
  }
  if (strlen($data['merchantReferenceId']) > 100) {
    $messages['merchantReferenceId'] = "La referencia no puede tener más de 100 caracteres.";
  }
  if (!preg_match("/^[a-zA-Z0-9 .-]*$/", $data['merchantReferenceId'])) {
    $messages['merchantReferenceId'] = "El string contiene caracteres no permitidos. Se permiten solo Punto ( . ) y Guión ( - )";
  }

  return $messages;
}
