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

require_once __DIR__ . '/class/class-pagadito-handler.php';
require_once __DIR__ . '/class/class-clients.php';
require_once __DIR__ . '/validation.php';

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
  register_rest_route('pagadito/v1', '/customer', array(
    'methods' => 'POST',
    'callback' => 'customer_endpoint',
  ));
  register_rest_route('pagadito/v1', '/setup_payer', array(
    'methods' => 'POST',
    'callback' => 'setup_payer_endpoint',
  ));
  register_rest_route('pagadito/v1', '/validate_card', array(
    'methods' => 'POST',
    'callback' => 'validate_card_endpoint',
  ));
});


add_action('rest_api_init', function () {
  register_rest_route('pagadito/v1', '/clients', array(
    'methods' => 'POST',
    'callback' => 'create_client_endpoint',
  ));
  register_rest_route('pagadito/v1', '/clients/(?P<id>\d+)', array(
    'methods' => 'PUT',
    'callback' => 'update_client_endpoint',
  ));
  register_rest_route('pagadito/v1', '/clients/(?P<id>\d+)', array(
    'methods' => 'GET',
    'callback' => 'get_client_endpoint',
  ));
  register_rest_route('pagadito/v1', '/clients', array(
    'methods' => 'GET',
    'callback' => 'get_clients_endpoint',
  ));
  register_rest_route('pagadito/v1', '/clients/(?P<id>\d+)', array(
    'methods' => 'DELETE',
    'callback' => 'delete_client_endpoint',
  ));
  register_rest_route('pagadito/v1', '/clients/(?P<id>\d+)/regenerate_token/(?P<type>[\w]+)', array(
    'methods' => 'POST',
    'callback' => 'regenerate_token_endpoint',
  ));
});

function get_transactions($data)
{
  // Validar el token
  $token = $data->get_header('Authorization');
  if (!$token) {
    return new WP_REST_Response(array('message' => 'Token no proporcionado'), 401);
  }

  $token = str_replace('Bearer ', '', $token); // Eliminar el prefijo 'Bearer ' del token
  $client_id = validate_jwt_token($token);
  if (!$client_id) {
    return new WP_REST_Response(array('message' => 'Token inválido o expirado'), 401);
  }

  $clients = new Clients();
  $client = $clients->getClientById($client_id);

  if (!$client) {
    return new WP_REST_Response(array('message' => 'Cliente no encontrado'), 404);
  }

  // Determinar el entorno
  $environment = ($token === $client->sandbox_token) ? 'sandbox' : 'production';

  global $wpdb;
  $tablaOperations = $wpdb->prefix . "er_pagadito_operations";
  $where = "client_id = " . intval($client_id) . " AND environment = '" . esc_sql($environment) . "'";

  if ($data['date_to'] && $data['date_from']) {
    $date_to = $data['date_to'];
    $date_from = $data['date_from'];
    $where .= " AND `date` >= '" . esc_sql($date_from) . "' AND `date` <= '" . esc_sql($date_to) . "'";
  }

  if ($data['origin']) {
    $origin = $data['origin'];
    $where .= " AND `origin` = '" . esc_sql($origin) . "'";
  }

  if ($data['country']) {
    $country = $data['country'];
    $where .= " AND `cod_country` = '" . esc_sql($country) . "'";
  }

  if ($data['http_code']) {
    $http_code = $data['http_code'];
    $where .= " AND `http_code` = '" . esc_sql($http_code) . "'";
  }

  $sql = "SELECT * FROM `" . $tablaOperations . "` WHERE " . $where;
  $result = $wpdb->get_results($sql);

  return new WP_REST_Response($result, 200);
}

function save_product($data)
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

  $clients = new Clients();
  $client = $clients->getClientById($client_id);

  if (!$client) {
    return new WP_REST_Response(array('message' => 'Cliente no encontrado'), 404);
  }

  $params = $data->get_json_params();
  $params['client_id'] = $client_id;

  // Verificar si el token es de sandbox
  if ($token === $client->sandbox_token) {
    $handler = new PagaditoHandler($client->role == 'admin', true);
  } else {
    $handler = new PagaditoHandler($client->role == 'admin');
  }

  $validateData = validateSaveProductRequest($params);
  if (count($validateData) > 0) {
    return new WP_REST_Response(
      array(
        "pagadito_http_code" => 400,
        "pagadito_response" => $validateData
      ),
      400
    );
  }

  $response = $handler->handleWooCommerce($params);
  return new WP_REST_Response($response, 200);
}

