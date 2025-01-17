<?php

/**
 * Payment gateway main class
 *
 * @link       https://erdesarrollo.com.ve
 * @since      1.2.1
 *
 * @package    ErPagadito_gateway
 * @subpackage ErPagadito_gateway/includes
 */

/**
 * Payment gateway main class.
 *
 * This class initializes the methods necessary to display the payment gateway.
 *
 * @since      1.2.1
 * @package    ErPagadito_gateway
 * @subpackage ErPagadito_gateway/includes
 * @author     Erick Hernandez <erick@erdesarrollo.com.ve>
 */

class WC_Er_Pagadito_Gateway extends WC_Payment_Gateway
{

  /**
   * Class constructor, more about it in Step 3
   */
  public function __construct()
  {
    $this->id = 'er_pagadito'; // payment gateway plugin ID
    $this->icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name
    $this->has_fields = true; // in case you need a custom credit card form
    $this->method_title = 'Er Pagadito Gateway';
    $this->method_description = 'Custom payment gateway plugin for the Pagadito platform'; // will be displayed on the options page

    // gateways can support subscriptions, refunds, saved payment methods,
    // but in this tutorial we begin with simple payments
    $this->supports = array(
      'products'
    );

    // Method with all the options fields
    $this->init_form_fields();

    // Load the settings.
    $this->init_settings();
    $this->title = $this->get_option('title');
    $this->description = $this->get_option('description');
    $this->enabled = $this->get_option('enabled');
    $this->testmode = 'yes' === $this->get_option('testmode');
    $this->private_key = $this->testmode ? $this->get_option('test_private_key') : $this->get_option('private_key');
    $this->publishable_key = $this->testmode ? $this->get_option('test_publishable_key') : $this->get_option('publishable_key');

    // This action hook saves the settings
    add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

    // We need custom JavaScript to obtain a token
    add_action('wp_enqueue_scripts', array($this, 'payment_scripts'));

    // You can also register a webhook here
    // add_action( 'woocommerce_api_{webhook name}', array( $this, 'webhook' ) );
  }

  /**
   * Plugin options, we deal with it in Step 3 too
   */
  public function init_form_fields()
  {
    $this->form_fields = array(
      'enabled' => array(
        'title'       => 'Enable/Disable',
        'label'       => 'Enable Pagadito Gateway',
        'type'        => 'checkbox',
        'description' => '',
        'default'     => 'no'
      ),
      'title' => array(
        'title'       => 'Title',
        'type'        => 'text',
        'description' => 'This controls the title which the user sees during checkout.',
        'default'     => 'Credit Card',
        'desc_tip'    => true,
      ),
      'description' => array(
        'title'       => 'Description',
        'type'        => 'textarea',
        'description' => 'This controls the description which the user sees during checkout.',
        'default'     => 'Pague con su tarjeta de crédito a través de Pagadito.',
      ),
      'testmode' => array(
        'title'       => 'Test mode',
        'label'       => 'Enable Test Mode',
        'type'        => 'checkbox',
        'description' => 'Place the payment gateway in test mode using test API keys.',
        'default'     => 'yes',
        'desc_tip'    => true,
      ),
      'test_publishable_key' => array(
        'title'       => 'Test Publishable Key',
        'type'        => 'text'
      ),
      'test_private_key' => array(
        'title'       => 'Test Private Key',
        'type'        => 'password',
      ),
      'publishable_key' => array(
        'title'       => 'Live Publishable Key',
        'type'        => 'text'
      ),
      'private_key' => array(
        'title'       => 'Live Private Key',
        'type'        => 'password'
      )
    );
  }

  /**
   * You will need it if you want your custom credit card form, Step 4 is about it
   */
  public function payment_fields()
  {
    // ok, let's display some description before the payment form
    if ($this->description) {
      // you can instructions for test mode, I mean test card numbers etc.
      if ($this->testmode) {
        $this->description .= ' TEST MODE ENABLED. In test mode, you can use the card numbers listed in <a href="#">documentation</a>.';
        $this->description  = trim($this->description);
      }
      // display the description with <p> tags etc.
      echo wpautop(wp_kses_post($this->description));
    }

    // I will echo() the form, but you can close PHP tags and print it directly in HTML
    echo '<fieldset id="wc-' . esc_attr($this->id) . '-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">';

    // Add this action hook if you want your custom payment gateway to support it
    do_action('woocommerce_credit_card_form_start', $this->id);

    require_once plugin_dir_path(__FILE__) . '../design/form.php';

    do_action('woocommerce_credit_card_form_end', $this->id);

    echo '<div class="clear"></div></fieldset>';
  }

