<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header('Location: ../login.php');
  exit();
}
$role = (int)($_SESSION['usuario']['id_rol'] ?? 0);
if (!in_array($role, [1, 2], true)) {
  header('Location: index.php');
  exit();
}
include 'layout/header.php';
?>
<body class="index">
  <?php include 'layout/navbar.php'; ?>
  <div class="container mt-5">
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="card p-4 shadow-sm h-100">
          <h5 class="mb-3 text-gradient"><i class="bi bi-person-plus"></i> Registrar cliente</h5>
          <form id="formCliente">
            <div class="mb-3">
              <label class="form-label">Nombre *</label>
              <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Apellido</label>
              <input type="text" name="apellido" class="form-control">
            </div>
            <div class="mb-3">
              <label class="form-label">Teléfono</label>
              <input type="text" name="telefono" class="form-control" placeholder="0000-0000">
            </div>
            <div class="mb-3">
              <label class="form-label">Correo</label>
              <input type="email" name="correo" class="form-control" placeholder="cliente@correo.com">
            </div>
            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-primary" id="btnGuardarCliente"><i class="bi bi-save"></i> Guardar cliente</button>
              <button type="button" class="btn btn-outline-secondary d-none" id="btnCancelarEdicion"><i class="bi bi-arrow-counterclockwise"></i> Cancelar edición</button>
            </div>
            <div id="mensajeCliente" class="alert mt-3 d-none"></div>
          </form>
        </div>
      </div>
      <div class="col-lg-8">
        <div class="card p-4 shadow-sm h-100">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-2">
            <div>
              <h5 class="mb-1"><i class="bi bi-people"></i> Clientes registrados</h5>
              <p class="text-muted mb-0">Administra la información de tus clientes y su estado.</p>
            </div>
            <div class="form-check form-switch align-self-start">
              <input class="form-check-input" type="checkbox" role="switch" id="showInactive">
              <label class="form-check-label" for="showInactive">Mostrar inactivos</label>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-striped table-hover align-middle" id="tablaClientes">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Cliente</th>
                  <th>Contacto</th>
                  <th>Estado</th>
                  <th>Historial</th>
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
  <script src="/salon_belleza/assets/js/clientes.js"></script>
</body>
</html>