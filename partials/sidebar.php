<?php
// partials/sidebar.php
if (session_status() === PHP_SESSION_NONE) session_start();
$usuario = $_SESSION['usuario'] ?? 'Invitado';
$rol = $_SESSION['rol'] ?? '';
?>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-top">
    <button class="toggle-btn" id="toggleSidebar" aria-label="Toggle sidebar"><i class="fas fa-bars"></i></button>
    <div class="brand">HandyHub</div>
  </div>

  <nav class="sidebar-nav" id="sidebarNav" aria-label="Main navigation">
    <a class="nav-link" href="/Proyecto_HandyHub/views/dashboard.php"><i class="fas fa-home"></i><span class="nav-text">Dashboard</span></a>
    <a class="nav-link" href="/Proyecto_HandyHub/views/usuarios.php"><i class="fas fa-user"></i><span class="nav-text">Usuarios</span></a>
    <a class="nav-link" href="/Proyecto_HandyHub/views/roles.php"><i class="fas fa-user-tag"></i><span class="nav-text">Roles</span></a>
    <a class="nav-link" href="/Proyecto_HandyHub/views/herramientas.php"><i class="fas fa-wrench"></i><span class="nav-text">Herramientas</span></a>
    <a class="nav-link" href="/Proyecto_HandyHub/views/mantenimientos.php"><i class="fas fa-tools"></i><span class="nav-text">Mantenimientos</span></a>
    <a class="nav-link" href="/Proyecto_HandyHub/views/prestamos.php"><i class="fas fa-file-signature"></i><span class="nav-text">Préstamos</span></a>
    <a class="nav-link" href="/Proyecto_HandyHub/views/bitacora.php"><i class="fas fa-book"></i><span class="nav-text">Bitácora</span></a>
    <a class="nav-link" href="/Proyecto_HandyHub/config/logout.php"><i class="fas fa-sign-out-alt"></i><span class="nav-text">Salir</span></a>
  </nav>

  <div class="sidebar-footer">
    <div class="user">
      <i class="fas fa-user-circle" style="font-size:20px;"></i>
      <div class="user-info">
        <div style="font-weight:700;"><?= htmlspecialchars($usuario) ?></div>
        <div style="font-size:12px;color:#c9d6dd;"><?= htmlspecialchars($rol) ?></div>
      </div>
    </div>
  </div>
</aside>

<script>
  (function(){
    const btn = document.getElementById('toggleSidebar');
    function applyState(collapsed){
      if(collapsed) document.body.classList.add('sidebar-collapsed');
      else document.body.classList.remove('sidebar-collapsed');
    }
    const saved = localStorage.getItem('sidebarCollapsed') === '1';
    applyState(saved);
    if(btn) btn.addEventListener('click', ()=>{
      const now = document.body.classList.toggle('sidebar-collapsed');
      localStorage.setItem('sidebarCollapsed', now ? '1' : '0');
    });
  })();
</script>


<div id="sbTooltip" style="position:fixed;display:none;pointer-events:none;z-index:1200;"></div>
