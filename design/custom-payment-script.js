jQuery(document).ready(function ($) {
  let paymentData = null;

  const urlBase = `${data.site_url}/wp-json/pagadito/v1`;
  const token =
    "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MSwiaXNzdWVkX2F0IjoxNzMyODM1MjAwfQ.2B17dlF6oxxMBxfi85l5UzAdCa0xX9QQRLfeLccafw4";

  $.fn.inputFilter = function (callback, errMsg) {
    return this.on("input keydown keyup mousedown mouseup select contextmenu drop focusout", function (e) {
      if (callback(this.value)) {
        // Accepted value
        if (["keydown", "mousedown", "focusout"].indexOf(e.type) >= 0) {
          $(this).removeClass("input-error");
          this.setCustomValidity("");
        }
        this.oldValue = this.value;
        this.oldSelectionStart = this.selectionStart;
        this.oldSelectionEnd = this.selectionEnd;
      } else if (this.hasOwnProperty("oldValue")) {
        // Rejected value - restore the previous one
        $(this).addClass("input-error");
        this.setCustomValidity(errMsg);
        this.reportValidity();
        this.value = this.oldValue;
        this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
      } else {
        // Rejected value - nothing to restore
        this.value = "";
      }
    });
  };

  function formatExpiry(expiry) {
    var mon, parts, sep, year;

    parts = expiry.match(/^\D*(\d{1,2})(\D+)?(\d{1,4})?/);
    if (!parts) {
      return '';
    }

    mon = parts[1] || '';
    sep = parts[2] || '';
    year = parts[3] || '';
    if (year.length > 0) {
      sep = '/';
    } else if (sep === ' /') {
      mon = mon.substring(0, 1);
      sep = '';
    } else if (mon.length === 2 || sep.length > 0) {
      sep = '/';
    } else if (
      mon.length === 1 &&
      (mon !== '0' && mon !== '1')
    ) {
      mon = '0' + mon;
      sep = '/';
    }

    return mon + sep + year;
  }

  $("#cc-number, #cc-cvv").inputFilter(function (value) {
    return /^\d*$/.test(value); // Allow digits only, using a RegExp
  }, "Only digits allowed");

  $("#cc-expiration").inputFilter(function (value) {
    return /^[0-9]+(\/[0-9]+)*$/.test(value); // Allow digits only, using a RegExp
  }, "Only digits allowed");

  $('body').on('click paste keyup change', '#cc-expiration', function () {
    $(this).val(formatExpiry($(this).val()));
  });

  $('body').on('click', '#validate_payer', function () {
    const ccName = $('#cc-name').val();
    const ccNumberValid = $('#cc-number').hasClass('valid');
    const ccExpirationValid = /^[0-9]{2}\/[0-9]{4}$/.test($('#cc-expiration').val()) && parseInt($(
      '#cc-expiration').val().split('/')[0]) <= 12;
    const ccCvvValid = /^[0-9]{3}$/.test($('#cc-cvv').val());

    if (ccName == '' || ccName == undefined) {
      $('#cc-name').addClass('invalid');
    } else {
      $('#cc-name').removeClass('invalid');
    }

    if (!ccNumberValid) {
      $('#cc-number').addClass('invalid');
    } else {
      $('#cc-number').removeClass('invalid');
    }

    if (!ccExpirationValid) {
      $('#cc-expiration').addClass('invalid');
    } else {
      $('#cc-expiration').removeClass('invalid');
    }

    if (!ccCvvValid) {
      $('#cc-cvv').addClass('invalid');
    } else {
      $('#cc-cvv').removeClass('invalid');
    }

    if (ccName && ccNumberValid && ccExpirationValid && ccCvvValid) {
      setupPayer();
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error en los datos de la tarjeta',
        text: 'Por favor, verifica los datos de la tarjeta de crédito.'
      });
    }
  });

  const setupPayer = () => {
    $('.form-custom-pagadito').fadeOut('fast');
    $('#loader').fadeIn('slow');
    var formData = $('form[name="checkout"]').serializeArray();
    var formObject = {};
    $.each(formData, function (index, field) {
      formObject[field.name] = field.value;
    });
    if (!formObject.billing_first_name ||
      !formObject.billing_last_name ||
      !formObject.billing_email ||
      !formObject.billing_phone ||
      !formObject.billing_address_1 ||
      !formObject.billing_city ||
      !formObject.billing_state ||
      !formObject.billing_postcode ||
      !formObject.billing_country
    ) {
      $('#loader').fadeOut('fast');
      $('.form-custom-pagadito').fadeIn('slow');
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Debe completar el formulario con sus datos personales antes de poder realizar el pago.'
      });
      return false;
    }
    paymentData = {
      amount: data.cart_total,
      currency: "USD",
      mechantReferenceId: formObject['woocommerce-process-checkout-nonce'],
      firstName: formObject.billing_first_name,
      lastName: formObject.billing_last_name,
      email: formObject.billing_email,
      phone: formObject.billing_phone,
      address: formObject.billing_address_1,
      postalCode: formObject.billing_postcode,
      city: formObject.billing_city,
      state: formObject.billing_state,
      country: "604",
      ip: data.user_ip,
      holderName: formObject['cc-name'],
      cardNumber: formObject['cc-number'],
      cvv: formObject['cc-cvv'],
      expirationDate: formObject['cc-expiration'],
      referenceId: "",
      request_id: "",
      returnUrl: `${data.site_url}/pagadito-test-3ds/return`,
    };

    $.ajax({
      url: urlBase + "/setup_payer",
      method: "POST",
      headers: {
        Authorization: token,
        "Content-Type": "application/json",
      },
      data: JSON.stringify(paymentData),
      success: function (response) {
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

          var form = $("<form>", {
            method: "post",
            action: response.pagadito_response.deviceDataCollectionUrl,
            target: $("#iframeCardinalCollectionPagadito").attr("name"),
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
          $('#loader').fadeOut('fast');
          $('.form-custom-pagadito').fadeIn('slow');
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Ocurrio un error validando su tarjeta, por favor verifique nuevamente los datos ingresados.'
          });
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.log("Error:", jqXHR.responseText);
        $('#loader').fadeOut('fast');
        $('.form-custom-pagadito').fadeIn('slow');
        Swal.fire({
          icon: 'error',
          title: 'Error en la operación',
          text: 'Ocurrio un error validando su tarjeta, por favor verifique nuevamente los datos ingresados.'
        });
      },
    });
  }

  window.addEventListener(
    "message",
    function (event) {
      if (
        event.origin === "https://centinelapistag.cardinalcommerce.com"
      ) {
        let rsp = JSON.parse(event.data);

        if (rsp.MessageType === "profile.completed") {
          if (rsp.Status === true) {
            $.ajax({
              url: urlBase + "/customer",
              method: "POST",
              headers: {
                Authorization: token,
                "Content-Type": "application/json",
              },
              data: JSON.stringify({
                token: localStorage.getItem("transactionToken")
              }),
              success: function (response) {
                console.log("Respuesta del servidor (cobro):", response);
                if (response.pagadito_http_code == 200) {
                  // alert('revisa la consola');
                  $('#cc_authorization').val(response.pagadito_response.customer_reply.authorization);
                  $('#cc_request_id').val(response.pagadito_response.request_id);
                  $('form[name="checkout"]').submit();
                } else if (
                  response.pagadito_response.data.response_code == "PG402-05"
                ) {
                  $('#loader').fadeOut('fast');
                  $('.custom-card-pagadito').css('padding', '0');
                  $('#step_up_iframe_pagadito').fadeIn('slow');
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
                  form_step_up_form.submit();
                } else {
                  $('#loader').fadeOut('fast');
                  $('.form-custom-pagadito').fadeIn('slow');
                  Swal.fire({
                    icon: 'error',
                    title: 'Error en la operación de cobro',
                    text: response.pagadito_response.data.response_message
                  });
                }
              },
              error: function (jqXHR, textStatus, errorThrown) {
                console.log("Error en el cobro:", jqXHR.responseText);
                $('#loader').fadeOut('fast');
                $('.form-custom-pagadito').fadeIn('slow');
                Swal.fire({
                  icon: 'error',
                  title: 'Error en la operación de cobro',
                  text: 'Por favor, verifica los datos de la tarjeta de crédito.'
                });
              },
            });
          } else {
            $('#loader').fadeOut('fast');
            $('.form-custom-pagadito').fadeIn('slow');
            Swal.fire({
              icon: 'error',
              title: 'Error en la validación Challenge',
              text: 'Por favor, verifica los datos de la tarjeta de crédito.'
            });
          }
        }
      }
    },
    false
  );

  function handleIframeMessage(event) {
    if (event.data.paymentSuccess !== undefined) {
      if (event.data.paymentSuccess) {
        $('.custom-card-pagadito').css('padding', '3rem 3.5rem');
        $('#step_up_iframe_pagadito').fadeOut('fast');
        $('#loader').fadeIn('slow');
        $('#cc_authorization').val(event.data.authorization);
        $('#cc_request_id').val(event.data.request_id);
        $('form[name="checkout"]').submit();
      } else {
        $('.custom-card-pagadito').css('padding', '3rem 3.5rem');
        $('#step_up_iframe_pagadito').fadeOut('fast');
        $('#loader').fadeOut('fast');
        $('.form-custom-pagadito').fadeIn('slow');
        Swal.fire({
          icon: 'error',
          title: 'Error en la validación Challenge',
          text: 'Por favor, verifica los datos de la tarjeta de crédito.'
        });
      }
    }
  }

  window.addEventListener('message', handleIframeMessage, false);

});
