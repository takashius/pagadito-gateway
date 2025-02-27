<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function validateSaveProductRequest($data)
{
  $messages = array();

  // Validar campos requeridos
  $required_fields = [
    'cardNumber',
    'expirationDate',
    'cvv',
    'holderName',
    'firstName',
    'lastName',
    'city',
    'email',
    'state',
    'postalCode',
    'address',
    'phone',
    'mechantReferenceId',
    'country'
  ];

  foreach ($required_fields as $field) {
    if (empty($data[$field])) {
      $messages[$field] = 'El campo ' . $field . ' es obligatorio.';
    }
  }

  // Validaciones específicas para cada campo
  if (!is_numeric($data['cardNumber']) || strlen($data['cardNumber']) != 16) {
    $messages['cardNumber'] = 'El número de tarjeta es inválido.';
  }
  if (!is_numeric($data['cvv']) || strlen($data['cvv']) != 3) {
    $messages['cvv'] = 'El código de seguridad de la tarjeta es inválido.';
  }
  if (!preg_match("/^[a-zA-Z0-9 .'-]{1,26}$/", $data['holderName'])) {
    $messages['holderName'] = 'El nombre es invalido, maximo 26 caracteres y se permiten solo letras y los siguientes caracteres especiales: Punto ( . ), Guión ( - ), Apóstrofe ( ’ ) y Comilla simple ( \' ).';
  }
  if (!preg_match("/^[a-zA-Z0-9 .'´]{1,30}$/", $data['firstName'])) {
    $messages['firstName'] = 'El nombre es invalido, maximo 30 caracteres y se permiten solo Punto ( . ) y Apóstrofe ( ’ ).';
  }
  if (!preg_match("/^[a-zA-Z0-9 .'´]{1,30}$/", $data['lastName'])) {
    $messages['lastName'] = 'El apellido es invalido, maximo 30 caracteres y se permiten solo Punto ( . ) y Apóstrofe ( ’ ).';
  }
  if (!preg_match("/^[a-zA-Z0-9 .'´]{1,30}$/", $data['city'])) {
    $messages['city'] = 'La ciudad es invalida, maximo 30 caracteres y se permiten solo Punto ( . ) y Apóstrofe ( ’ ).';
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
    $messages['state'] = "El estado contiene caracteres no permitidos. Se permiten solo Paréntesis ( ), Guión ( - ), Punto ( . ) y Apóstrofe ( ’ ).";
  }
  if (strlen($data['postalCode']) > 15) {
    $messages['postalCode'] = "El código postal no puede tener más de 15 caracteres.";
  }
  if (!preg_match("/^[a-zA-Z0-9 .-]*$/", $data['postalCode'])) {
    $messages['postalCode'] = "El código postal contiene caracteres no permitidos. Se permiten solo Punto ( . ) y Guión ( - ).";
  }
  if (strlen($data['address']) > 60) {
    $messages['address'] = "La dirección no puede tener más de 60 caracteres.";
  }
  if (!preg_match("/^[a-zA-Z0-9 .,#;°&*-]*$/", $data['address'])) {
    $messages['address'] = "La dirección contiene caracteres no permitidos. Se permiten solo Punto ( . ), Coma ( , ), Numeral ( # ), Punto y coma ( ; ), Guión ( - ), Símbolo de grado ( ° ), Ampersand ( & ) y Asterísco ( * ).";
  }
  if (strlen($data['phone']) > 15) {
    $messages['phone'] = "El telefono no puede tener más de 15 caracteres.";
  }
  if (!preg_match("/^[a-zA-Z0-9 ()+*-]*$/", $data['phone'])) {
    $messages['phone'] = "El telefono contiene caracteres no permitidos. Se permiten solo Paréntesis ( ), Signo Más ( + ), Guión ( - ) y Asterísco ( * ).";
  }
  if (strlen($data['mechantReferenceId']) > 100) {
    $messages['merchantReferenceId'] = "La referencia no puede tener más de 100 caracteres.";
  }
  if (!preg_match("/^[a-zA-Z0-9 .-]*$/", $data['mechantReferenceId'])) {
    $messages['merchantReferenceId'] = "El string contiene caracteres no permitidos. Se permiten solo Punto ( . ) y Guión ( - ).";
  }
  if (!is_numeric($data['country']) || strlen((string)$data['country']) > 3) {
    $messages['country'] = "Utilice los códigos de país de tres números que se encuentran en el Listado de Códigos ISO 3166 para Países.";
  }

  return $messages;
}

function validateHolderName($name)
{
  $name = eliminarAcentos($name);
  $parts = explode(' ', $name);

  if (strlen($name) > 26) {
    if (count($parts) === 4) {
      array_pop($parts);
    } else if (count($parts) === 3) {
      unset($parts[1]);
      $parts = array_values($parts);
    }
  }

  return implode(' ', $parts);
}

function eliminarAcentos($string)
{
  $acentos = array(
    'á' => 'a',
    'é' => 'e',
    'í' => 'i',
    'ó' => 'o',
    'ú' => 'u',
    'Á' => 'A',
    'É' => 'E',
    'Í' => 'I',
    'Ó' => 'O',
    'Ú' => 'U'
  );
  return strtr($string, $acentos);
}

function validarCodigoPais($codigo)
{
  $codigosPais = [
    "222",
    "320",
    "340",
    "558",
    "188",
    "591",
    "840",
    "218",
    "068",
    "600",
    "858",
    "032",
    "152",
    "484",
    "724",
    "630",
    "250",
    "124",
    "380",
    "826",
    "388",
    "060",
    "084",
    "534",
    "328",
    "740"
  ];
  return in_array($codigo, $codigosPais);
}

function validate_jwt_token($token)
{
  $secret_key = 'S0ph14%41M3&G4Br13l4';
  try {
    $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
    $client_id = $decoded->id;

    // Verificar si el token es válido comparándolo con el almacenado en la base de datos 
    $clients = new Clients();
    $client = $clients->getClientById($client_id);
    if ($client && ($client->sandbox_token === $token || $client->production_token === $token)) {
      return $client_id;
    } else {
      return null;
    }
  } catch (Exception $e) {
    return null;
  }
}

function is_client_admin($client_id)
{
  $clients = new Clients();
  $client = $clients->getClientById($client_id);
  return $client && $client->role === 'admin';
}

function validate_authorization($data)
{
  $token = $data->get_header('Authorization');
  if (!$token) {
    return new WP_REST_Response(array('message' => 'Token no proporcionado'), 401);
  }

  $token = str_replace('Bearer ', '', $token);
  $client_id = validate_jwt_token($token);
  if (!$client_id) {
    return new WP_REST_Response(array('message' => 'Token inválido o expirado'), 401);
  }

  // Verificar rol de administrador 
  if (!is_client_admin($client_id)) {
    return new WP_REST_Response(array('message' => 'No autorizado'), 403);
  }

  return $client_id;
}
