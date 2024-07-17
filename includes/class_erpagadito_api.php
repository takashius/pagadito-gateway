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
  register_rest_route('pagadito/v1', '/cobro', array(
    'methods' => 'POST',
    'callback' => 'save_product',
  ));
});


function save_product($data)
{

  if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    global $wpdb;
    $amount = $data['amount'];
    $ip = $data['ip'];
    $merchantTransactionId = $data['mechantReferenceId'];
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
      print_r($array);
      $wpdb->insert($wpdb->prefix . "er_pagadito_operations", $array);
    }

    return $res;
  } else {
    echo 'WooCommerce no está activo. Asegúrate de activarlo para proceder.';
  }
}
