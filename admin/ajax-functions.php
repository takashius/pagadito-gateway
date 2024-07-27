<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://erdesarrollo.com.ve
 * @since      1.2.5
 *
 * @package    Er_Pagadito_Gateway
 * @subpackage Er_Pagadito_Gateway/admin
 * @author     Erick Hernandez <erick@erdearrollo.com.ve>
 */

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

add_action('wp_ajax_get_ajax_transactions', 'get_ajax_transactions');
function get_ajax_transactions()
{
  [$results, $total_results, $total_pages, $page, $results_per_page] = get_results(true);

  wp_send_json(array(
    "data" => $results,
    "total_results" => $total_results,
    "total_pages" => $total_pages,
    "page" => $page,
    "results_per_page" => $results_per_page
  ));

  wp_die();
}

add_action('wp_ajax_generar_csv', 'generar_csv');
function generar_csv()
{
  $spreadsheet = new Spreadsheet();
  $sheet = $spreadsheet->getActiveSheet();
  $items = [];

  [$results, $total_results] = get_results(true);

  $headers = [
    'Monto', 'Moneda', 'merchantReferenceId', 'Nombre', 'Apellido', 'IP',
    'Autorizacion', 'http_code', 'response_code', 'Mensaje de respuesta',
    'Fecha peticion', 'Fecha de pago', 'Origen', 'Fecha'
  ];
  $sheet->fromArray($headers, NULL, 'A1');
  foreach ($results as $result) {
    $items[] = array(
      $result->amount,
      $result->currency,
      $result->merchantReferenceId,
      $result->firstName,
      $result->lastName,
      $result->ip,
      $result->authorization,
      $result->http_code,
      $result->response_code,
      $result->response_message,
      $result->request_date,
      $result->paymentDate,
      $result->origin,
      $result->date
    );
  }
  $sheet->fromArray($items, NULL, 'A2');

  $sheet->getColumnDimension('A')->setWidth(15);
  $sheet->getColumnDimension('B')->setWidth(10);
  $sheet->getColumnDimension('C')->setWidth(30);
  $sheet->getColumnDimension('D')->setWidth(20);
  $sheet->getColumnDimension('E')->setWidth(20);
  $sheet->getColumnDimension('F')->setWidth(15);
  $sheet->getColumnDimension('G')->setWidth(15);
  $sheet->getColumnDimension('H')->setWidth(10);
  $sheet->getColumnDimension('I')->setWidth(15);
  $sheet->getColumnDimension('J')->setWidth(40);
  $sheet->getColumnDimension('K')->setWidth(20);
  $sheet->getColumnDimension('L')->setWidth(20);
  $sheet->getColumnDimension('M')->setWidth(10);
  $sheet->getColumnDimension('N')->setWidth(20);

  $headerStyle = [
    'fill' => [
      'fillType' => Fill::FILL_SOLID,
      'startColor' => [
        'argb' => Color::COLOR_BLUE // Color de fondo amarillo
      ]
    ],
    'font' => [
      'bold' => true,
      'color' => [
        'argb' => Color::COLOR_WHITE // Color del texto blanco
      ]
    ]
  ];
  $sheet->getStyle('A1:N1')->applyFromArray($headerStyle);

  // Crear el escritor de Excel
  $writer = new Xlsx($spreadsheet);
  $filename = 'hello_world.xlsx';

  // Enviar encabezados para la descarga del archivo
  header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
  header('Content-Disposition: attachment; filename="' . $filename . '"');
  header('Cache-Control: max-age=0');

  // Guardar el archivo en la salida
  $writer->save('php://output');
  exit;
}

function get_results($paginated)
{
  global $wpdb;
  $tablaOperations = $wpdb->prefix . "er_pagadito_operations";
  $where = [];
  $params = [];

  if ($_POST['date_to'] && $_POST['date_from']) {
    $date_to = dateConvert($_POST['date_to']);
    $date_from = dateConvert($_POST['date_from']);
    $where[] = " `date` >= %s AND `date` <= %s";
    $params[] = $date_to;
    $params[] = $date_from;
  }

  if ($_POST['origin']) {
    $origin = $_POST['origin'];
    $where[] = " `origin` = %s";
    $params[] = $origin;
  }

  if ($_POST['http_code']) {
    $http_code = $_POST['http_code'];
    $where[] = " `http_code` = %s";
    $params[] = $http_code;
  }

  if ($_POST['pattern']) {
    $pattern = '%' . $wpdb->esc_like($_POST['pattern']) . '%';
    $where[] = " (`firstName` LIKE %s OR `lastName` LIKE %s OR `response_message` LIKE %s)";
    $params[] = $pattern;
    $params[] = $pattern;
    $params[] = $pattern;
  }

  if ($paginated) {
    $results_per_page = isset($_POST['results_per_page']) ? intval($_POST['results_per_page']) : 10;
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $offset = ($page - 1) * $results_per_page;
  }

  $sql = "SELECT * FROM `" . $tablaOperations . "`";
  if (!empty($where)) {
    $sql .= " WHERE " . implode(' AND ', $where);
  }
  if ($paginated) {
    $sql .= " LIMIT %d OFFSET %d";
    $params[] = $results_per_page;
    $params[] = $offset;
  }
  $query = $wpdb->prepare($sql, ...$params);
  $results = $wpdb->get_results($query);
  $total_results = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `" . $tablaOperations . "`" . (!empty($where) ? " WHERE " . implode(' AND ', $where) : ''), ...array_slice($params, 0, -2)));

  if ($paginated) {
    $total_pages = ceil($total_results / $results_per_page);
    return [$results, $total_results, $total_pages, $page, $results_per_page];
  } else {
    return [$results, $total_results];
  }
}

function dateConvert($date)
{
  $parts = explode('/', $date);

  $dateConverted = $parts[2] . '-' . $parts[1] . '-' . $parts[0];

  return $dateConverted;
}