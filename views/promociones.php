<?php
// Iniciar sesión para validar acceso al panel
session_start();

// Restringir acceso a usuarios sin sesión
if (!isset($_SESSION['usuario'])) {
  header('Location: ../login.php');
  exit();
}

// Solo los administradores (rol 1) pueden gestionar promociones
if ((int)($_SESSION['usuario']['id_rol'] ?? 0) !== 1) {
  header('Location: index.php');
  exit();
}

include 'layout/header.php';
?>
<body class="index">
  <?php include 'layout/navbar.php'; ?>

  <div class="container mt-5">
    <div class="row">
      <div class="col-md-5">
        <div class="card p-4 shadow-sm">
          <h5 class="mb-3 text-gradient"><i class="bi bi-gift"></i> Crear promoción o combo</h5>
          <form id="formPromocion">
            <input type="hidden" id="id_promocion" name="id_promocion">
            <div class="mb-3">
              <label class="form-label">Nombre</label>
              <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Tipo</label>
              <select class="form-select" id="tipo" name="tipo" required>
                <option value="">-- Seleccione --</option>
                <option value="Promoción">Promoción</option>
                <option value="Combo">Combo</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label" for="precio" id="labelPrecio">Precio ofertado ($)</label>
              <input type="number" step="0.01" min="0" class="form-control" id="precio" name="precio" required>
              <div class="form-text" id="precioHelp">Para promociones ingresa el porcentaje de descuento.</div>
            </div>
            <div class="mb-3">
              <label class="form-label">Descripción</label>
              <textarea class="form-control" id="descripcion" name="descripcion" rows="3" placeholder="Describe los servicios incluidos o beneficios."></textarea>
            </div>
            <div class="d-grid">
              <button type="submit" class="btn" id="btnGuardarPromocion"><i class="bi bi-save"></i> Guardar</button>
            </div>
            <div id="mensajePromocion" class="alert mt-3 d-none"></div>
          </form>
        </div>
      </div>

      <div class="col-md-7">
        <div class="card p-4 shadow-sm">
          <h5 class="mb-3"><i class="bi bi-list-check"></i> Promociones y combos registrados</h5>
          <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle" id="tablaPromociones">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Nombre</th>
                  <th>Tipo</th>
                  <th>Precio</th>
                  <th>Estado</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include 'layout/footer.php'; ?>
  <script src="/salon_belleza/assets/js/promociones.js"></script>
</body>
</html>