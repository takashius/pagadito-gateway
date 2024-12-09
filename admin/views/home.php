<div id="loader_new-er-control" class="pre-load-web">
  <div class="imagen-load">
    <div class="preloader"></div> <?php echo __('Cargando...', 'er-control-vatm'); ?>
  </div>
</div>
<nav aria-label="breadcrumb" id="url_site" item_ref="<?php echo get_site_url(); ?>">
  <ol class="breadcrumb">
    <li class="breadcrumb-item active" aria-current="page">Pagadito Reports</li>
  </ol>
</nav>
<div class="container">
  <div class="card-body">
    <div class="row d-flex">
      <div class="col">
        <div class="form-group">
          <label for="pattern"><?php echo __('Parámetros de búsqueda', 'er-control-vatm'); ?></label>
          <input type="text" class="form-control" id="pattern" name="pattern">
        </div>
      </div>
      <div class="col-2">
        <div class="form-group">
          <label for="http_response"><?php echo __('HTTP Response', 'er-control-vatm'); ?></label>
          <select class="general form-control" style="width:100%; max-width: none;" id="http_response"
            name="http_response">
            <option>Todos</option>
            <option>200</option>
            <option>400</option>
            <option>500</option>
          </select>
        </div>
      </div>
      <div class="col-2">
        <div class="form-group">
          <label for="origin"><?php echo __('Cliente', 'er-control-vatm'); ?></label>
          <select class="general form-control" style="width:100%; max-width: none;" id="customer" name="customer">
            <option>Todos</option>
          </select>
        </div>
      </div>
      <div class="col-2">
        <div class="form-group">
          <label for="date_to"><?php echo __('Desde:', 'er-control-vatm'); ?></label>
          <input type="text" class="form-control" id="date_to" name="date_to" required readonly
            value="<?php echo date('d/m/Y') ?>">
        </div>
      </div>
      <div class="col-2">
        <div class="form-group">
          <label for="date_from"><?php echo __('Hasta:', 'er-control-vatm'); ?></label>
          <input type="text" class="form-control" id="date_from" name="date_from" required readonly
            value="<?php echo date('d/m/Y') ?>">
        </div>
      </div>
      <div class="col-1 justify-content-center align-self-center">
        <button type="button" class="btn btn-primary " id="search">
          <?php echo __('Buscar', 'er-pagadito-gateway'); ?>
        </button>
      </div>
    </div>
    <div>
      <table class="table table-bordered">
        <thead class="bg-primary text-white">
          <tr>
            <th scope="col">IP</th>
            <th scope="col">Nombre Cliente</th>
            <th scope="col">Moneda</th>
            <th scope="col">http_code</th>
            <th scope="col">origin</th>
            <th scope="col">Monto</th>
            <th scope="col">Fecha</th>
          </tr>
        </thead>
        <tbody id="tableBodyReport">

        </tbody>
      </table>
    </div>
    <div class="d-flex justify-content-end row">
      <div class="col">
        <div class="row">
          <div class="col-4">
            <div class="form-group">
              <label for="perPag"><?php echo __('Por Página', 'er-control-vatm'); ?></label>
              <select class="general form-control" style="width:100%; max-width: none;" id="perPag" name="perPag">
                <option>10</option>
                <option>20</option>
                <option>50</option>
                <option>100</option>
              </select>
            </div>
          </div>
          <div class="col-4">
            <div class="form-group">
              <label for="download"><?php echo __('Descargar', 'er-control-vatm'); ?></label>
              <select class="general form-control" style="width:100%; max-width: none;" id="download" name="download">
                <option>Seleccione uno</option>
                <option>PDF</option>
                <option>Excel</option>
              </select>
            </div>
          </div>
        </div>
      </div>
      <div class="col d-flex justify-content-end">
        <nav aria-label="...">
          <ul class="pagination" id="pagination">
            <li class="page-item disabled">
              <a class="page-link" href="#" tabindex="-1">Previous</a>
            </li>
            <li class="page-item"><a class="page-link" href="#">1</a></li>
            <li class="page-item active">
              <a class="page-link" href="#">2 <span class="sr-only">(current)</span></a>
            </li>
            <li class="page-item"><a class="page-link" href="#">3</a></li>
            <li class="page-item">
              <a class="page-link" href="#">Next</a>
            </li>
          </ul>
        </nav>
      </div>
    </div>
  </div>
</div>