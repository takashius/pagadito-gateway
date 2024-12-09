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
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

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

add_action('wp_ajax_excel_report', 'excel_report');
function excel_report()
{
  $spreadsheet = new Spreadsheet();
  $sheet = $spreadsheet->getActiveSheet();
  $items = [];

  [$results, $total_results] = get_results(false);

  $headers = [
    'Monto',
    'Moneda',
    'merchantReferenceId',
    'Nombre',
    'Apellido',
    'IP',
    'Autorizacion',
    'http_code',
    'response_code',
    'Mensaje de respuesta',
    'Fecha peticion',
    'Fecha de pago',
    'Origen',
    'Fecha'
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
        'argb' => Color::COLOR_BLUE
      ]
    ],
    'font' => [
      'bold' => true,
      'color' => [
        'argb' => Color::COLOR_WHITE
      ]
    ]
  ];
  $sheet->getStyle('A1:N1')->applyFromArray($headerStyle);

  $writer = new Xlsx($spreadsheet);
  $filename = 'excel_report.xlsx';

  header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
  header('Content-Disposition: attachment; filename="' . $filename . '"');
  header('Cache-Control: max-age=0');

  $writer->save('php://output');
  exit;
}

add_action('wp_ajax_pdf_report', 'pdf_report');
function pdf_report()
{

  $spreadsheet = new Spreadsheet();
  $sheet = $spreadsheet->getActiveSheet();
  $items = [];

  [$results, $total_results] = get_results(false);

  $headers = [
    'Monto',
    'Moneda',
    'Nombre',
    'IP',
    'Autorizacion',
    'http_code',
    'Mensaje de respuesta',
    'Origen',
    'Fecha'
  ];
  $sheet->fromArray($headers, NULL, 'A1');
  foreach ($results as $result) {
    $items[] = array(
      $result->amount,
      $result->currency,
      $result->firstName . ' ' . $result->lastName,
      $result->ip,
      $result->authorization,
      $result->http_code,
      $result->response_message,
      $result->origin,
      $result->date
    );
  }
  $sheet->fromArray($items, NULL, 'A2');

  $sheet->getColumnDimension('A')->setWidth(5);
  $sheet->getColumnDimension('B')->setWidth(6);
  $sheet->getColumnDimension('C')->setWidth(20);
  $sheet->getColumnDimension('D')->setWidth(12);
  $sheet->getColumnDimension('E')->setWidth(10);
  $sheet->getColumnDimension('F')->setWidth(8);
  $sheet->getColumnDimension('G')->setWidth(40);
  $sheet->getColumnDimension('H')->setWidth(10);
  $sheet->getColumnDimension('I')->setWidth(20);

  $headerStyle = [
    'fill' => [
      'fillType' => Fill::FILL_SOLID,
      'startColor' => [
        'argb' => Color::COLOR_BLUE
      ]
    ],
    'font' => [
      'bold' => true,
      'color' => [
        'argb' => Color::COLOR_WHITE
      ]
    ]
  ];
  $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);
  $sheet->getStyle('A1:I' . (count($results) + 1))->getFont()->setSize(8);
  $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
  $sheet->getStyle('A1:I' . (count($results) + 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
  $sheet->getStyle('A1:I' . (count($results) + 1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
  $sheet->getStyle('A1:I' . (count($results) + 1))->getAlignment()->setWrapText(true);

  $writer = new Dompdf($spreadsheet);
  $filename = 'pdf_report.pdf';
  header('Content-Type: application/pdf');
  header('Content-Disposition: attachment; filename="' . $filename . '"');
  header('Cache-Control: max-age=0');

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

  if ($_POST['customer']) {
    $client_id = intval($_POST['customer']);
    $where[] = " `client_id` = %d";
    $params[] = $client_id;
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

add_action('wp_ajax_get_clients', 'get_clients');
function get_clients()
{
  global $wpdb;
  $tableClients = $wpdb->prefix . "er_pagadito_clients";
  $results = $wpdb->get_results("SELECT ID, name FROM $tableClients WHERE deleted = 0");
  wp_send_json($results);
}
