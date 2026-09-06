<?php
session_start();
require __DIR__ . '/../../app/helpers.php';
exigirAdmin();

$pdo = conectar();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValido($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        die('Sessão expirada. Recarregue a página e tente novamente.');
    }
    $id = (int) ($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';
    if (in_array($status, ['pendente', 'confirmada', 'cancelada'], true)) {
        $stmt = $pdo->prepare('UPDATE reservas SET status = :status WHERE id = :id');
        $stmt->execute([':status' => $status, ':id' => $id]);
    }
    header('Location: reservas.php');
    exit;
}

$reservas = $pdo->query(
    'SELECT r.*, h.nome AS hotel_nome FROM reservas r
     JOIN hoteis h ON h.id = r.hotel_id
     ORDER BY r.criado_em DESC'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GrandStays — Reservas (admin)</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600;700&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php require __DIR__ . '/nav.php'; ?>

  <div class="page">
    <div class="container">
      <div class="sec-header" style="text-align:left">
        <h2 class="titulo">Reservas</h2>
        <div class="divider"></div>
      </div>

      <table style="width:100%;border-collapse:collapse">
        <thead>
          <tr style="text-align:left;border-bottom:1px solid #ddd">
            <th style="padding:8px">Hóspede</th>
            <th style="padding:8px">Hotel</th>
            <th style="padding:8px">Check-in</th>
            <th style="padding:8px">Check-out</th>
            <th style="padding:8px">Hóspedes</th>
            <th style="padding:8px">Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($reservas as $r): ?>
            <tr style="border-bottom:1px solid #eee">
              <td style="padding:8px">
                <?= limpar($r['nome_hospede']) ?><br>
                <small style="opacity:.6"><?= limpar($r['email']) ?></small>
              </td>
              <td style="padding:8px"><?= limpar($r['hotel_nome']) ?></td>
              <td style="padding:8px"><?= formatarDataBr($r['checkin']) ?></td>
              <td style="padding:8px"><?= formatarDataBr($r['checkout']) ?></td>
              <td style="padding:8px"><?= (int) $r['hospedes'] ?></td>
              <td style="padding:8px">
                <form method="POST" style="display:flex;gap:6px;align-items:center">
                  <?= csrfCampo() ?>
                  <input type="hidden" name="id" value="<?= $r['id'] ?>">
                  <select name="status" onchange="this.form.submit()">
                    <?php foreach (['pendente', 'confirmada', 'cancelada'] as $s): ?>
                      <option value="<?= $s ?>" <?= $r['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                  </select>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($reservas)): ?>
            <tr><td colspan="6" style="padding:8px">Nenhuma reserva ainda.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
