<style>
body {
  background: #ddd;
  min-height: 100vh;
  vertical-align: middle;
  display: flex;

}

.card {
  margin: auto;
  width: auto;
  padding: 3rem 3.5rem;
  box-shadow: 0 6px 20px 0 rgba(0, 0, 0, 0.19);
}

.mt-50 {
  margin-top: 50px
}

.mb-50 {
  margin-bottom: 50px
}


@media(max-width:767px) {
  .card {
    width: 90%;
    padding: 1.5rem;
  }
}

@media(height:1366px) {
  .card {
    width: 90%;
    padding: 8vh;
  }
}

.card-title {
  font-weight: 700;
  font-size: 2.5em;
}

.nav {
  display: flex;
}

.nav ul {
  list-style-type: none;
  display: flex;
  padding-inline-start: unset;
  margin-bottom: 6vh;
}

.nav li {
  padding: 1rem;
}

.nav li a {
  color: black;
  text-decoration: none;
}

.active {
  border-bottom: 2px solid black;
  font-weight: bold;
}

input {
  border: none;
  outline: none;
  font-size: 1rem;
  font-weight: 600;
  color: #000;
  width: 100%;
  min-width: unset;
  background-color: transparent;
  border-color: transparent;
  margin: 0;
}

form a {
  color: grey;
  text-decoration: none;
  font-size: 0.87rem;
  font-weight: bold;
}

form a:hover {
  color: grey;
  text-decoration: none;
}

/* form .row {
  margin: 0;
  overflow: hidden;
} */

/* form .row-1 {
  border: 1px solid rgba(0, 0, 0, 0.137);
  padding: 0.5rem;
  outline: none;
  width: 100%;
  min-width: unset;
  border-radius: 5px;
  background-color: rgba(221, 228, 236, 0.301);
  border-color: rgba(221, 228, 236, 0.459);
  margin: 2vh 0;
  overflow: hidden;
}

form .row-2 {
  border: none;
  outline: none;
  background-color: transparent;
  margin: 0;
  padding: 0 0.8rem;
}

form .row .row-2 {
  border: none;
  outline: none;
  background-color: transparent;
  margin: 0;
  padding: 0 0.8rem;
}

form .row .col-2,
.col-7,
.col-3 {
  display: flex;
  align-items: center;
  text-align: center;
  padding: 0 1vh;
}

form .row .col-2 {
  padding-right: 0;
} */

#card-header {
  font-weight: bold;
  font-size: 0.9rem;
}

#card-inner {
  font-size: 0.7rem;
  color: gray;
}

.three .col-7 {
  padding-left: 0;
}

.three {
  overflow: hidden;
  justify-content: space-between;
}

.three .col-2 {
  border: 1px solid rgba(0, 0, 0, 0.137);
  padding: 0.5rem;
  outline: none;
  width: 100%;
  min-width: unset;
  border-radius: 5px;
  background-color: rgba(221, 228, 236, 0.301);
  border-color: rgba(221, 228, 236, 0.459);
  margin: 2vh 0;
  width: fit-content;
  overflow: hidden;
}

.three .col-2 input {
  font-size: 0.7rem;
  margin-left: 1vh;
}

.btn {
  width: 100%;
  background-color: rgb(65, 202, 127);
  border-color: rgb(65, 202, 127);
  color: white;
  justify-content: center;
  padding: 2vh 0;
  margin-top: 3vh;
}

.btn:focus {
  box-shadow: none;
  outline: none;
  box-shadow: none;
  color: white;
  -webkit-box-shadow: none;
  -webkit-user-select: none;
  transition: none;
}

.btn:hover {
  color: white;
}

input:focus::-webkit-input-placeholder {
  color: transparent;
}

input:focus:-moz-placeholder {
  color: transparent;
}

input:focus::-moz-placeholder {
  color: transparent;
}

input:focus:-ms-input-placeholder {
  color: transparent;
}

#cc-number {
  background-image: url(https://jquerycreditcardvalidator.com/images/images.png), url(https://jquerycreditcardvalidator.com/images/images.png);
  background-position: 2px -121px, 240px -61px;
  background-size: 120px 361px, 120px 361px;
  background-repeat: no-repeat;
  padding-left: 54px;
  width: 225px;
}

#cc-number.valid.visa {
  background-position: 2px -163px, 240px -87px;
}

#cc-number.visa_electron {
  background-position: 2px -205px, 240px -61px
}

#cc-number.mastercard {
  background-position: 2px -247px, 240px -61px
}

#cc-number.maestro {
  background-position: 2px -289px, 240px -61px
}

#cc-number.discover {
  background-position: 2px -331px, 240px -61px
}

#cc-number.valid.visa {
  background-position: 2px -163px, 240px -87px
}

#cc-number.valid.visa_electron {
  background-position: 2px -205px, 240px -87px
}

#cc-number.valid.mastercard {
  background-position: 2px -247px, 240px -87px
}

#cc-number.valid.maestro {
  background-position: 2px -289px, 240px -87px
}

#cc-number.valid.discover {
  background-position: 2px -331px, 240px -87px
}

.invalid {
  border: 2px solid red !important;
}

.valid {
  border: 2px solid green !important;
}
</style>
<script type="text/javascript">
$(document).ready(function() {
  $.fn.inputFilter = function(callback, errMsg) {
    return this.on("input keydown keyup mousedown mouseup select contextmenu drop focusout", function(e) {
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

  $("#cc-number, #cc-cvv").inputFilter(function(value) {
    return /^\d*$/.test(value); // Allow digits only, using a RegExp
  }, "Only digits allowed");

  $("#cc-expiration").inputFilter(function(value) {
    return /^[0-9]+(\/[0-9]+)*$/.test(value); // Allow digits only, using a RegExp
  }, "Only digits allowed");

  $('#cc-number').validateCreditCard(function(result) {
    if (result.valid) {
      $('#cc-number').addClass('valid');
      // $('#cc-number').removeClass('invalid');
      $('#cc-number').addClass(result.card_type.name);
    } else {
      // $('#cc-number').addClass('invalid');
      $('#cc-number').removeClass('valid');
    }
  })
  $('body')
    .on('click paste keyup change', '#cc-expiration', function() {
      $(this).val(formatExpiry($(this).val()));

    });
})
</script>

<div class="card ">
  <div class="row">
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
  <div class="row">
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
</div>