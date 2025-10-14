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
  <title>Panel Principal | BeautyFlow</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="index">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="#"><i class="bi bi-scissors"></i> BeautyFlow</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link active" href="#"><i class="bi bi-house-door"></i> Inicio</a></li>
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
            <li><a class="dropdown-item text-danger" href="../logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a></li>
          </ul>
        </div>
      </div>
    </div>
  </nav>

  <!-- Contenido -->
  <div class="container mt-5">
    <div class="text-center mb-5">
      <h2 class="section-title">Bienvenido(a), <?php echo $usuario; ?> ✨</h2>
      <p class="text-muted">Panel de administración de BeautyFlow</p>
    </div>

    <!-- Tarjetas resumen -->
    <div class="row g-4 mb-5">
      <div class="col-md-3">
        <div class="card p-4 text-center">
          <i class="bi bi-calendar-check text-gradient"></i>
          <h5>Citas del día</h5>
          <p class="fs-4 fw-bold">12</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card p-4 text-center">
          <i class="bi bi-person-hearts text-gradient"></i>
          <h5>Clientes</h5>
          <p class="fs-4 fw-bold">248</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card p-4 text-center">
          <i class="bi bi-brush text-gradient"></i>
          <h5>Servicios activos</h5>
          <p class="fs-4 fw-bold">18</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card p-4 text-center">
          <i class="bi bi-cash-coin text-gradient"></i>
          <h5>Ingresos del día</h5>
          <p class="fs-4 fw-bold">$540.00</p>
        </div>
      </div>
    </div>

    <!-- Gráfico -->
    <div id="chartContainer" class="mb-5">
      <h5 class="mb-4 text-center section-title">Ingresos semanales</h5>
      <canvas id="incomeChart" height="100"></canvas>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    const ctx = document.getElementById('incomeChart');

    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'],
        datasets: [{
          label: 'Ingresos ($)',
          data: [120, 150, 180, 200, 300, 250, 320],
          backgroundColor: '#8a2be2',
          borderColor: '#5e2a84',
          borderWidth: 1,
          borderRadius: 5
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              color: '#4b286d',
              font: { weight: '600' }
            },
            grid: {
              color: '#eee'
            }
          },
          x: {
            ticks: {
              color: '#4b286d',
              font: { weight: '600' }
            },
            grid: {
              display: false
            }
          }
        },
        plugins: {
          legend: {
            labels: { color: '#4b286d', font: { weight: '600' } }
          }
        }
      }
    });
  </script>
  <!-- Footer -->
<footer class="footer  text-center text-white">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <h6 class="fw-bold mb-2"><i class="bi bi-scissors"></i> Salón de Belleza</h6>
        <p class="small mb-1">Tu estilo, nuestro arte 💜</p>
        <p class="small mb-3">Sistema de gestión integral — © <?php echo date('Y'); ?> Todos los derechos reservados.</p>

        <div>
          <a href="#" class="text-white mx-2"><i class="bi bi-facebook"></i></a>
          <a href="#" class="text-white mx-2"><i class="bi bi-instagram"></i></a>
          <a href="#" class="text-white mx-2"><i class="bi bi-whatsapp"></i></a>
        </div>
      </div>
    </div>
  </div>
</footer>
</body>
</html>