function create_client_endpoint($data)
{
  $client_id = validate_authorization($data);
  if ($client_id instanceof WP_REST_Response) {
    return $client_id;
  }

  try {
    $clients = new Clients();
    $params = $data->get_json_params();
    $result = $clients->createClient($params);
    return new WP_REST_Response($result, 201);
  } catch (Exception $e) {
    return new WP_REST_Response(array('message' => 'Error al crear el cliente', 'error' => $e->getMessage()), 500);
  }
}

function update_client_endpoint($data)
{
  $client_id = validate_authorization($data);
  if ($client_id instanceof WP_REST_Response) {
    return $client_id;
  }

  try {
    $clients = new Clients();
    $params = $data->get_json_params();
    $client_id = $data['id'];
    $updated = $clients->updateClient($client_id, $params);
    return new WP_REST_Response(array('updated' => $updated), 200);
  } catch (Exception $e) {
    return new WP_REST_Response(array('message' => 'Error al actualizar el cliente', 'error' => $e->getMessage()), 500);
  }
}

function get_client_endpoint($data)
{
  $client_id = validate_authorization($data);
  if ($client_id instanceof WP_REST_Response) {
    return $client_id;
  }

  try {
    $clients = new Clients();
    $client_id = intval($data['id']);
    $client = $clients->getClientById($client_id);

    if (!$client) {
      return new WP_REST_Response(array('message' => 'Cliente no encontrado'), 404);
    }

    return new WP_REST_Response($client, 200);
  } catch (Exception $e) {
    return new WP_REST_Response(array('message' => 'Error al obtener el cliente', 'error' => $e->getMessage()), 500);
  }
}

function get_clients_endpoint($data)
{
  $client_id = validate_authorization($data);
  if ($client_id instanceof WP_REST_Response) {
    return $client_id;
  }

  try {
    $clients = new Clients();
    $page = isset($data['page']) ? intval($data['page']) : 1;
    $per_page = isset($data['per_page']) ? intval($data['per_page']) : 10;

    $client_list = $clients->getClients($page, $per_page);

    return new WP_REST_Response($client_list, 200);
  } catch (Exception $e) {
    return new WP_REST_Response(array('message' => 'Error al obtener la lista de clientes', 'error' => $e->getMessage()), 500);
  }
}

function delete_client_endpoint($data)
{
  $client_id = validate_authorization($data);
  if ($client_id instanceof WP_REST_Response) {
    return $client_id;
  }

  try {
    $clients = new Clients();
    $client_id = intval($data['id']);
    $deleted = $clients->deleteClient($client_id);

    if ($deleted === 0) {
      return new WP_REST_Response(array('message' => 'Cliente no encontrado o ya eliminado'), 404);
    }

    return new WP_REST_Response(array('message' => 'Cliente eliminado correctamente'), 200);
  } catch (Exception $e) {
    return new WP_REST_Response(array('message' => 'Error al eliminar el cliente', 'error' => $e->getMessage()), 500);
  }
}

function regenerate_token_endpoint($data)
{
  $client_id = validate_authorization($data);
  if ($client_id instanceof WP_REST_Response) {
    return $client_id;
  }

  try {
    $clients = new Clients();
    $client_id = intval($data['id']);
    $type = sanitize_text_field($data['type']);

    if (!in_array($type, ['sandbox', 'production'])) {
      return new WP_REST_Response(array('message' => 'Tipo de token inválido'), 400);
    }

    $new_token = $clients->regenerateToken($client_id, $type);

    return new WP_REST_Response(array('new_token' => $new_token), 200);
  } catch (Exception $e) {
    return new WP_REST_Response(array('message' => 'Error al regenerar el token', 'error' => $e->getMessage()), 500);
  }
}

