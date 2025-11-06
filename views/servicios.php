<?php
session_start();
// Simple check: require logged user
if (!isset($_SESSION['usuario'])) {
  header('Location: ../login.php'); exit();
}
$usuario = $_SESSION['usuario']['nombre'];
include 'layout/header.php';
?>
<body class="index">
  <?php include 'layout/navbar.php'; ?>

  <div class="container mt-5">
    <div class="row">
      <div class="col-md-6">
        <div class="card p-4">
          <h5>Crear Servicio</h5>
          <form id="formServicio">
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
              <button class="btn btn-primary" id="btnCrear">Crear Servicio</button>
            </div>
            <div id="mensajeServicio" class="alert mt-3 d-none"></div>
          </form>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card p-4">
          <h5>Servicios existentes</h5>
          <table class="table" id="tablaServicios">
            <thead>
              <tr><th>#</th><th>Nombre</th><th>Precio</th><th>Duración</th></tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <?php include 'layout/footer.php'; ?>
  <script src="/salon_belleza/assets/js/servicios.js"></script>
</body>
</html>
