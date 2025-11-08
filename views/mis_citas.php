<?php
include 'layout/header.php';
?>
<body class="index">
  <?php include 'layout/navbar.php'; ?>
  <div class="container mt-5">
    <?php $role = $_SESSION['usuario']['id_rol'] ?? 0; ?>
    <div class="text-center mb-4">
      <h3 class="section-title mb-4 text-center"><?php echo ($role == 2 || $role == 1) ? 'Citas 📅' : 'Mis Citas 📅'; ?>
        <button id="notifBtn" type="button" class="btn btn-link p-0 ms-2" title="Notificaciones" style="vertical-align:middle;">
          <i class="bi bi-bell" style="font-size:1.05rem;"></i>
          <span id="notifBadge" class="badge bg-danger" style="display:none; font-size:0.7rem; vertical-align:top;">0</span>
        </button>
      </h3>
      <p class="text-muted"><?php echo ($role == 2 || $role == 1) ? 'Listado de citas del salón. Usa el filtro de estilista para ver las citas por profesional.' : 'Vista personal de tus citas asignadas. Actualiza automáticamente y muestra todas tus citas por defecto si no aplicas filtros.'; ?></p>
    </div>

    <div class="card p-3 mb-4">
      <div class="row g-2 align-items-end">
        <div class="col-md-3">
          <label class="form-label">Fecha </label>
          <input type="date" id="selFecha" class="form-control" value="">
        </div>
        <div class="col-md-2">
          <label class="form-label">Desde (HH:MM)</label>
          <input type="time" id="filterDesde" class="form-control" value="00:00">
        </div>
        <div class="col-md-2">
          <label class="form-label">Hasta (HH:MM)</label>
          <input type="time" id="filterHasta" class="form-control" value="23:59">
        </div>
        <?php if ($role == 2 || $role == 1): /* recepcionistas y admin */ ?>
        <div class="col-md-3">
          <label class="form-label">Estilista</label>
          <select id="filterEstilista" class="form-select">
            <option value="">-- Todos los estilistas --</option>
          </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
        <?php else: ?>
        <div class="col-md-3 d-flex gap-2">
        <?php endif; ?>
          <button id="btnCargar" class="btn btn-primary icon-btn btn-sm" title="Aplicar filtros" aria-label="Aplicar filtros"><i class="bi bi-funnel"></i></button>
          <button id="btnLimpiar" class="btn btn-outline-secondary icon-btn btn-sm" title="Limpiar filtros" aria-label="Limpiar filtros"><i class="bi bi-eraser"></i></button>
        </div>
      </div>
    </div>

    <div id="agendaArea">
      <!-- agenda cargada aquí -->
    </div>
  </div>

  <?php include 'layout/footer.php'; ?>
  <!-- Modals -->
  <!-- Reassign modal -->
  <div class="modal fade" id="assignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Reasignar cita</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="assign_id_cita">
          <div class="mb-3">
            <label class="form-label">Estilista</label>
            <select id="assign_estilista" class="form-select"></select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button id="btnAssignConfirm" type="button" class="btn btn-primary">Reasignar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Reschedule modal -->
  <div class="modal fade" id="rescheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Reagendar cita</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="res_id_cita">
          <div class="mb-3">
            <label class="form-label">Fecha</label>
            <input type="date" id="res_fecha" class="form-control">
          </div>
          <div class="row g-2">
            <div class="col-md-6 mb-3">
              <label class="form-label">Hora inicio</label>
              <input type="time" id="res_hora_inicio" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Hora fin</label>
              <input type="time" id="res_hora_fin" class="form-control">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Estilista (opcional)</label>
            <select id="res_estilista" class="form-select"><option value="">(mantener)</option></select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button id="btnRescheduleConfirm" type="button" class="btn btn-primary">Reagendar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Cancel modal -->
  <div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Cancelar cita</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="cancel_id_cita">
          <div class="mb-3">
            <label class="form-label">Motivo</label>
            <textarea id="cancel_motivo" class="form-control" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button id="btnCancelConfirm" type="button" class="btn btn-danger">Confirmar cancelación</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Notifications modal -->
  <div class="modal fade" id="notifModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Notificaciones</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body" id="notifList">
          <!-- lista cargada por JS -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
  <script src="/salon_belleza/assets/js/mis_citas.js"></script>
</body>
</html>
