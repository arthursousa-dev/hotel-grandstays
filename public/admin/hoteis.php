<?php
session_start();
require __DIR__ . '/../../app/helpers.php';
exigirAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'excluir') {
    if (!csrfValido($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        die('Sessão expirada. Recarregue a página e tente novamente.');
    }
    $id = (int) ($_POST['id'] ?? 0);
    $stmt = conectar()->prepare('UPDATE hoteis SET ativo = 0 WHERE id = :id');
    $stmt->execute([':id' => $id]);
    header('Location: hoteis.php');
    exit;
}

$hoteis = conectar()->query('SELECT * FROM hoteis WHERE ativo = 1 ORDER BY nome')->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GrandStays — Hotéis (admin)</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600;700&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php require __DIR__ . '/nav.php'; ?>

  <div class="page">
    <div class="container">
      <div class="sec-header" style="display:flex;justify-content:space-between;align-items:center;text-align:left">
        <div>
          <h2 class="titulo">Hotéis</h2>
        </div>
        <a href="hotel-form.php" class="btn-primary">+ Novo hotel</a>
      </div>

      <table style="width:100%;border-collapse:collapse">
        <thead>
          <tr style="text-align:left;border-bottom:1px solid #ddd">
            <th style="padding:8px">Nome</th>
            <th style="padding:8px">Categoria</th>
            <th style="padding:8px">Preço/noite</th>
            <th style="padding:8px">Avaliação</th>
            <th style="padding:8px"></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($hoteis as $h): ?>
            <tr style="border-bottom:1px solid #eee">
              <td style="padding:8px"><?= limpar($h['nome']) ?></td>
              <td style="padding:8px"><?= limpar($h['categoria']) ?></td>
              <td style="padding:8px"><?= formatarPreco($h['preco_noite']) ?></td>
              <td style="padding:8px">★ <?= number_format($h['avaliacao'], 1) ?></td>
              <td style="padding:8px;white-space:nowrap">
                <a href="hotel-form.php?id=<?= $h['id'] ?>">Editar</a>
                &nbsp;·&nbsp;
                <form method="POST" style="display:inline" onsubmit="return confirm('Remover este hotel do catálogo?')">
                  <?= csrfCampo() ?>
                  <input type="hidden" name="acao" value="excluir">
                  <input type="hidden" name="id" value="<?= $h['id'] ?>">
                  <button type="submit" style="background:none;border:none;color:#c0392b;cursor:pointer;font:inherit;padding:0">Excluir</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
