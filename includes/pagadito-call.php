<?php
/* If your project is live, uncomment the following line, and comment the next one*/
/* define("GATEWAY_URL", "https://api.pagadito.com/v1/"); */
$gateway_options = get_option('woocommerce_' . 'er_pagadito_settings', array());
$testmode = isset($gateway_options['testmode']) ? $gateway_options['testmode'] : 'yes';
$test_publishable_key = isset($gateway_options['test_publishable_key']) ? $gateway_options['test_publishable_key'] : '';
$test_private_key = isset($gateway_options['test_private_key']) ? $gateway_options['test_private_key'] : '';
$publishable_key = isset($gateway_options['publishable_key']) ? $gateway_options['publishable_key'] : '';
$private_key = isset($gateway_options['private_key']) ? $gateway_options['private_key'] : '';


if ($testmode === 'yes') {
  define("GATEWAY_URL", "https://sandbox-api.pagadito.com/v1/");

  define("KEY_UID", $test_publishable_key);
  define("KEY_WSK", $test_private_key);
} else {
  define("GATEWAY_URL", "https://api.pagadito.com/v1/");

  define("KEY_UID", $publishable_key);
  define("KEY_WSK", $private_key);
}

// require_once __DIR__ . '/lib/class.pagadito.php';
require_once('lib/class.pagadito.php');

$params = array(
  /* You have to add the credit card and billing information */
  /* card Object */
  'card' => array(
    'number' => $cardNumber,
    'expirationDate' => $expiryMonth . '/' . $expiryYear,
    'cvv' => $cvv,
    'cardHolderName' => $holderName,
    'firstName' => $firstName,
    'lastName' => $lastName,
    /* billingAddress Object */
    'billingAddress' => array(
      'city' => $city,
      'state' => $state,
      'zip' => $postalCode,
      'countryId' => '740',
      'line1' => $address_1,
      'line2' => $address_2,
      'phone' => $phone,
    ),
    'email' => $email,
  ),
  /* You have to add the transaction information, including the details */
  /* transaction Object */
  'transaction' => $transaction,
  /* You have to add the navigation information */
  /* browserInfo Object */
  'browserInfo' => array(
    'deviceFingerprintID' => time(),
    'customerIp' => $ip,
  ),
);
// echo "KEY_UID = " . KEY_UID . "/n";
// echo "KEY_WSK = " . KEY_WSK . "/n";
// echo "testmode = " . $testmode . "/n";
/* Now, you send information to Pagadito */
$Pagadito = new Pagadito(false);
$res = $Pagadito->createCustomer($params);
// print_r($res);
// print_r($params);