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

    $query = 'CREATE TABLE ' . $tableOperation . ' ('
      . '`ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,'
      . '`amount` float(12,2) NOT NULL,'
      . '`currency` varchar(5) NULL,'
      . '`merchantReferenceId` varchar(40) NULL,'
      . '`firstName` varchar(160) NULL,'
      . '`lastName` varchar(160) NULL,'
      . '`ip` varchar(20) NULL,'
      . '`authorization` varchar(60) NULL,'
      . '`http_code` varchar(5) NOT NULL,'
      . '`response_code` varchar(25) NOT NULL,'
      . '`response_message` TEXT NOT NULL,'
      . '`request_date` datetime NOT NULL,'
      . '`paymentDate` datetime NULL,'
      . '`origin` ENUM("web", "api") NOT NULL DEFAULT "api",'
      . '`date` datetime NOT NULL DEFAULT now()'
      . ');';
    $wpdb->get_results($query);
  }
}
