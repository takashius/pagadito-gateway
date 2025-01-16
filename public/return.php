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
      <div id="loader" class="text-center">
        <div class="spinner-border" role="status">
          <span class="sr-only">Loading...</span>
        </div>
        <p>Cargando...</p>
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
  function sendPaymentStatus(status, authorization = null, request_id = null) {
    window.parent.postMessage({
      paymentSuccess: status,
      authorization: authorization,
      request_id: request_id
    }, '*');
  }
  $.ajax({
    url: "<? echo get_site_url() ?>/wp-json/pagadito/v1/validate_card",
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
          sendPaymentStatus(true, response.pagadito_response.customer_reply.authorization, response
            .pagadito_response.request_id);
        } else {
          sendPaymentStatus(false);
        }
      } else {
        console.log(response.pagadito_response.customer_reply);
        sendPaymentStatus(false);
      }
    },
    error: function(jqXHR, textStatus, errorThrown) {
      console.log("Error:", jqXHR.responseText);
      sendPaymentStatus(false);
    },
  });

});
</script>

</html>