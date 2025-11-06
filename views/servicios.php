<?php
session_start();
// Simple check: require logged user
if (!isset($_SESSION['usuario'])) {
  header('Location: ../login.php'); exit();
}
$usuario = $_SESSION['usuario']['nombre'];
// Restringir acceso a administradores
if (isset($_SESSION['usuario']['id_rol']) && (int)$_SESSION['usuario']['id_rol'] !== 1) {
  // Si no es admin, redirigir al catálogo público
  header('Location: catalogo.php'); exit();
}
include 'layout/header.php';
?>
<body class="index">
  <?php include 'layout/navbar.php'; ?>

  <div class="container mt-5">
    <div class="row">
      <div class="col-md-6">
        <div class="card p-4">
          <h5 class="mb-3 text-gradient"><i class="bi bi-brush"></i> Crear Servicio</h5>
          <form id="formServicio">
            <input type="hidden" id="id_servicio" name="id_servicio">
            <div class="mb-3">
              <label class="form-label">Nombre</label>
              <input type="text" id="nombre" name="nombre" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Precio ($)</label>
              <input type="number" step="0.01" id="precio" name="precio" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Duración (min)</label>
              <input type="number" id="duracion" name="duracion" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Descripción (opcional)</label>
              <textarea id="descripcion" name="descripcion" class="form-control"></textarea>
            </div>
            <div class="d-grid">
              <button class="btn" id="btnCrear"><i class="bi bi-save"></i> Guardar</button>
            </div>
            <div id="mensajeServicio" class="alert mt-3 d-none"></div>
          </form>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card p-4">
          <h5 class="mb-3"><i class="bi bi-list-check"></i> Servicios existentes</h5>
          <table class="table table-bordered table-striped align-middle" id="tablaServicios">
            <thead>
              <tr><th>#</th><th>Nombre</th><th>Precio</th><th>Duración</th><th>Acciones</th></tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="row mt-4">
      <div class="col-md-6">
        <div class="card p-4">
          <h5 class="mb-3 text-gradient"><i class="bi bi-plus-circle"></i> Agregar especificación</h5>
          <form id="formEspecificacion">
            <div class="mb-3">
              <label class="form-label">Servicio</label>
              <select id="selectServicio" name="servicio_id" class="form-control" required>
                <option value="">-- Seleccione --</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Nombre</label>
              <input type="text" id="esp_nombre" name="nombre" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Tipo (afecta)</label>
              <select id="esp_tipo" name="tipo" class="form-control">
                <option value="precio">Precio</option>
                <option value="tiempo">Tiempo</option>
                <option value="ambos">Precio y Tiempo</option>
              </select>
              <small class="form-text text-muted">Indica si la especificación añade precio, tiempo o ambos.</small>
            </div>
            <div class="mb-3">
              <label class="form-label">Precio extra ($)</label>
              <input type="number" step="0.01" id="esp_precio" name="valor_precio" class="form-control" value="0.00">
            </div>
            <div class="mb-3">
              <label class="form-label">Tiempo extra (min)</label>
              <input type="number" id="esp_tiempo" name="valor_tiempo" class="form-control" value="0">
            </div>
            <div class="mb-3">
              <label class="form-label">Descripción (opcional)</label>
              <textarea id="esp_descripcion" name="descripcion" class="form-control"></textarea>
            </div>
            <div class="d-grid">
              <button class="btn" id="btnCrearEsp"><i class="bi bi-save"></i> Guardar</button>
            </div>
            <div id="mensajeEspecificacion" class="alert mt-3 d-none"></div>
          </form>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card p-4">
          <h5 class="mb-3"><i class="bi bi-list-ul"></i> Especificaciones del servicio seleccionado</h5>
          <table class="table table-bordered table-striped align-middle" id="tablaEspecificaciones">
            <thead>
              <tr><th>#</th><th>Nombre</th><th>Precio extra</th><th>Tiempo extra</th></tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <?php include 'layout/footer.php'; ?>
  <script src="/salon_belleza/assets/js/servicios.js"></script>
  <script src="/salon_belleza/assets/js/especificaciones.js"></script>
</body>
</html>
