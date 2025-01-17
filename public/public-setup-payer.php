<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Prueba de Setup Payer</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
  <style>
  #iframeCardinalCollection {
    display: none;
  }

  #loader {
    display: none;
    border: 8px solid #f3f3f3;
    border-radius: 50%;
    border-top: 8px solid #3498db;
    width: 40px;
    height: 40px;
    -webkit-animation: spin 1s linear infinite;
    /* Safari */
    animation: spin 1s linear infinite;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
  }

  @-webkit-keyframes spin {
    0% {
      -webkit-transform: rotate(0deg);
    }

    100% {
      -webkit-transform: rotate(360deg);
    }
  }

  @keyframes spin {
    0% {
      transform: rotate(0deg);
    }

    100% {
      transform: rotate(360deg);
    }
  }
  </style>
</head>

<body>
  <div class="container mt-5">
    <h1 class="text-center mb-4">Prueba de Setup Payer</h1>
    <form id="setupPayerForm">
      <div class="form-group">
        <label for="cardNumber">Número de Tarjeta</label>
        <input type="text" class="form-control" id="cardNumber" placeholder="4456530000001005" required>
      </div>
      <div class="form-group">
        <label for="amountInputForm">Monto</label>
        <input type="text" class="form-control" id="amountInputForm" placeholder="33.948800" required>
      </div>
      <div class="form-group">
        <label for="referenceIdForm">ID de Referencia</label>
        <input type="text" class="form-control" id="referenceIdForm" placeholder="1188def5-c0ee-4586-aa51-499bab78efc4"
          required>
      </div>
      <div class="form-group">
        <label for="amountInputForm">Cliente</label>
        <select class="form-control" id="clientToken">
          <option
            value="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6NCwiaXNzdWVkX2F0IjoxNzM3MDk0OTk2fQ.xz6RiAh6lvNBai-dPtwDxVzmPBR1Pvpo88rFma2_otg">
            Ejemplo 1</option>
          <option
            value="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6NSwiaXNzdWVkX2F0IjoxNzM3MDg4NTkyfQ._19nbtkJ5sK9BzZurStI_DOWQ0Quofay79elVC_9OqQ">
            Ejemplo 2</option>
        </select>
      </div>
      <button type="button" class="btn btn-primary btn-block" id="setupPayerBtn">Probar Setup Payer</button>
    </form>
  </div>
  <iframe id="step_up_iframe" style="border: none; display: block; margin-top: 10px" height="600px" width="400px"
    name="stepUpIframe"></iframe>

  <form id="step_up_form" name="stepup" method="POST" target="stepUpIframe" action="">
    <input id="step_up_form_jwt_input" type="hidden" name="JWT" value="" />
    <input id="step_up_form_custom_data" type="hidden" name="MD" value="" />
  </form>
  <div id="loader"></div>
  <iframe id="iframeCardinalCollection" name="iframeCardinalCollection" style="display: none"></iframe>

  <script>
  // Token de autorización (reemplazar con un token válido)
  const urlBase = "/wp-json/pagadito/v1";
  let token =
    'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6NCwiaXNzdWVkX2F0IjoxNzM3MDg4NDkwfQ.Qxuna-7qew5wpBJW0_SBq6O8h38276R5ZMOFxB4tPcs';
  let BearerToken = `Bearer ${token}`;
  var testData = {
    number: "4456530000001005",
    expirationDate: "01/2026",
    cvv: "123",
    cardHolderName: "JOHN DOE",
  };

  var paymentData = {
    amount: "33.948800",
    currency: "USD",
    mechantReferenceId: "1188def5-c0ee-4586-aa51-499bab78efc4",
    firstName: "JOHN",
    lastName: "DOE",
    email: "pagador_sandbox@pagadito.com",
    phone: "2341410133",
    address: "avenidaamericasur162",
    postalCode: "13006",
    city: "trujillo",
    state: "NA",
    country: "604",
    ip: "179.60.205.141",
    holderName: testData.cardHolderName,
    cardNumber: testData.number,
    cvv: testData.cvv,
    expirationDate: testData.expirationDate,
    referenceId: "",
    request_id: "",
    returnUrl: "<? echo get_site_url() ?>/pagadito-test-3ds/return",
  };

  $(document).ready(function() {
    let referenceId = null;
    let request_id = null;

    $("#cardNumber").on("change", function() {
      testData.number = $(this).val();
      paymentData.cardNumber = $(this).val();
      localStorage.setItem("cardNumber", $(this).val());
    });

    $("#amountInputForm").on("change", function() {
      paymentData.amount = $(this).val();
    });

    $("#referenceIdForm").on("change", function() {
      paymentData.mechantReferenceId = $(this).val();
    });

    $("#clientToken").on("change", function() {
      token = $(this).val();
      BearerToken = `Bearer ${token}`;
    });

    $("#setupPayerBtn").click(function() {
      $("#loader").show();

      // Llamada al endpoint setup_payer
      $.ajax({
        url: urlBase + "/setup_payer",
        method: "POST",
        headers: {
          Authorization: BearerToken,
          "Content-Type": "application/json",
        },
        data: JSON.stringify(paymentData),
        success: function(response) {
          if (
            response.pagadito_response.deviceDataCollectionUrl &&
            response.pagadito_response.accessToken
          ) {
            localStorage.setItem(
              "referenceId",
              response.pagadito_response.referenceId
            );
            localStorage.setItem(
              "request_id",
              response.pagadito_response.request_id
            );
            localStorage.setItem(
              "transactionToken",
              response.pagadito_response.token
            );
            paymentData.referenceId =
              response.pagadito_response.referenceId;
            paymentData.request_id = response.pagadito_response.request_id;

            var form = $("<form>", {
              method: "post",
              action: response.pagadito_response.deviceDataCollectionUrl,
              target: $("#iframeCardinalCollection").attr("name"),
            });

            form.append(
              $("<input>", {
                type: "hidden",
                name: "JWT",
                value: response.pagadito_response.accessToken,
              })
            );

            form.appendTo(document.body).submit();
          } else {
            alert(
              "Falta URL o token en la respuesta. Revisa la consola para más detalles."
            );
          }
        },
        error: function(jqXHR, textStatus, errorThrown) {
          console.log("Error:", jqXHR.responseText);
          alert(
            "Error en la operación. Revisa la consola para más detalles."
          );
        },
      });
    });

    window.addEventListener(
      "message",
      function(event) {
        if (
          event.origin === "https://centinelapistag.cardinalcommerce.com"
        ) {
          let rsp = JSON.parse(event.data);

          if (rsp.MessageType === "profile.completed") {
            if (rsp.Status === true) {
              // Llamada al endpoint de cobro
              $.ajax({
                url: urlBase + "/customer",
                method: "POST",
                headers: {
                  Authorization: BearerToken,
                  "Content-Type": "application/json",
                },
                data: JSON.stringify({
                  token: localStorage.getItem("transactionToken")
                }),
                success: function(response) {
                  console.log("Respuesta del servidor (cobro):", response);
                  if (response.pagadito_http_code == 200) {
                    alert(
                      "Cobro realizado con éxito. Revisa la consola para más detalles."
                    );
                  } else if (
                    response.pagadito_response.data.response_code == "PG402-05"
                  ) {
                    localStorage.setItem(
                      "id_transaction",
                      response.pagadito_response.data.customer_reply
                      .id_transaction
                    );
                    // pending authentication step up challenge
                    let form_step_up_form =
                      document.querySelector("#step_up_form");
                    let form_step_up_form_jwt_input =
                      document.querySelector("#step_up_form_jwt_input");
                    let form_step_up_form_custom_data =
                      document.querySelector("#step_up_form_custom_data");

                    form_step_up_form.action =
                      response.pagadito_response.data.customer_reply.stepUpUrl;
                    form_step_up_form_jwt_input.value =
                      response.pagadito_response.data.customer_reply.accessToken;
                    form_step_up_form_custom_data.value =
                      JSON.stringify(paymentData);
                    form_step_up_form.submit();
                  }

                  $("#loader").hide();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                  console.log("Error en el cobro:", jqXHR.responseText);
                  alert(
                    "Error en la operación de cobro. Revisa la consola para más detalles."
                  );
                  $("#loader").hide();
                },
              });
            } else {
              alert(
                "Error en la validación de datos. Revisa la consola para más detalles."
              );
              $("#loader").hide();
            }
          }
        }
      },
      false
    );
  });
  </script>
</body>

</html>