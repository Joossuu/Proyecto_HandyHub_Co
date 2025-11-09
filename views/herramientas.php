<?php
// views/herramientas.php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: /Proyecto_HandyHub/index.php');
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Herramientas - HandyHub</title>
  <link rel="stylesheet" href="/Proyecto_HandyHub/assets/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
</head>
<body>
<?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

<main class="main-wrapper">
  <div class="header-row">
    <div class="header-left">
      <h1 class="page-title">Herramientas</h1>
      <div class="small-muted">Inventario de herramientas</div>
    </div>
    <div class="header-actions">
      <button class="btn btn-primary" data-new-btn="herramientas">Nueva herramienta</button>
    </div>
  </div>

  <section id="moduleListWrapper" data-module="herramientas"></section>

  <footer class="app-footer">© <?= date('Y') ?> HandyHub. Todos los derechos reservados.</footer>
</main>

<script src="/Proyecto_HandyHub/assets/module-common.js"></script>
<script src="/Proyecto_HandyHub/assets/herramientas.js"></script>
</body>
</html>
