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
</body>
</html>