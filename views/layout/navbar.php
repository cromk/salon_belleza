<?php
// Determinar la ruta actual para marcar el enlace activo
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$currentFile = basename($requestPath);

function nav_active($name, $currentFile) {
  if ($name === 'index') {
    return ($currentFile === 'index.php' || $currentFile === '') ? 'active' : '';
  }
  return ($currentFile === $name) ? 'active' : '';
}
?>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="/salon_belleza/views/index.php"><i class="bi bi-scissors"></i> BeautyFlow</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link <?php echo nav_active('index', $currentFile); ?>" href="/salon_belleza/views/index.php"><i class="bi bi-house-door"></i> Inicio</a></li>
          <li class="nav-item"><a class="nav-link <?php echo nav_active('citas.php', $currentFile); ?>" href="/salon_belleza/views/citas.php"><i class="bi bi-calendar2-check"></i> Citas</a></li>
          <li class="nav-item"><a class="nav-link <?php echo nav_active('clientes.php', $currentFile); ?>" href="/salon_belleza/views/clientes.php"><i class="bi bi-person-hearts"></i> Clientes</a></li>
          <li class="nav-item"><a class="nav-link <?php echo nav_active('servicios.php', $currentFile); ?>" href="/salon_belleza/views/servicios.php"><i class="bi bi-brush"></i> Servicios</a></li>
          <li class="nav-item"><a class="nav-link <?php echo nav_active('personal.php', $currentFile); ?>" href="/salon_belleza/views/personal.php"><i class="bi bi-people"></i> Personal</a></li>
        </ul>

          <div class="dropdown">
          <a class="nav-link dropdown-toggle text-white fw-semibold" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle"></i> <?php echo $usuario; ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="#"><i class="bi bi-gear"></i> Configuración</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="/salon_belleza/logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a></li>
          </ul>
        </div>
      </div>
    </div>
  </nav>