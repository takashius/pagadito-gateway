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
  <li><strong>Id de Transacci&oacute;n:</strong> <?php echo $request_id; ?></li>
  <li><strong>Id de Autorizaci&oacute;n:</strong> <?php echo $authorization; ?></li>
</ul>

<?php
    $items = $order->get_items();
    $itemPrinted = [false, false, false];

    $productos = [
      'RECARGAS' => 8419,
      'DESARROLLO_SOFTWARE' => 636,
      'VPX_VIRTUAL' => 297,
      'CALLING_CARD' => 259,
      'TEST_PRODUCT' => 284
    ];

    foreach ($items as $item) {
      $product_id = $item->get_product_id();

      switch ($product_id) {
        case $productos['TEST_PRODUCT']:
          case $productos['VPX_VIRTUAL']:
          if (!$itemPrinted[1]) {
            $itemPrinted[1] = true; ?>
<h2>Características esenciales de tu producto: </h2>
<ul>
  <li><strong>Configuración en la Nube:</strong> No se requiere hardware físico, lo que reduce los costos de instalación
    y mantenimiento.
  </li>
  <li><strong>Escalabilidad:</strong> Escala fácilmente tu sistema telefónico según las necesidades de tu empresa,
    añadiendo o eliminando líneas sin complicaciones.
  </li>
  <li><strong>Funciones Avanzadas de Llamadas:</strong> Desvío de llamadas, buzón de voz, grabación de llamadas,
    conferencias, IVR (Respuesta de Voz Interactiva) y más.
  </li>
  <li><strong>Movilidad:</strong> Accede a tu sistema telefónico desde cualquier lugar a través de aplicaciones móviles
    y de escritorio.
  </li>
  <li><strong>Seguridad:</strong> Comunicaciones encriptadas y protocolos de seguridad avanzados para proteger tus
    datos.
  </li>
  <li><strong>Soporte al Cliente 24/7:</strong> Asistencia técnica y soporte al cliente disponible en cualquier momento.
  </li>
  <li><strong>Integración con CRM:</strong> Compatible con sistemas de gestión de relaciones con clientes (CRM) para una
    mayor eficiencia.
  </li>
  <li><strong>Costos Reducidos:</strong> Tarifas competitivas y sin costos ocultos, ideal para empresas de todos los
    tamaños.
  </li>
  <li><strong>Interfaz Amigable:</strong> Panel de control intuitivo y fácil de usar para gestionar tu sistema
    telefónico.
  </li>
  <li><strong>Reporte y Análisis:</strong> Herramientas de análisis y reportes para monitorear el rendimiento y
    optimizar la comunicación.
  </li>
</ul>
<?php
          }
          break;
        case $productos['DESARROLLO_SOFTWARE']:
          if (!$itemPrinted[2]) {
            $itemPrinted[2] = true; ?>
<h2>Características esenciales de tu producto: </h2>
<ol>
  <li><strong>Análisis del Mercado Objetivo:</strong>
    <ul>
      <li>Identificación y segmentación del público objetivo.</li>
      <li>Comportamiento de compra y preferencias del consumidor.</li>
    </ul>
  </li>
  <li><strong>Investigación de la Competencia:</strong>
    <ul>
      <li>Análisis de competidores directos e indirectos.</li>
      <li>Identificación de fortalezas, debilidades, oportunidades y amenazas (FODA).</li>
    </ul>
  </li>
  <li><strong>Tendencias del Mercado:</strong>
    <ul>
      <li>Estudio de tendencias actuales y proyecciones futuras.</li>
      <li>Identificación de nichos de mercado emergentes.</li>
    </ul>
  </li>
  <li><strong>Evaluación de Producto o Servicio:</strong>
    <ul>
      <li>Opinión del consumidor sobre productos existentes.</li>
      <li>Potencial de nuevos lanzamientos o mejoras.</li>
    </ul>
  </li>
  <li><strong>Análisis de Canales de Distribución y Comunicación:</strong>
    <ul>
      <li>Identificación de los canales más efectivos para llegar al cliente.</li>
      <li>Estrategias de comunicación y posicionamiento.</li>
    </ul>
  </li>
  <li><strong>Recomendaciones Estratégicas:</strong>
    <ul>
      <li>Propuestas concretas para optimizar campañas y estrategias.</li>
      <li>Plan de acción para la implementación de resultados obtenidos.</li>
    </ul>
  </li>
</ol>
<?php
          }
          break;
        case $productos['CALLING_CARD']:
        case $productos['RECARGAS']:
        default:
          if (!$itemPrinted[0]) {
            $itemPrinted[0] = true; ?>
<p>
  Gracias por elegir nuestras Calling Cards para mantenerte conectado con tus seres queridos en el extranjero. Nos
  complace informarte que hemos recibido tu pedido y el servicio será activado en un plazo de 24 horas.
</p>
<h2>Características esenciales de tu producto: </h2>
<ul>
  <li><strong>Recargas Flexibles:</strong> Elige la cantidad de minutos que necesitas según tus preferencias. Alta
    Calidad de Llamada: Disfruta de una experiencia de comunicación clara y sin interrupciones.
  </li>
  <li><strong>Tarifas Competitivas:</strong> Con precios accesibles y sin cargos ocultos, puedes estar tranquilo
    sabiendo que estás obteniendo el mejor valor.
  </li>
  <li><strong>Acceso Global:</strong> Realiza llamadas a cualquier parte del mundo sin complicaciones.</li>
  <li><strong>Compatibilidad:</strong> Utiliza nuestras tarjetas en teléfonos móviles y fijos con facilidad.
  </li>
  <li><strong>Recarga Online:</strong> Recarga tus minutos de forma rápida y segura desde nuestro portal en línea.
  </li>
  <li><strong>Soporte al Cliente:</strong> Si tienes alguna consulta, nuestro equipo está disponible para asistirte.
  </li>
</ul>
<p>
<h2>Números para usar con tu Calling Card: </h2>
Para realizar llamadas, marca el número + 1 (305) 9995383.<br />
También puedes usar nuestro número de atención toll-free: +1 (888) 2078625 para llamadas sin costo adicional.<br />
Recuerda que el servicio se activará dentro de las 24 horas siguientes a tu compra, y podrás comenzar a disfrutar de
todas las ventajas que ofrecen nuestras Calling Cards.
</p>
<?php
          }
          break;
      }
    }
    ?>
<p>
  Te agradecemos nuevamente por confiar en nuestros servicios. Estamos aquí para asegurarnos de que tu experiencia sea
  siempre satisfactoria y sin contratiempos.
</p>
<?php
  }
}