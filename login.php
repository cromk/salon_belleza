<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | Salón de Belleza</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="login">

  <div class="card1 p-4">
    <div class="text-center mb-3">
      <i class="bi bi-scissors brand-icon"></i>
      <h3 class="fw-bold mt-2">BeautyFlow</h3>
      <p class="text-muted mb-4">Tu estilo, nuestro arte ✨</p>
    </div>

    <form id="loginForm">
      <div class="mb-3">
        <label for="usuario" class="form-label fw-semibold">Usuario</label>
        <div class="input-group">
          <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
          <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Ingrese su usuario" required>
        </div>
      </div>

      <div class="mb-3">
        <label for="password" class="form-label fw-semibold">Contraseña</label>
        <div class="input-group">
          <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
          <input type="password" class="form-control" id="password" name="password" placeholder="Ingrese su contraseña" required>
          <span class="input-group-text bg-white" id="togglePassword" role="button" aria-label="Mostrar contraseña" style="cursor: pointer;"><i class="bi bi-eye"></i></span>
        </div>
      </div>

      <div class="d-grid mt-4">
        <button type="submit" id="btnLogin" class="btn btn-login">Iniciar Sesión</button>
      </div>

      <div id="mensaje" class="alert alert-danger mt-3 d-none"></div>

      <div class="text-center mt-3 text-muted"></div>
    </form>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="assets/js/login.js"></script>
</body>
</html>