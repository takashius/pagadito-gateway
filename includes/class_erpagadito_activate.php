<?php

/**
 * Fired during plugin activation
 *
 * @link       https://erdesarrollo.com.ve
 * @since      1.0.1
 *
 * @package    ErPagadito_gateway
 * @subpackage ErPagadito_gateway/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.1
 * @package    ErPagadito_gateway
 * @subpackage ErPagadito_gateway/includes
 * @author     Erick Hernandez <erick@erdesarrollo.com.ve>
 */
class ErPagadito_gateway_Activator
{

  /**
   * Short Description. (use period)
   *
   * Long Description.
   *
   * @since    1.1.8
   */
  public static function activate()
  {
    global $wpdb;
    $tableOperation = $wpdb->prefix . "er_pagadito_operations";
    $tableClients = $wpdb->prefix . "er_pagadito_clients";

    // Crear tabla de operaciones
    $queryOperation = 'CREATE TABLE ' . $tableOperation . ' ('
      . '`ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,'
      . '`client_id` bigint(20) unsigned NOT NULL,'
      . '`amount` float(12,2) NOT NULL,'
      . '`currency` varchar(5) NULL,'
      . '`cod_country` varchar(5) NULL,'
      . '`merchantReferenceId` varchar(40) NULL,'
      . '`firstName` varchar(160) NOT NULL,'
      . '`lastName` varchar(160) NOT NULL,'
      . '`ip` varchar(20) NOT NULL,'
      . '`email` varchar(120) NOT NULL,'
      . '`phone` varchar(60) NOT NULL,'
      . '`address` varchar(160) NOT NULL,'
      . '`city` varchar(20) NOT NULL,'
      . '`postalCode` varchar(20) NOT NULL,'
      . '`request_id` varchar(100) NULL,'
      . '`referenceId` varchar(100) NULL,'
      . '`authorization` varchar(60) NOT NULL,'
      . '`http_code` varchar(5) NOT NULL,'
      . '`response_code` varchar(25) NOT NULL,'
      . '`response_message` TEXT NOT NULL,'
      . '`request_date` datetime NOT NULL,'
      . '`paymentDate` datetime NULL,'
      . '`environment` ENUM("production", "sandbox") NOT NULL,'
      . '`origin` ENUM("web", "api") NOT NULL DEFAULT "api",'
      . '`token` varchar(160) NULL,'
      . '`date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP'
      . ');';

    // Crear tabla de clientes
    $queryClients = 'CREATE TABLE ' . $tableClients . ' ('
      . '`ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,'
      . '`name` varchar(255) NOT NULL,'
      . '`abbr_name` varchar(4) NOT NULL,'
      . '`email` varchar(255) NOT NULL,'
      . '`address` varchar(255) NOT NULL,'
      . '`tax_id` varchar(255) NOT NULL,'
      . '`sandbox_token` TEXT NOT NULL,'
      . '`production_token` TEXT NOT NULL,'
      . '`role` ENUM("admin", "user") NOT NULL DEFAULT "user",'
      . '`created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,'
      . '`updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
      . '`deleted` boolean NOT NULL DEFAULT 0'
      . ');';

    $wpdb->query($queryOperation);
    $wpdb->query($queryClients);
  }
}
