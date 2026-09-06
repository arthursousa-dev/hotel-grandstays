<?php $paginaAtual = basename($_SERVER['SCRIPT_NAME']); ?>
  <nav>
    <a href="dashboard.php" class="logo">Grand<span>Stays</span> <small style="font-size:12px;opacity:.6">admin</small></a>
    <ul class="">
      <li><a href="dashboard.php" class="<?= $paginaAtual === 'dashboard.php' ? 'ativo' : '' ?>">Dashboard</a></li>
      <li><a href="hoteis.php" class="<?= $paginaAtual === 'hoteis.php' ? 'ativo' : '' ?>">Hotéis</a></li>
      <li><a href="reservas.php" class="<?= $paginaAtual === 'reservas.php' ? 'ativo' : '' ?>">Reservas</a></li>
      <li><a href="../index.php">Ver site</a></li>
      <li><a href="logout.php">Sair</a></li>
    </ul>
  </nav>