  /*
    * Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form
    */
  public function payment_scripts()
  {
    if (is_checkout()) {
      if (!function_exists('get_plugin_data')) {
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
      }
      $plugin_file_path = plugin_dir_path(__FILE__) . '../pagadito-gateway.php';
      $plugin_data = get_plugin_data($plugin_file_path);
      $plugin_version = $plugin_data['Version'];

      $cart_contents = WC()->cart->get_cart();
      $user_id = get_current_user_id();
      $timestamp = time();
      $unique_data = array(
        'cart_contents' => $cart_contents,
        'user_id' => $user_id,
        'timestamp' => $timestamp
      );
      $cart_hash = "REDW-" . md5(json_encode($unique_data));

      wp_register_script('custom-payment-script', plugin_dir_url(__FILE__) . '../design/custom-payment-script.js', array('jquery'), $plugin_version, true);

      wp_localize_script('custom-payment-script', 'data', array(
        'cart_total' => WC()->cart->get_cart_contents_total(),
        'uniq_hash' => $cart_hash,
        'site_url' => get_site_url(),
        'user_ip' => $this->get_ip_address() ? $this->get_ip_address() : "208.87.3.109"
      ));

      wp_enqueue_script('custom-payment-script');
    }
  }


  /*
     * Fields validation, more in Step 5
    */
  public function validate_fields()
  {
    if (empty($_POST['cc-name'])) {
      wc_add_notice('El nombre de la <b>tarjeta de crédito</b> es requerido', 'error');
      return false;
    }
    if (empty($_POST['cc-number'])) {
      wc_add_notice('El número de la <b>tarjeta de crédito</b> es requerido', 'error');
      return false;
    }
    if (empty($_POST['cc-expiration'])) {
      wc_add_notice('El vencimiento de la <b>tarjeta de crédito</b> es requerido', 'error');
      return false;
    }
    if (empty($_POST['cc-cvv'])) {
      wc_add_notice('El código de seguridad de la <b>tarjeta de crédito</b> es requerido', 'error');
      return false;
    }
    if (empty($_POST['cc_authorization'])) {
      wc_add_notice('Debe validar su pago previamente', 'error');
      return false;
    }
    return true;
  }

  /*
    * We're processing the payments here, everything about it is in Step 5
    */
  public function process_payment($order_id)
  {
    @ini_set('display_errors', 1);
    global $woocommerce;
    $order = new WC_Order($order_id);

    $request_id = sanitize_text_field($_POST['cc_request_id']);
    $authorization = sanitize_text_field($_POST['cc_authorization']);
    $merchant_reference_id = sanitize_text_field($_POST['cc_merchant_reference_id']);

    // Actualiza el estado del pedido a completado
    $order->set_status('completed', 'Pedido completado automáticamente');

    // Establecer el método de pago
    $order->set_payment_method('er_pagadito');
    $order->set_payment_method_title('Pagadito');

    // Añadir metadatos al pedido
    $order->add_meta_data('request_id', $request_id);
    $order->add_meta_data('authorization', $authorization);
    $order->add_meta_data('merchant_reference_id', $merchant_reference_id);

    // Marcar el pedido como completado
    $order->payment_complete();

    // Calcular totales y guardar el pedido
    $order->calculate_totals();
    $order->save();

    // Vaciar el carrito
    $woocommerce->cart->empty_cart();

    // Redirección a la página de agradecimiento
    return array(
      'result' => 'success',
      'redirect' => $this->get_return_url($order)
    );
  }

  function get_ip_address()
  {
    foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
      if (array_key_exists($key, $_SERVER) === true) {
        foreach (explode(',', $_SERVER[$key]) as $ip) {
          $ip = trim($ip);

          if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
            return $ip;
          }
        }
      }
    }
  }

  /*
    * In case you need a webhook, like PayPal IPN etc
    */
  public function webhook() {}
}
