$(document).ready(function () {
  let client = false;
  $('.general').select2({
    theme: "bootstrap",
    minimumResultsForSearch: Infinity
  });

  $('#date_from').datepicker({
    format: 'dd/mm/yyyy',
    startDate: $('#date_from').datepicker('getDate'),
    todayBtn: true,
    toggleActive: true,
    autoclose: true
  });

  $('#date_to').datepicker({
    format: 'dd/mm/yyyy',
    endDate: '0D',
    todayBtn: true,
    toggleActive: true,
    autoclose: true
  });

  $('#date_to').on('change', function () {
    const dateSelected = $(this).val();
    if (compararFechas(dateSelected, $('#date_from').val())) {
      $('#date_from').datepicker('setDate', dateSelected);
    }
    $('#date_from').datepicker('setStartDate', dateSelected);
  })

  $('.input-daterange input').each(function () {
    $(this).datepicker('clearDates');
  });

  $('#perPag').on('select2:close', function () {
    getTransactions(1);
  });

  $('#download').on('select2:close', function () {
    const params = {
      pattern: $('#pattern').val(),
      date_from: $('#date_from').val(),
      date_to: $('#date_to').val(),
      origin: $('#origin').val(),
      http_code: $('#http_response').val(),
    };

    const data = {
      date_from: params.date_from,
      date_to: params.date_to
    };

    if (params.origin != 'Todos') data.origin = params.origin;
    if (params.http_code != 'Todos') data.http_code = params.http_code;
    if (params.pattern) data.pattern = params.pattern;

    const typeDownload = $(this).val();

    if (typeDownload === 'Excel') {
      $('#loader_new-er-control').attr('style', 'visibility: visible;');
      data.action = 'excel_report';
      $.ajax({
        url: ajaxurl,
        type: 'POST',
        data,
        xhrFields: {
          responseType: 'blob'
        },
        success: function (data) {
          var a = document.createElement('a');
          var url = window.URL.createObjectURL(data);
          a.href = url;
          a.download = 'report.xlsx';
          document.body.appendChild(a);
          a.click();
          window.URL.revokeObjectURL(url);
          $('#download').val('Seleccione uno').trigger('change');
          $('#loader_new-er-control').attr('style', 'visibility: hidden;');
        },
        error: function (xhr, status, error) {
          console.error('Error al generar el archivo CSV:', error);
          $('#download').val('Seleccione uno').trigger('change');
          $('#loader_new-er-control').attr('style', 'visibility: hidden;');
        }
      });
    } else if (typeDownload === 'PDF') {
      $('#loader_new-er-control').attr('style', 'visibility: visible;');
      data.action = 'pdf_report';
      $.ajax({
        url: ajaxurl,
        type: 'POST',
        data,
        xhrFields: {
          responseType: 'blob'
        },
        success: function (data) {
          var a = document.createElement('a');
          var url = window.URL.createObjectURL(data);
          a.href = url;
          a.download = 'report.pdf';
          document.body.appendChild(a);
          a.click();
          window.URL.revokeObjectURL(url);
          $('#download').val('Seleccione uno').trigger('change');
          $('#loader_new-er-control').attr('style', 'visibility: hidden;');
        },
        error: function (xhr, status, error) {
          console.error('Error al generar el archivo PDF:', error);
          $('#download').val('Seleccione uno').trigger('change');
          $('#loader_new-er-control').attr('style', 'visibility: hidden;');
        }
      });
    }
  })

  $('#search').on('click', function () {
    const params = {
      pattern: $('#pattern').val(),
      date_from: $('#date_from').val(),
      date_to: $('#date_to').val(),
      customer: $('#customer').val(),
      http_code: $('#http_response').val(),
    }
    getTransactions(1, params);
  })

  function fetchClients() {
    const data = {
      action: 'get_clients'
    };

    $('#loader_new-er-control').attr('style', 'visibility: visible;');

    jQuery.post(ajaxurl, data, function (response) {
      const selectCustomer = $('#customer');
      response.forEach((client, index) => {
        const option = $('<option></option>').attr('value', client.ID).text(client.name);
        selectCustomer.append(option);
        if (index === 0) { option.prop('selected', true); }
      });
      client = response[0].ID;

      const params = {
        pattern: "",
        date_from: $('#date_from').val(),
        date_to: $('#date_to').val(),
        customer: client,
        http_code: $('#http_response').val(),
      }
      getTransactions(1, params);
    }).fail(function (e) {
      $('#loader_new-er-control').attr('style', 'visibility: hidden;');
      console.log('Error fetching clients:', e);
    });
  }

  const getTransactions = (page, search = null) => {
    $('#loader_new-er-control').attr('style', 'visibility: visible;');
    const results_per_page = $('#perPag option:selected').val();
    const data = {
      action: 'get_ajax_transactions',
      results_per_page,
      page
    }

    if (search) {
      data.date_from = search.date_from;
      data.date_to = search.date_to;
      if (search.origin != 'Todos') data.origin = search.origin;
      if (search.customer != 'Todos') data.customer = search.customer;
      if (search.http_code != 'Todos') data.http_code = search.http_code;
      if (search.pattern) data.pattern = search.pattern;
    }

    jQuery.post(ajaxurl, data, function (response) {
      $('#loader_new-er-control').attr('style', 'visibility: hidden;');
      $('#tableBodyReport').html('');
      response.data.map(item => {
        $('#tableBodyReport').append(`
        <tr>
          <td>${item.ip}</td>
          <td>${recortarString(`${item.firstName} ${item.lastName}`)}</td>
          <td>${item.currency}</td>
          <td>${item.http_code}</td>
          <td>${item.origin}</td>
          <td>${item.amount}</td>
          <td>${convertirFecha(item.date)}</td>
        </tr>
			`);
      });

      pagination(response.total_pages, response.page);

      if (response.data.length <= 0) {
        $('#tableBodyReport').append(`
          <tr class="empty">
            <th colspan="7" class="text-center">No se han agregado elementos</th>
          </tr>
        `);
      }
    }).catch(e => {
      $('#loader_new-er-control').attr('style', 'visibility: hidden;');
      console.log(e)
    })
  }

  $('body').on('click', '.page-link', function (e) {
    e.preventDefault();
    const page = $(this).data('page');
    if ($('#pattern').val() != $('#date_from').val()) {
      const params = {
        pattern: $('#pattern').val(),
        date_from: $('#date_from').val(),
        date_to: $('#date_to').val(),
        customer: $('#customer').val(),
        http_code: $('#http_response').val(),
      }
      getTransactions(page, params);
    } else {
      getTransactions(page);
    }
  })

  const pagination = (totalPages, currentPage) => {
    let paginationHtml = '';

    if (currentPage > 1) {
      paginationHtml += `<li class="page-item">
            <a href="#" class="page-link" data-page="${currentPage - 1}">Anterior</a>
        </li>`;
    } else {
      paginationHtml += `<li class="page-item disabled">
            <a class="page-link" href="#" tabindex="-1">Anterior</a>
        </li>`;
    }

    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, currentPage + 2);

    if (startPage > 1) {
      paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
      if (startPage > 2) {
        paginationHtml += `<li class="page-item disabled"><a class="page-link" href="#">...</a></li>`;
      }
    }

    for (let i = startPage; i <= endPage; i++) {
      if (i === currentPage) {
        paginationHtml += `<li class="page-item active">
                <a class="page-link" href="#">${i} <span class="sr-only">(current)</span></a>
            </li>`;
      } else {
        paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
      }
    }

    if (endPage < totalPages) {
      if (endPage < totalPages - 1) {
        paginationHtml += `<li class="page-item disabled"><a class="page-link" href="#">...</a></li>`;
      }
      paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`;
    }

    if (currentPage < totalPages) {
      paginationHtml += `<li class="page-item">
            <a class="page-link" href="#" data-page="${currentPage + 1}">Siguiente</a>
        </li>`;
    } else {
      paginationHtml += `<li class="page-item disabled">
            <a class="page-link" href="#" data-page="${currentPage + 1}">Siguiente</a>
        </li>`;
    }

    $('#pagination').html(paginationHtml);
  }

  fetchClients();
});

function compararFechas(fecha1, fecha2) {
  let partesFecha1 = fecha1.split('/');
  let partesFecha2 = fecha2.split('/');

  let date1 = new Date(partesFecha1[2], partesFecha1[1] - 1, partesFecha1[0]);
  let date2 = new Date(partesFecha2[2], partesFecha2[1] - 1, partesFecha2[0]);

  if (date1 > date2) {
    return true;
  } else {
    return false;
  }
}

function convertirFecha(fecha, type = 1) {
  let [fechaPartes, horaPartes] = fecha.split(' ');

  let [anio, mes, dia] = fechaPartes.split('-');

  let [hora, minutos] = horaPartes.split(':');

  if (type == 1) {
    return `${dia}/${mes}/${anio}`;
  } else {
    return `${dia}/${mes}/${anio} ${hora}:${minutos}`;
  }
}

function recortarString(str) {
  if (str.length > 35) {
    return str.substring(0, 35) + '...';
  }
  return str;
}