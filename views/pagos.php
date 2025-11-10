<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}


if (!isset($citas) || !isset($pagos)) {
  header('Location: ../controllers/PagosController.php');
  exit();
}

if (!isset($_SESSION['usuario'])) { 
    header('Location: ../login.php'); 
    exit(); 
}
$usuario = $_SESSION['usuario']['nombre'];
// Restringir acceso a recepcionistas
if (isset($_SESSION['usuario']['id_rol']) && (int)$_SESSION['usuario']['id_rol'] !== 2) {
  // Si no es recepcionista, redirigir a la vista de reportes
  header('Location: /pagos.php'); exit();
}

include 'layout/header.php';
?>
<body>
<?php include 'layout/navbar.php'; ?>

<div class="container mt-4">
  <h3><i class="bi bi-credit-card"></i> Pagos del Salón Beauty Flow</h3>

  <div class="card p-4 mt-3 shadow-sm">
    <h5 class="mb-3"><i class="bi bi-cash-stack"></i> Registrar nuevo pago</h5>

    <form action="../controllers/PagosController.php?action=registrar" method="POST" class="row g-3">

      <!--  CAMPO: Seleccionar Cliente -->
      <div class="col-md-4">
        <label for="id_cita" class="form-label">Cliente / Cita</label>
        <select class="form-select" id="id_cita" name="id_cita" required>
          <option value="">Seleccione un cliente...</option>
          <?php if (!empty($citas)): ?>
            <?php foreach ($citas as $c): ?>
              <option value="<?= htmlspecialchars($c['id_cita']) ?>">
                <?= htmlspecialchars($c['cliente']) ?> - <?= htmlspecialchars($c['servicio']) ?>
              </option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
      </div>

      <!--  CAMPO: Servicio -->
      <div class="col-md-4">
        <label for="servicio" class="form-label">Servicio</label>
        <input type="text" class="form-control" id="servicio" name="servicio" readonly>
      </div>

      <!--  CAMPO: Monto -->
      <div class="col-md-2">
        <label for="monto" class="form-label">Monto ($)</label>
        <input type="number" step="0.01" class="form-control" id="monto" name="monto" readonly required>
      </div>

      <!--  CAMPO: Método -->
      <div class="col-md-2">
        <label for="metodo" class="form-label">Método de Pago</label>
        <select class="form-select" id="metodo" name="metodo" required>
          <option value="">Seleccione...</option>
          <option value="Efectivo">Efectivo</option>
          <option value="Tarjeta">Tarjeta</option>
          <option value="Transferencia">Transferencia</option>
        </select>
      </div>

      <div class="col-md-12 d-flex justify-content-end">
        <button type="submit" class="btn btn-success mt-3">
          <i class="bi bi-check-circle"></i> Confirmar Pago
        </button>
      </div>
    </form>
  </div>

  <div class="col-md-12 mt-4">
    <div class="card p-4 shadow-sm">
      <h5 class="mb-3"><i class="bi bi-list-check"></i> Historial de Pagos</h5>
      <table class="table table-bordered table-striped align-middle" id="tablaPagos">
        <thead>
          <tr>
            <th>#</th>
            <th>Fecha Cita</th>
            <th>Método</th>
            <th>Monto ($)</th>
            <th>Referencia</th>
            <th>Estado</th>
            <th>Fecha Pago</th>
            <th>Factura</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($pagos)): ?>
            <?php foreach ($pagos as $p): ?>
              <tr>
                <td><?= htmlspecialchars($p['id_pago']) ?></td>
                <td><?= htmlspecialchars($p['fecha_cita']) ?></td>
                <td><?= htmlspecialchars($p['metodo']) ?></td>
                <td>$<?= number_format($p['monto'], 2) ?></td>
                <td><?= htmlspecialchars($p['referencia']) ?></td>
                <td><span class="badge bg-success"><?= htmlspecialchars($p['estado']) ?></span></td>
                <td><?= htmlspecialchars($p['fecha_pago']) ?></td>
                <td>
                <a href="../controllers/FacturaController.php?id_pago=<?= $p['id_pago'] ?>" 
                  class="btn btn-sm btn-outline" target="_blank">
                  <i class="bi bi-file-earmark-pdf"></i> PDF
                </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="7" class="text-center text-muted">No hay pagos registrados</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
// Pasar datos de PHP a JavaScript
const citas = <?= json_encode($citas ?? []) ?>;

document.getElementById('id_cita').addEventListener('change', function() {
  const idCita = this.value;
  
  if (idCita) {
    // Buscar la cita seleccionada en el array
    const citaSeleccionada = citas.find(c => c.id_cita == idCita);
    
    if (citaSeleccionada) {
      // Rellenar los campos con los datos de la cita
      document.getElementById('servicio').value = citaSeleccionada.servicio || '';
      document.getElementById('monto').value = citaSeleccionada.monto || '';
    }
  } else {
    // Limpiar campos si no hay selección
    document.getElementById('servicio').value = '';
    document.getElementById('monto').value = '';
  }
});
</script>

<?php include 'layout/footer.php'; ?>
</body>
</html>