<?php
/*
 * Plugin Name: WooCommerce Er Pagadito Gateway
 * Plugin URI: https://erdesarrollo.com.ve/pagadito-plugin
 * Description: Custom payment gateway plugin for the Pagadito platform.
 * Author: Erick Hernandez
 * Author URI: http://erdesarrollo.com.ve
 * Version: 3.1.9
 */

require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

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

require_once plugin_dir_path(__FILE__) . 'admin/pagadito-gateway-admin.php';


function add_custom_plugin_query_vars($query_vars)
{
  $query_vars[] = 'pagadito_setup_payer';
  $query_vars[] = 'pagadito_return';
  return $query_vars;
}
add_filter('query_vars', 'add_custom_plugin_query_vars');

function add_custom_plugin_rewrite_rules()
{
  add_rewrite_rule('^pagadito-test-3ds/?$', 'index.php?pagadito_setup_payer=1', 'top');
  add_rewrite_rule('^pagadito-test-3ds/return/?$', 'index.php?pagadito_return=1', 'top');
}
add_action('init', 'add_custom_plugin_rewrite_rules');

function template_redirect_to_custom_pages()
{
  global $wp_query;
  if (isset($wp_query->query_vars['pagadito_setup_payer'])) {
    include plugin_dir_path(__FILE__) . 'public/public-setup-payer.php';
    exit;
  }
  if (isset($wp_query->query_vars['pagadito_return'])) {
    include plugin_dir_path(__FILE__) . 'public/return.php';
    exit;
  }
}
add_action('template_redirect', 'template_redirect_to_custom_pages');

function enqueue_custom_styles_and_scripts()
{
  if (is_checkout()) {
    wp_enqueue_style('custom-form-styles', plugin_dir_url(__FILE__) . 'design/custom-form.css', array(), '1.0.0');
    wp_enqueue_script('sweetalert', 'https://cdn.jsdelivr.net/npm/sweetalert2@11');
  }
}
add_action('wp_enqueue_scripts', 'enqueue_custom_styles_and_scripts');
