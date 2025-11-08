<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../login.php");
  exit();
}
$usuario = $_SESSION['usuario']['nombre'];
// Obtener número de servicios activos mediante el modelo
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../models/servicioModel.php';
try {
  $con = new Conexion();
  $cn = $con->conectar();
  $servicioModel = new servicioModel($cn);
  $servicios_count = $servicioModel->countActive();
} catch (Exception $e) {
  error_log('Error obteniendo conteo de servicios: ' . $e->getMessage());
  $servicios_count = 0;
}

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
              <?php
              // Calcular conteo dinámico de citas del día
              $citas_count = 0;
              try {
                $fecha_hoy = date('Y-m-d');
                // si es estilista, contar solo sus citas; si es admin (1) contar todas las citas
                $id_rol = isset($_SESSION['usuario']['id_rol']) ? (int)$_SESSION['usuario']['id_rol'] : 0;
                if ($id_rol === 3) {
                  // buscar id_estilista por id_usuario
                  $uid = $_SESSION['usuario']['id_usuario'] ?? null;
                  if ($uid) {
                    $stmt = $cn->prepare("SELECT id_estilista FROM estilistas WHERE id_usuario = :u LIMIT 1");
                    $stmt->execute([':u' => $uid]);
                    $r = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($r && isset($r['id_estilista'])) {
                      $id_est = (int)$r['id_estilista'];
                      $q = $cn->prepare("SELECT COUNT(*) as cnt FROM citas WHERE id_estilista = :e AND fecha_cita = :f AND (observaciones IS NULL OR observaciones NOT LIKE 'BLOQUEO:%')");
                      $q->execute([':e'=>$id_est, ':f'=>$fecha_hoy]);
                      $citas_count = (int)$q->fetchColumn();
                    }
                  }
                } else {
                  // admin o cualquier otro rol: contar todas las citas del día (excluyendo bloqueos)
                  $q = $cn->prepare("SELECT COUNT(*) as cnt FROM citas WHERE fecha_cita = :f AND (observaciones IS NULL OR observaciones NOT LIKE 'BLOQUEO:%')");
                  $q->execute([':f'=>$fecha_hoy]);
                  $citas_count = (int)$q->fetchColumn();
                }
              } catch (Exception $e) {
                error_log('Error contando citas: '.$e->getMessage());
                $citas_count = 0;
              }
              ?>
              <p class="fs-4 fw-bold"><?php echo $citas_count; ?></p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card p-4 text-center">
          <i class="bi bi-person-hearts text-gradient"></i>
          <h5>Clientes</h5>
          <?php
          // contar clientes reales (excluir clientes marcador para bloqueos cuyo correo comience con 'bloqueo@')
          $clientes_count = 0;
          try {
            $q = $cn->prepare("SELECT COUNT(*) as cnt FROM clientes WHERE correo NOT LIKE 'bloqueo@%'");
            $q->execute();
            $clientes_count = (int)$q->fetchColumn();
          } catch (Exception $e) {
            error_log('Error contando clientes: '.$e->getMessage());
            $clientes_count = 0;
          }
          ?>
          <p class="fs-4 fw-bold"><?php echo $clientes_count; ?></p>
        </div>
      </div>
      <div class="col-md-3">
          <div class="card p-4 text-center">
          <i class="bi bi-brush text-gradient"></i>
          <h5>Servicios activos</h5>
          <p class="fs-4 fw-bold"><?php echo $servicios_count; ?></p>
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