<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../login.php");
  exit();
}
$usuario = $_SESSION['usuario']['nombre'];

include 'layout/header.php';
?>
<body class="index">

  <!-- Navbar -->
  <?php include 'layout/navbar.php'; ?>

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
  <?php include 'layout/footer.php'; ?>
</body>
</html>