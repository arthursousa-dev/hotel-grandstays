<?php
require_once __DIR__ . '/../bootstrap_sessao.php';
require __DIR__ . '/../../app/helpers.php';
exigirAdmin();

$pdo = conectar();
$totalHoteis  = (int) $pdo->query('SELECT COUNT(*) FROM hoteis WHERE ativo = 1')->fetchColumn();
$totalReservas = (int) $pdo->query('SELECT COUNT(*) FROM reservas')->fetchColumn();
$pendentes     = (int) $pdo->query("SELECT COUNT(*) FROM reservas WHERE status = 'pendente'")->fetchColumn();
$recentes      = $pdo->query(
    'SELECT r.*, h.nome AS hotel_nome FROM reservas r
     JOIN hoteis h ON h.id = r.hotel_id
     ORDER BY r.criado_em DESC LIMIT 5'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GrandStays — Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600;700&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php require __DIR__ . '/nav.php'; ?>

  <div class="page">
    <div class="container">
      <div class="sec-header">
        <h2 class="titulo">Olá, <?= limpar($_SESSION['admin_nome']) ?></h2>
        <div class="divider"></div>
      </div>

      <div class="grid-3">
        <div class="card-stat">
          <p class="stat-number"><?= $totalHoteis ?></p>
          <p class="stat-label">Hotéis ativos</p>
        </div>
        <div class="card-stat">
          <p class="stat-number"><?= $totalReservas ?></p>
          <p class="stat-label">Reservas no total</p>
        </div>
        <div class="card-stat">
          <p class="stat-number"><?= $pendentes ?></p>
          <p class="stat-label">Reservas pendentes</p>
        </div>
      </div>

      <div class="sec-header">
        <h2 class="titulo">Reservas recentes</h2>
        <div class="divider"></div>
      </div>

      <table style="width:100%;border-collapse:collapse">
        <thead>
          <tr style="text-align:left;border-bottom:1px solid #ddd">
            <th style="padding:8px">Hóspede</th>
            <th style="padding:8px">Hotel</th>
            <th style="padding:8px">Check-in</th>
            <th style="padding:8px">Check-out</th>
            <th style="padding:8px">Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentes as $r): ?>
            <tr style="border-bottom:1px solid #eee">
              <td style="padding:8px"><?= limpar($r['nome_hospede']) ?></td>
              <td style="padding:8px"><?= limpar($r['hotel_nome']) ?></td>
              <td style="padding:8px"><?= formatarDataBr($r['checkin']) ?></td>
              <td style="padding:8px"><?= formatarDataBr($r['checkout']) ?></td>
              <td style="padding:8px"><?= limpar($r['status']) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($recentes)): ?>
            <tr><td colspan="5" style="padding:8px">Nenhuma reserva ainda.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
