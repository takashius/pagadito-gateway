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
      Gracias por elegir nuestras Calling Cards para mantenerte conectado con tus seres queridos en el extranjero. Nos
      complace informarte que hemos recibido tu pedido y el servicio será activado en un plazo de 24 horas.
    </p>
    <h2>Características esenciales de tu producto: </h2>
    <p>
    <ul>
      <li><strong>Recargas Flexibles:</strong> Elige la cantidad de minutos que necesitas según tus preferencias.
        Alta Calidad de Llamada: Disfruta de una experiencia de comunicación clara y sin interrupciones. </li>
      <li><strong>Tarifas Competitivas:</strong> Con precios accesibles y sin cargos ocultos, puedes estar tranquilo
        sabiendo que estás obteniendo el mejor valor. </li>
      <li><strong>Acceso Global:</strong> Realiza llamadas a cualquier parte del mundo sin complicaciones. </li>
      <li><strong>Compatibilidad:</strong> Utiliza nuestras tarjetas en teléfonos móviles y fijos con facilidad. </li>
      <li><strong>Recarga Online:</strong> Recarga tus minutos de forma rápida y segura desde nuestro portal en línea. </li>
      <li><strong>Soporte al Cliente:</strong> Si tienes alguna consulta, nuestro equipo está disponible para asistirte.
      </li>
    </ul>
    </p>
    <p>
    <h2>Números para usar con tu Calling Card: </h2>
    Para realizar llamadas, marca el número +1 (305) 6990993.<br />
    También puedes usar nuestro número de atención toll-free: +1 (888) 2078625 para llamadas sin costo adicional.<br />
    Recuerda que el servicio se activará dentro de las 24 horas siguientes a tu compra, y podrás comenzar a disfrutar de
    todas las ventajas que ofrecen nuestras Calling Cards.<br /><br />
    </p>
    <p>
      Te agradecemos nuevamente por confiar en nuestros servicios. Estamos aquí para asegurarnos de que tu experiencia sea
      siempre satisfactoria y sin contratiempos.
    </p>
<?php
  }
}