function setup_payer_endpoint($data)
{
  // Validar el token
  $token = $data->get_header('Authorization');
  if (!$token) {
    return new WP_REST_Response(array('message' => 'Token no proporcionado'), 401);
  }

  $token = str_replace('Bearer ', '', $token);
  $client_id = validate_jwt_token($token);
  if (!$client_id) {
    return new WP_REST_Response(array('message' => 'Token inválido o expirado'), 401);
  }

  $clients = new Clients();
  $client = $clients->getClientById($client_id);

  if (!$client) {
    return new WP_REST_Response(array('message' => 'Cliente no encontrado'), 404);
  }

  $params = [
    "card" => [
      "number" => sanitize_text_field($data['cardNumber']),
      "expirationDate" => sanitize_text_field($data['expirationDate']),
      "cvv" => sanitize_text_field($data['cvv']),
      "cardHolderName" => sanitize_text_field($data['holderName']),
      "firstName" => sanitize_text_field($data['firstName']),
      "lastName" => sanitize_text_field($data['lastName']),
      "billingAddress" => [
        "city" => sanitize_text_field($data['city']),
        "state" => sanitize_text_field($data['state']),
        "zip" => sanitize_text_field($data['postalCode']),
        "countryId" => sanitize_text_field($data['country']),
        "line1" => sanitize_text_field($data['address']),
        "phone" => sanitize_text_field($data['phone']),
      ],
      'email' => sanitize_text_field($data['email']),
    ],
    'returnUrl' => sanitize_text_field($data['returnUrl']),
    "transaction" => [
      "merchantTransactionId" => sanitize_text_field($data['mechantReferenceId']),
      "currencyId" => sanitize_text_field($data['currency']),
      "transactionDetails" => [
        [
          "quantity" => "1",
          "description" => "Recarga",
          "amount" => sanitize_text_field($data['amount']),
        ],
      ],
    ]
  ];

  // Instanciar PagaditoHandler y ejecutar setupPayer
  if ($token === $client->sandbox_token) {
    $handler = new PagaditoHandler($client->role == 'admin', true);
  } else {
    $handler = new PagaditoHandler($client->role == 'admin');
  }
  $res = $handler->setupPayer($params, $client_id, sanitize_text_field($data['ip']));

  return new WP_REST_Response($res, 200);
}

function customer_endpoint($data)
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

  $clients = new Clients();
  $client = $clients->getClientById($client_id);

  if (!$client) {
    return new WP_REST_Response(array('message' => 'Cliente no encontrado'), 404);
  }

  $params = $data->get_json_params();
  $params['client_id'] = $client_id;

  // Verificar si el token es de sandbox
  if ($token === $client->sandbox_token) {
    $handler = new PagaditoHandler($client->role == 'admin', true);
  } else {
    $handler = new PagaditoHandler($client->role == 'admin');
  }

  if (!isset($params['token']) || !is_string($params['token']) || empty($params['token'])) {
    return new WP_REST_Response(
      array(
        "pagadito_http_code" => 400,
        "pagadito_response" => 'Token is required and must be a non-empty string'
      ),
      400
    );
  }

  $res = $handler->setCustomer($params);

  return new WP_REST_Response($res, 200);
}

function validate_card_endpoint($data)
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

  $clients = new Clients();
  $client = $clients->getClientById($client_id);

  if (!$client) {
    return new WP_REST_Response(array('message' => 'Cliente no encontrado'), 404);
  }

  $params = $data->get_json_params();
  $params['client_id'] = $client_id;

  // Verificar si el token es de sandbox
  if ($token === $client->sandbox_token) {
    $handler = new PagaditoHandler($client->role == 'admin', true);
  } else {
    $handler = new PagaditoHandler($client->role == 'admin');
  }

  if (!isset($params['token']) || !is_string($params['token']) || empty($params['token'])) {
    return new WP_REST_Response(
      array(
        "pagadito_http_code" => 400,
        "pagadito_response" => 'Token is required and must be a non-empty string'
      ),
      400
    );
  }

  if (!isset($params['transactionId']) || !is_string($params['transactionId']) || empty($params['transactionId'])) {
    return new WP_REST_Response(
      array(
        "pagadito_http_code" => 400,
        "pagadito_response" => 'Transaction ID is required and must be a non-empty string'
      ),
      400
    );
  }

  $res = $handler->setValidateCard($params);

  return new WP_REST_Response($res, 200);
}