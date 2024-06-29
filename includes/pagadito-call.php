<?php
/* If your project is live, uncomment the following line, and comment the next one*/
/* define("GATEWAY_URL", "https://api.pagadito.com/v1/"); */

define("GATEWAY_URL", "https://sandbox-api.pagadito.com/v1/");
/* Copy from Technical Configuration -> Integration Parameters */
define("KEY_UID", "10c761376d75ed5f70e6652bbe987856");
define("KEY_WSK", "3e6f661af746a20050c23659857dea29");
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
      'line1' => $address,
      'phone' => $phone,
    ),
    'email' => $email,
  ),
  /* You have to add the transaction information, including the details */
  /* transaction Object */
  'transaction' => array(
    'merchantTransactionId' => $mechantReferenceId,
    'currencyId' => $currency,
    /* transactionDetails Object */
    'transactionDetails' => array(
      array(
        'quantity' => '1',
        'description' => 'Recarga',
        'amount' => $amount,
      ),
    ),
  ),
  /* You have to add the navigation information */
  /* browserInfo Object */
  'browserInfo' => array(
    'deviceFingerprintID' => time(),
    'customerIp' => $ip,
  ),
);
/* Now, you send information to Pagadito */
$Pagadito = new Pagadito(false);
$res = $Pagadito->createCustomer($params);
// print_r($params);