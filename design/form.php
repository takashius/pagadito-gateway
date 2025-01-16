<div class="card custom-card-pagadito">
  <!-- Loader de Bootstrap -->
  <div id="loader" class="text-center" style="display: none;">
    <div class="spinner-border" role="status">
      <span class="sr-only">Loading...</span>
    </div>
    <p>Cargando...</p>
  </div>
  <iframe id="step_up_iframe_pagadito" style="border: none; display: none; height: 105vh;" height="400px"
    name="stepUpIframe"></iframe>

  <div class="row form-custom-pagadito">
    <div class="col-md-12 mb-12">
      <label for="cc-name">Nombre en la tarjeta</label>
      <input type="text" class="form-control" id="cc-name" name="cc-name" placeholder="" required="">
      <small class="text-muted">Nombre completo como se muestra en la tarjeta.</small>
      <div class="invalid-feedback">
        El nombre es requerido
      </div>
    </div>
    <div class="col-md-12 mb-12">
      <label for="cc-number">Número de Tarjeta de Crédito</label>
      <input type="text" maxlength="16" class="form-control" style="width: 100%; height: 35px;" id="cc-number"
        name="cc-number" placeholder="1234 5678 9012 3456" required="">
      <div class="invalid-feedback">
        El número de la tarjeta es requerido
      </div>
    </div>
  </div>
  <div class="row form-custom-pagadito">
    <div class="col-md-6 mb-3">
      <label for="cc-expiration">Vencimiento</label>
      <input type="text" maxlength="7" class="form-control" id="cc-expiration" name="cc-expiration"
        placeholder="MM/YYYY" required="">
      <div class="invalid-feedback">
        El vencimiento es requerido
      </div>
    </div>
    <div class="col-md-6 mb-3">
      <label for="cc-cvv">CVV</label>
      <input type="text" class="form-control" id="cc-cvv" name="cc-cvv" placeholder="123" maxlength="3" minlength="3"
        required="">
      <div class="invalid-feedback">
        El código de seguridad es requerido
      </div>
    </div>
  </div>
  <input id="cc_request_id" type="hidden" name="cc_request_id" value="" />
  <input id="cc_authorization" type="hidden" name="cc_authorization" value="" />
  <button type="button" class="button alt  form-custom-pagadito" name="validate_payer" id="validate_payer"
    value="Validar pago" data-value="Validar pago">Validar pago</button>
</div>
<iframe id="iframeCardinalCollectionPagadito" name="iframeCardinalCollectionPagadito" style="display: none"></iframe>
<form id="step_up_form" name="stepup" method="POST" target="stepUpIframe" action="">
  <input id="step_up_form_jwt_input" type="hidden" name="JWT" value="" />
  <input id="step_up_form_custom_data" type="hidden" name="MD" value="" />
</form>

<script>
  $(document).ready(function() {
    const submitButton = $('form[name="checkout"]').find('button[type="submit"]');
    submitButton.prop('disabled', true);

    $('#cc-number').on('input', function() {
      var ccNumber = $(this).val();

      if (ccNumber.length > 0) {
        $(this).validateCreditCard(function(result) {
          console.log('result', result);
          if (result.valid) {
            $('#cc-number').addClass('valid').removeClass('invalid');
            $('#cc-number').addClass(result.card_type.name);
          } else {
            $('#cc-number').removeClass('valid').addClass('invalid');
          }
        });
      } else {
        $('#cc-number').removeClass('valid invalid'); // Eliminar clases si no hay dígitos
      }
    });


  });
</script>