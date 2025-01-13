<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>:::: Challenge executed ::::</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
</head>

<body>
  <div class="row">
    <div class="container">
      <div class="row bg-primary">
        <div class="col text-center">
          <h1>Challenge enviado</h1>
        </div>
      </div>
      <div id="step1" class="row px-4 py-5">

        <button class="btn btn-primary" type="button" disabled>
          <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
          Procesando Autorizacion...
        </button>

      </div>
    </div>
  </div> <!-- fin de container -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>

<script type="text/javascript">
var token =
  "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MSwiaXNzdWVkX2F0IjoxNzMyODM1MjAwfQ.2B17dlF6oxxMBxfi85l5UzAdCa0xX9QQRLfeLccafw4";

var payload = {
  token: localStorage.getItem('transactionToken'),
  transactionId: localStorage.getItem('id_transaction'),
};

$(document).ready(function() {
  $.ajax({
    // url: "https://redwtele.com/wp-json/pagadito/v1/validate_card",
    url: "http://testwoocommerce.local/wp-json/pagadito/v1/validate_card",
    method: "POST",
    headers: {
      Authorization: token,
      "Content-Type": "application/json",
    },
    data: JSON.stringify(payload),
    success: function(response) {
      console.log(response);
      if (response.pagadito_http_code == 200) {
        if (response.pagadito_response.response_code == 'PG200-00') {
          let msj = "Pago procesado con exito.\n"

          msj += "\n\t Autorizacion: " + response.pagadito_response.customer_reply.authorization
          msj += "\n\t ID Transaccion: " + response.pagadito_response.customer_reply.merchantTransactionId
          msj += "\n\t Total: " + response.pagadito_response.customer_reply.totalAmount

          alert(msj);
        } else {
          alert("no se pudo procesar el pago");
        }
      } else {
        console.log(response.pagadito_response.customer_reply);

        let msj = "No se pudo procesar el pago: \n"
        msj += "\n\t " + response.pagadito_response.customer_reply
        alert(msj);
      }
    },
    error: function(jqXHR, textStatus, errorThrown) {
      console.log("Error:", jqXHR.responseText);
      alert("Error en la operación. Revisa la consola para más detalles.");
    },
  });

});
</script>

</html>