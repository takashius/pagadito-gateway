<?php

/**
 * Additional elements of mail sending
 *
 * @link       https://erdesarrollo.com.ve
 * @since      1.0.1
 *
 * @package    ErPagadito_gateway
 * @subpackage ErPagadito_gateway/includes
 */

/**
 * Additional elements of mail sending.
 *
 * This class defines the code necessary to modify the information in the purchase email.
 *
 * @since      1.0.1
 * @package    ErPagadito_gateway
 * @subpackage ErPagadito_gateway/includes
 * @author     Erick Hernandez <erick@erdesarrollo.com.ve>
 */
class ErPagadito_gateway_Email_Extra
{

  /**
   * Add additional information
   *
   * Payment fields are added to the Pagadito gateway.
   *
   * @since    1.0.1
   */
  public static function updateData($order, $sent_to_admin, $plain_text)
  {
    $authorization = $order->get_meta('authorization');
    $request_id = $order->get_meta('request_id'); ?>
<h2>Pagadito </h2>
<ul>
  <li><strong>Id de Transacci&oacute;n</strong> <?php echo $request_id ?></li>
  <li><strong>Id de Autorizaci&oacute;n</strong> <?php echo $authorization ?></li>
</ul>
<p>
  Gracias por tu compra, en este momento nuestro sistema esta procesando la creación de tu servicio en menos de 24 horas
  tendrás en tu correo los datos para poder utilizar tu servicio. Atentamente RedW Telecom
</p>
<?php
  }
}