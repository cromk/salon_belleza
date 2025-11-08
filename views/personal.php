<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../login.php");
  exit();
}
$usuario = $_SESSION['usuario']['nombre'];
// Restringir acceso a administradores
if (isset($_SESSION['usuario']['id_rol']) && (int)$_SESSION['usuario']['id_rol'] !== 1) {
  header('Location: catalogo.php'); exit();
}
include 'layout/header.php';
?>
<body class="index">

  <!-- Navbar -->
  <?php include 'layout/navbar.php'; ?>

  <div class="container mt-5">
    <h3 class="section-title mb-4 text-center">Gestión de Personal 💇‍♀️</h3>

    <!-- Formulario -->
    <div class="card mb-5 p-4">
      <h5 class="mb-3 text-gradient"><i class="bi bi-person-plus"></i> Registrar nuevo personal</h5>
      <form id="formPersonal">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Nombre</label>
            <input type="text" id="nombre" name="nombre" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Apellido</label>
            <input type="text" id="apellido" name="apellido" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Correo</label>
            <input type="email" id="correo" name="correo" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Teléfono</label>
            <input type="text" id="telefono" name="telefono" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Usuario</label>
            <input type="text" id="usuario" name="usuario" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Clave</label>
            <input type="password" id="clave" name="clave" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Rol</label>
            <select id="id_rol" name="id_rol" class="form-select" required></select>
          </div>
        </div>
        <div class="mt-4 text-end">
          <button type="submit" class="btn"><i class="bi bi-save"></i> Guardar</button>
        </div>
      </form>
      <div id="mensaje" class="alert mt-3 d-none"></div>
    </div>

    <!-- Tabla -->
    <div class="card p-4">
      <h5 class="mb-3"><i class="bi bi-list-check"></i> Personal registrado</h5>
      <table class="table table-bordered table-striped align-middle" id="tablaPersonal">
        <thead>
          <tr>
            <th>#</th>
            <th>Nombre completo</th>
            <th>Correo</th>
            <th>Teléfono</th>
            <th>Usuario</th>
            <th>Rol</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>

  <!-- Sección: Estilistas -->
  <div class="container mt-4">
    <h3 class="section-title mb-4 text-center">Gestión de Estilistas ✂️</h3>

    <div class="row">
      <div class="col-md-5">
        <div class="card p-4">
          <h5 class="mb-3 text-gradient"><i class="bi bi-person-badge"></i> Nuevo perfil de estilista</h5>
          <form id="formEstilista">
            <div class="mb-3">
              <label class="form-label">Usuario (preexistente)</label>
              <select id="selectUsuario" name="id_usuario" class="form-control" required>
                <option value="">-- Seleccione usuario --</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Especialidad</label>
              <input type="text" id="especialidad" name="especialidad" class="form-control">
            </div>
            <div class="mb-3">
              <label class="form-label">Años de experiencia</label>
              <input type="number" id="experiencia" name="experiencia" class="form-control" value="0">
            </div>
            <div class="mb-3">
              <label class="form-label">Disponible</label>
              <select id="disponible" name="disponible" class="form-control">
                <option value="Sí">Sí</option>
                <option value="No">No</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Servicios que ofrece</label>
              <div id="listaServicios" class="d-flex flex-column" style="max-height:200px; overflow:auto; border:1px solid #eee; padding:8px; border-radius:6px;"></div>
            </div>
            <div class="mb-3">
              <label class="form-label">Horario base (agrega uno o más)</label>
              <div class="d-flex g-2 align-items-end mb-2">
                <select id="horario_dia" class="form-select me-2" style="width:150px;"> 
                  <option value="Lunes">Lunes</option>
                  <option value="Martes">Martes</option>
                  <option value="Miércoles">Miércoles</option>
                  <option value="Jueves">Jueves</option>
                  <option value="Viernes">Viernes</option>
                  <option value="Sábado">Sábado</option>
                  <option value="Domingo">Domingo</option>
                </select>
                <input id="horario_hi" type="time" class="form-control me-2" style="width:130px;" />
                <input id="horario_hf" type="time" class="form-control me-2" style="width:130px;" />
                <button id="btnAddHorario" type="button" class="btn btn-outline-secondary">Añadir</button>
              </div>
              <div id="horariosList" style="min-height:40px;"></div>
            </div>
            <div class="d-grid">
              <button type="submit" class="btn"><i class="bi bi-save"></i> Guardar estilista</button>
            </div>
            <div id="mensajeEstilista" class="alert mt-3 d-none"></div>
          </form>
        </div>
      </div>

      <div class="col-md-7">
        <div class="card p-4">
          <h5 class="mb-3"><i class="bi bi-list-check"></i> Estilistas registrados</h5>
          <table class="table table-bordered table-striped align-middle" id="tablaEstilistas">
            <thead>
              <tr><th>#</th><th>Nombre</th><th>Especialidad</th><th>Años</th><th>Servicios</th></tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <script>
    /*$(document).ready(function() {

      // Cargar roles
      $.post("../controllers/PersonalController.php", { action: "getRoles" }, function(res) {
        if (res.success) {
          let options = '<option value="">Seleccione un rol</option>';
          res.data.forEach(r => {
            options += `<option value="${r.id_rol}">${r.nombre}</option>`;
          });
          $("#id_rol").html(options);
        }
      }, "json");

      // Guardar personal
      $("#formPersonal").on("submit", function(e) {
        e.preventDefault();

        $.post("../controllers/PersonalController.php", {
          action: "create",
          nombre: $("#nombre").val(),
          apellido: $("#apellido").val(),
          correo: $("#correo").val(),
          telefono: $("#telefono").val(),
          usuario: $("#usuario").val(),
          clave: $("#clave").val(),
          id_rol: $("#id_rol").val()
        }, function(res) {
          if (res.success) {
            $("#mensaje").removeClass("d-none alert-danger").addClass("alert-success").text("Personal registrado correctamente.");
            $("#formPersonal")[0].reset();
            cargarPersonal();
          } else {
            $("#mensaje").removeClass("d-none alert-success").addClass("alert-danger").text("Error al registrar.");
          }
        }, "json");
      });

      // Cargar personal existente
      function cargarPersonal() {
        $.post("../controllers/PersonalController.php", { action: "read" }, function(res) {
          if (res.success) {
            let filas = "";
            res.data.forEach((p, i) => {
              filas += `
                <tr>
                  <td>${i + 1}</td>
                  <td>${p.nombre} ${p.apellido}</td>
                  <td>${p.correo}</td>
                  <td>${p.telefono}</td>
                  <td>${p.usuario}</td>
                  <td>${p.rol}</td>
                </tr>`;
            });
            $("#tablaPersonal tbody").html(filas);
          }
        }, "json");
      }

      cargarPersonal();
    });*/
  </script>
  <!-- Footer -->
  <?php include 'layout/footer.php'; ?>
  <script src="/salon_belleza/assets/js/personal.js"></script>
  <script src="/salon_belleza/assets/js/estilistas.js"></script>
</body>
</html>