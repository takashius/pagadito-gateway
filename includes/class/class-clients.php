<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Clients
{

  private $wpdb;
  private $table;
  private $secret_key;

  public function __construct()
  {
    global $wpdb;
    $this->wpdb = $wpdb;
    $this->table = $wpdb->prefix . "er_pagadito_clients";
    $this->secret_key = 'S0ph14%41M3&G4Br13l4';
  }

  public function createClient($data)
  {
    $result = $this->wpdb->insert(
      $this->table,
      array(
        'name' => sanitize_text_field($data['name']),
        'abbr_name' => sanitize_text_field($data['abbr_name']),
        'email' => sanitize_email($data['email']),
        'address' => sanitize_text_field($data['address']),
        'tax_id' => sanitize_text_field($data['tax_id']),
        'role' => isset($data['role']) ? sanitize_text_field($data['role']) : 'user',
      )
    );

    if ($result === false) {
      return array('client_id' => 0, 'error' => $this->wpdb->last_error);
    }

    return array('client_id' => $this->wpdb->insert_id);
  }

  public function updateClient($id, $data)
  {
    $this->wpdb->update(
      $this->table,
      array(
        'name' => sanitize_text_field($data['name']),
        'abbr_name' => sanitize_text_field($data['abbr_name']),
        'email' => sanitize_email($data['email']),
        'address' => sanitize_text_field($data['address']),
        'tax_id' => sanitize_text_field($data['tax_id']),
        'role' => isset($data['role']) ? sanitize_text_field($data['role']) : 'user',
        'updated_at' => current_time('mysql')
      ),
      array('ID' => intval($id))
    );

    return true;
  }

  public function setClientSecret($data, $isSandbox = true)
  {
    if ($isSandbox) {
      $dataQuery = array(
        'sandbox_client_id' => sanitize_text_field($data['client_id']),
        'sandbox_client_secret' => sanitize_text_field($data['client_secret']),
        'updated_at' => current_time('mysql')
      );
    } else {
      $dataQuery = array(
        'client_id' => sanitize_text_field($data['client_id']),
        'client_secret' => sanitize_text_field($data['client_secret']),
        'updated_at' => current_time('mysql')
      );
    }
    $this->wpdb->update(
      $this->table,
      $dataQuery,
      array('ID' => intval($data['id']))
    );

    return true;
  }

  public function setClientToken($id, $data, $isTestMode)
  {
    if ($isTestMode) {
      $query = $this->wpdb->prepare(
        "UPDATE {$this->table} SET
          sandbox_pagadito_token = %s,
          sandbox_token_expiration = %s,
          updated_at = %s
          WHERE ID = %d",
        sanitize_text_field($data['pagadito_token']),
        sanitize_text_field($data['token_expiration']),
        current_time('mysql'),
        intval($id)
      );
    } else {
      $query = $this->wpdb->prepare(
        "UPDATE {$this->table} SET
          pagadito_token = %s,
          token_expiration = %s,
          updated_at = %s
          WHERE ID = %d",
        sanitize_text_field($data['pagadito_token']),
        sanitize_text_field($data['token_expiration']),
        current_time('mysql'),
        intval($id)
      );
    }

    $result = $this->wpdb->query($query);
    return $result !== false ? $this->wpdb->rows_affected : 0;
  }

  public function getClients($page = 1, $per_page = 10)
  {
    $offset = ($page - 1) * $per_page;
    $query = $this->wpdb->prepare("SELECT * FROM {$this->table} WHERE deleted = 0 LIMIT %d OFFSET %d", $per_page, $offset);
    return $this->wpdb->get_results($query);
  }

  public function getClientById($id)
  {
    $query = $this->wpdb->prepare("SELECT * FROM {$this->table} WHERE ID = %d AND deleted = 0", intval($id));
    return $this->wpdb->get_row($query);
  }

  public function deleteClient($id)
  {
    $this->wpdb->update(
      $this->table,
      array('deleted' => 1),
      array('ID' => intval($id))
    );

    return $this->wpdb->affected_rows;
  }

  public function regenerateToken($client_id, $type)
  {
    $token_payload = array(
      'id' => $client_id,
      'issued_at' => time(),
    );

    $new_token = JWT::encode($token_payload, $this->secret_key, 'HS256');
    $field = $type === 'sandbox' ? 'sandbox_token' : 'production_token';

    $this->wpdb->update(
      $this->table,
      array($field => $new_token),
      array('ID' => intval($client_id))
    );

    return $new_token;
  }
}
