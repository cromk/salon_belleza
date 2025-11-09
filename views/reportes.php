<?php
session_start();
if (!isset($_SESSION['usuario'])) { 
  header('Location: ../login.php'); exit(); 
}
$usuario = $_SESSION['usuario']['nombre'];
// Restringir acceso a administradores
if (isset($_SESSION['usuario']['id_rol']) && (int)$_SESSION['usuario']['id_rol'] !== 1) {
  // Si no es admin, redirigir a la vista de reportes
  header('Location: /reportes.php'); exit();
}
include 'layout/header.php';
?>
<body>
  <?php include 'layout/navbar.php'; ?>

  <div class="container mt-4">
    <h3><i class="bi bi-graph-up"></i> Reportes del Salón</h3>
    <div class="row my-3">
      <div class="col-md-4">
        <label>Fecha inicio</label>
        <input type="date" id="fechaInicio" class="form-control">
      </div>
      <div class="col-md-4">
        <label>Fecha fin</label>
        <input type="date" id="fechaFin" class="form-control">
      </div>
      <div class="col-md-4">
        <label>Tipo de reporte</label>
        <select id="tipoReporte" class="form-select">
          <option value="ventas">Ventas</option>
          <option value="servicios">Servicios más solicitados</option>
          <option value="ocupacion">Ocupación de estilistas</option>
          <option value="clientes">Clientes frecuentes</option>
        </select>
      </div>
    </div>
    <button class="btn btn-primary" id="btnGenerar"><i class="bi bi-bar-chart"></i> Generar</button>
    <button class="btn btn-outline-success" id="btnExportarCSV">Exportar CSV</button>
    <button class="btn btn-outline-danger" id="btnExportarPDF">Exportar PDF</button>
    <br> 
    <br>     

    <!-- Gráfico -->
    <div id="chartContainer" class="mb-5"> 
      <canvas id="grafico" height="100"></canvas>
      <br>
      <br>
        <div class="card p-4 text-center">
          <table class="table table-bordered table-striped align-middle" id="tablaDatos"></table>
        </div>
      </div>
  </div>

  <?php include 'layout/footer.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="/salon_belleza/assets/js/reportes.js"></script>
</body>
</html>