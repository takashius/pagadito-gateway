<?php

/**
 * Register the Menu for the admin area.
 *
 * @since    2.0.3
 */

add_action('admin_menu', 'admin_menu');

function admin_menu()
{
  $key = 'edit.php?post_type=pagadito-gateway';
  add_menu_page(
    __('Pagadito Reports', 'er-pagadito-gateway'), // Title of the page
    __('Pagadito Reports', 'pagadito-gateway'), // Text to show on the menu link
    'publish_posts', // Capability requirement to see the link
    'pagadito-gateway',
    'home_page',
    'dashicons-printer',
    27
  );
  // add_submenu_page(
  //   'pagadito-gateway',
  //   __('Ventas', 'pagadito-gateway'),
  //   __('Ventas', 'pagadito-gateway'),
  //   'publish_posts',
  //   'er-pagadito-ventas',
  //   'home_ventas'
  // );
  // add_submenu_page(
  //   'pagadito-gateway',
  //   __('Clientes', 'pagadito-gateway'),
  //   __('Clientes', 'pagadito-gateway'),
  //   'publish_posts',
  //   'er-pagadito-clientes',
  //   'home_clientes'
  // );
  // add_submenu_page(
  //   'pagadito-gateway',
  //   __('Items', 'pagadito-gateway'),
  //   __('Items', 'pagadito-gateway'),
  //   'manage_options',
  //   'er-pagadito-items',
  //   'home_items'
  // );
}

add_action('admin_enqueue_scripts', 'enqueue_scripts');

function enqueue_scripts()
{
  wp_enqueue_style('er-pagadito-gateway', plugin_dir_url(__FILE__) . 'css/pagadito-gateway-admin.css', array(), '2.0.3', 'all');
  wp_enqueue_style("boostrap-min", plugin_dir_url(__FILE__) . 'css/bootstrap.min.css', array(), "4.5.3", 'all');
  wp_enqueue_style("boostrap-grid-min", plugin_dir_url(__FILE__) . 'css/bootstrap-grid.min.css', array(), "4.5.3", 'all');
  wp_enqueue_style("font-awesome", 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.css', array(), "4.4.0", 'all');
  wp_enqueue_style("DataTables-min", plugin_dir_url(__FILE__) . 'css/datatables.min.css', array('boostrap-min'), "1.10.22", 'all');
  wp_enqueue_style("jquery.notyfy", plugin_dir_url(__FILE__) . 'css/jquery.notyfy.css', array(), '2.0.3', 'all');
  wp_enqueue_style("jquery.notyfy-themes", plugin_dir_url(__FILE__) . 'css/jquery.notyfy-themes.default.css', array(), '2.0.3', 'all');
  wp_enqueue_style("Select2", plugin_dir_url(__FILE__) . 'css/select2.min.css', array(), "4.1.0", 'all');
  wp_enqueue_style("select2-bootstrap-theme", plugin_dir_url(__FILE__) . 'css/select2-bootstrap.min.css', array(), "0.1.0", 'all');
  wp_enqueue_style("Datepicker", 'https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/css/bootstrap-datepicker3.min.css', array(), "2.0", 'all');

  wp_enqueue_script("jquery-min", plugin_dir_url(__FILE__) . 'js/jquery-3.5.1.min.js', array(), "3.5.1", false);
  wp_enqueue_script("bootstrap-min", plugin_dir_url(__FILE__) . 'js/bootstrap.min.js', array('jquery-min'), "1.10.22", false);
  wp_enqueue_script("jquery.notyfy", plugin_dir_url(__FILE__) . 'js/jquery.notyfy.js', array('jquery-min'), '2.0.3', false);
  wp_enqueue_script("datatables-min", plugin_dir_url(__FILE__) . 'js/datatables.min.js', array('jquery-min', 'bootstrap-min'), "1.10.22", false);
  wp_enqueue_script('er-pagadito-gateway', plugin_dir_url(__FILE__) . 'js/pagatido-gateway-admin.js', array('jquery'), '2.0.3', false);
  wp_enqueue_script("bootbox", 'https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.4.0/bootbox.min.js', array('jquery-min'), "5.4.0", 'all');
  wp_enqueue_script("Select2", plugin_dir_url(__FILE__) . 'js/select2.min.js', array('jquery-min'), "4.1.0", false);
  wp_enqueue_script("Datepicker", "https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/js/bootstrap-datepicker.min.js", array('jquery-min'), "2.0", false);
}

require_once plugin_dir_path(dirname(__FILE__)) . 'admin/ajax-functions.php';

function home_page()
{
  include_once('views/home.php');
}

function home_ventas()
{
  include_once('views/ventas.php');
}

function home_clientes()
{
  include_once('views/clientes.php');
}

function home_items()
{
  include_once('views/items.php');
}
