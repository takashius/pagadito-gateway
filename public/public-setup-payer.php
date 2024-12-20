<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Prueba de Setup Payer</title>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
  <h1>Prueba de Setup Payer</h1>
  <input type="text" id="cardNumber" placeholder="4000000000002701" />
  <button id="setupPayerBtn">Probar Setup Payer</button>
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
  // const urlBase = "https://redwtele.com/wp-json/pagadito/v1";
  const urlBase = "http://testwoocommerce.local/wp-json/pagadito/v1";
  var token =
    "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MSwiaXNzdWVkX2F0IjoxNzMyODM1MjAwfQ.2B17dlF6oxxMBxfi85l5UzAdCa0xX9QQRLfeLccafw4";
  var testData = {
    number: "4000000000002701",
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
    expiryMonth: "01",
    expiryYear: "2026",
    referenceId: "",
    request_id: "",
    returnUrl: "http://testwoocommerce.local/pagadito-test-3ds/return",
  };

  $(document).ready(function() {
    let referenceId = null;
    let request_id = null;

    $("#cardNumber").on("change", function() {
      testData.number = $(this).val();
      paymentData.cardNumber = $(this).val();
      localStorage.setItem("cardNumber", $(this).val());
    });

    $("#setupPayerBtn").click(function() {
      $("#loader").show();

      // Llamada al endpoint setup_payer
      $.ajax({
        url: urlBase + "/setup_payer",
        method: "POST",
        headers: {
          Authorization: token,
          "Content-Type": "application/json",
        },
        data: JSON.stringify(testData),
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
                url: urlBase + "/cobro",
                method: "POST",
                headers: {
                  Authorization: token,
                  "Content-Type": "application/json",
                },
                data: JSON.stringify(paymentData),
                success: function(response) {
                  console.log("Respuesta del servidor (cobro):", response);
                  if (response.pagadito_http_code == 200) {
                    alert(
                      "Cobro realizado con éxito. Revisa la consola para más detalles."
                    );
                  } else if (
                    response.pagadito_response.response_code == "PG402-05"
                  ) {
                    localStorage.setItem(
                      "id_transaction",
                      response.pagadito_response.customer_reply
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
                      response.pagadito_response.customer_reply.stepUpUrl;
                    form_step_up_form_jwt_input.value =
                      response.pagadito_response.customer_reply.accessToken;
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