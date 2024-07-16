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
    $tablaClientes = $wpdb->prefix . "er_control_clientes";

    $query = 'CREATE TABLE ' . $tablaClientes . ' ('
      . '`ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,'
      . '`titulo` varchar(60) NOT NULL,'
      . '`nombre` varchar(80) NOT NULL,'
      . '`apellido` varchar(80) NULL,'
      . '`cedulaRif` varchar(40) UNIQUE,'
      . '`correo` varchar(60) NOT NULL,'
      . '`telefono` varchar(20) NULL,'
      . '`direccion` varchar(100) NOT NULL,'
      . '`status` tinyint NOT NULL DEFAULT 1,'
      . '`user_id` bigint(20) NOT NULL,'
      . '`fecha` datetime NOT NULL DEFAULT now()'
      . ');';
    $wpdb->get_results($query);
  }
}
