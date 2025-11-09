<?php
// Asegurarnos de que la sesión esté iniciada antes de leer datos de $_SESSION
if (session_status() == PHP_SESSION_NONE) session_start();

// Determinar la ruta actual para marcar el enlace activo
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$currentFile = basename($requestPath);

function nav_active($name, $currentFile) {
  // Normalizar: quitar sufijo .php y comparar en minúsculas
  $normalize = function($s) {
    $s = trim(strtolower((string)$s));
    if ($s === '') return 'index';
    if (substr($s, -4) === '.php') $s = substr($s, 0, -4);
    return $s;
  };
  $nName = $normalize($name);
  $nCurrent = $normalize($currentFile);
  // Mapear alias: tratar catalogo.php como servicios para mantener el mismo enlace activo
  if ($nCurrent === 'catalogo') $nCurrent = 'servicios';
  return ($nName === $nCurrent) ? 'active' : '';
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
          <li class="nav-item"><a class="nav-link <?php echo nav_active('mis_citas.php', $currentFile); ?>" href="/salon_belleza/views/mis_citas.php"><i class="bi bi-calendar2-check"></i> Citas</a></li>
          <li class="nav-item"><a class="nav-link <?php echo nav_active('clientes.php', $currentFile); ?>" href="/salon_belleza/views/clientes.php"><i class="bi bi-person-hearts"></i> Clientes</a></li>
          <li class="nav-item"><a class="nav-link <?php echo nav_active('servicios.php', $currentFile); ?>" href="/salon_belleza/views/servicios.php"><i class="bi bi-brush"></i> Servicios</a></li>
          <?php if (isset($_SESSION['usuario']) && (int)($_SESSION['usuario']['id_rol'] ?? 0) === 1): ?>
            <li class="nav-item"><a class="nav-link <?php echo nav_active('personal.php', $currentFile); ?>" href="/salon_belleza/views/personal.php"><i class="bi bi-people"></i> Personal</a></li>
            <li class="nav-item"><a class="nav-link <?php echo nav_active('agenda.php', $currentFile); ?>" href="/salon_belleza/views/agenda.php"><i class="bi bi-calendar2-week"></i> Agenda</a></li>
            <li class="nav-item"><a class="nav-link <?php echo nav_active('reportes.php', $currentFile); ?>" href="/salon_belleza/views/reportes.php"><i class="bi bi-graph-up"></i> Reportes</a></li>
          <?php endif; ?>
        </ul>

          <?php
          // Mostrar nombre de usuario si hay sesión, si no mostrar enlace a login
          if (session_status() == PHP_SESSION_NONE) session_start();
          $userName = isset($_SESSION['usuario']['nombre']) ? htmlspecialchars($_SESSION['usuario']['nombre']) : null;
          if ($userName): ?>
            <div class="dropdown">
              <a class="nav-link dropdown-toggle text-white fw-semibold" href="#" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle"></i> <?php echo $userName; ?>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#"><i class="bi bi-gear"></i> Configuración</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="/salon_belleza/logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a></li>
              </ul>
            </div>
          <?php else: ?>
            <a class="nav-link text-white fw-semibold" href="/salon_belleza/login.php"><i class="bi bi-box-arrow-in-right"></i> Iniciar sesión</a>
          <?php endif; ?>
      </div>
    </div>
  </nav>