<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="#"><i class="bi bi-scissors"></i> BeautyFlow</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link active" href="/salon_belleza/views/index.php"><i class="bi bi-house-door"></i> Inicio</a></li>
          <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-calendar2-check"></i> Citas</a></li>
          <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-person-hearts"></i> Clientes</a></li>
          <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-brush"></i> Servicios</a></li>
          <li class="nav-item"><a class="nav-link" href="personal.php"><i class="bi bi-people"></i> Personal</a></li>
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