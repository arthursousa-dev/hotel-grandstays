<?php $paginaAtual = basename($_SERVER['SCRIPT_NAME']); ?>
  <nav>
    <a href="index.php" class="logo">Grand<span>Stays</span></a>
    <ul class="">
      <li><a href="index.php" class="<?= $paginaAtual === 'index.php' ? 'ativo' : '' ?>">Início</a></li>
      <li><a href="hoteis.php" class="<?= $paginaAtual === 'hoteis.php' ? 'ativo' : '' ?>">Hotéis</a></li>
      <li><a href="contato.php" class="<?= $paginaAtual === 'contato.php' ? 'ativo' : '' ?>">Contato</a></li>
      <?php if (adminLogado()): ?>
        <li><a href="admin/dashboard.php">Painel admin</a></li>
      <?php else: ?>
        <li><a href="admin/login.php">Área admin</a></li>
      <?php endif; ?>
    </ul>
  </nav>
