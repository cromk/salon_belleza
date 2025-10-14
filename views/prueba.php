<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../login.php");
  exit();
}
$usuario = $_SESSION['usuario']['nombre'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Personal | Salón de Belleza</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <style>
    body {
      background-color: #f9f6fc;
      font-family: 'Poppins', sans-serif;
    }
    .navbar {
      background: linear-gradient(90deg, #5e2a84, #8a2be2, #b48ff7);
    }
    .navbar-brand, .nav-link {
      color: #fff !important;
    }
    .section-title {
      color: #4b286d;
      font-weight: 700;
    }
    .card {
      border: none;
      border-radius: 1rem;
      box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }
    .btn-primary {
      background-color: #8a2be2;
      border: none;
    }
    .btn-primary:hover {
      background-color: #6f2ebf;
    }
    table th {
      background-color: #8a2be2;
      color: white;
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold" href="#"><i class="bi bi-scissors"></i> Salón de Belleza</a>
      <div class="collapse navbar-collapse">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-house-door"></i> Inicio</a></li>
          <li class="nav-item"><a class="nav-link active" href="#"><i class="bi bi-people"></i> Personal</a></li>
        </ul>
        <span class="navbar-text text-white">
          <i class="bi bi-person-circle"></i> <?php echo $usuario; ?>
        </span>
      </div>
    </div>
  </nav>

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
          <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar</button>
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
</body>
</html>