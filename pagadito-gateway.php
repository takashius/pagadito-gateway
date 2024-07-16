<?php
/*
 * Plugin Name: WooCommerce Er Pagadito Gateway
 * Plugin URI: https://rudrastyh.com/woocommerce/payment-gateway-plugin.html
 * Description: Custom payment gateway plugin for the Pagadito platform.
 * Author: Erick Hernandez
 * Author URI: http://erdesarrollo.com.ve
 * Version: 1.0.1
 */

/*
 * This action hook registers our PHP class as a WooCommerce payment gateway
 */
add_filter('woocommerce_payment_gateways', 'er_pagadito_add_gateway_class');

function er_pagadito_add_gateway_class($gateways)
{
  $gateways[] = 'WC_Er_Pagadito_Gateway'; // your class name is here
  return $gateways;
}

/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
function er_pagadito_init_gateway_class()
{
  require_once plugin_dir_path(__FILE__) . 'includes/class_erpagadito_gateway.php';
}
add_action('plugins_loaded', 'er_pagadito_init_gateway_class');

function bootstrap_css()
{
  wp_enqueue_style(
    'bootstrap_css',
    'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css',
    array(),
    '4.1.3'
  );
  wp_enqueue_script(
    'jquery_min',
    'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js',
    '3.2.1',
    false
  );
  wp_enqueue_script(
    'creditCardValidator',
    'https://cdnjs.cloudflare.com/ajax/libs/jquery-creditcardvalidator/1.0.0/jquery.creditCardValidator.js',
    array('jquery_min'),
    '1.0.0',
    false
  );
}
add_action('wp_enqueue_scripts', 'bootstrap_css');

add_action('woocommerce_blocks_loaded', 'rudr_gateway_block_support');
function rudr_gateway_block_support()
{

  // here we're including our "gateway block support class"
  require_once __DIR__ . '/includes/class-wc-er-pagadito-gateway-blocks-support.php';

  // registering the PHP class we have just included
  add_action(
    'woocommerce_blocks_payment_method_type_registration',
    function (Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
      $payment_method_registry->register(new WC_Er_Pagadito_Gateway_Blocks_Support);
    }
  );
}

require_once plugin_dir_path(__FILE__) . 'includes/class_erpagadito_api.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-erpagadito_email_extra.php
 */
function custom_email_order_meta($order, $sent_to_admin, $plain_text)
{
  require_once plugin_dir_path(__FILE__) . 'includes/class-erpagadito_email_extra.php';
  ErPagadito_gateway_Email_Extra::updateData($order, $sent_to_admin, $plain_text);
}
add_filter('woocommerce_email_order_meta', 'custom_email_order_meta', 10, 3);

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class_erpagadito_activate.php
 */
function activate_pagadito_gateway()
{
  require_once plugin_dir_path(__FILE__) . 'includes/class_erpagadito_activate.php';
  ErPagadito_gateway_Activator::activate();
}
register_activation_hook(__FILE__, 'activate_pagadito_gateway');
