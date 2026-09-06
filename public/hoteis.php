<?php
session_start();
require __DIR__ . '/../app/helpers.php';

$categoriasValidas = ['Luxo', 'Resort', 'Boutique', 'Econômico'];
$categoria = $_GET['categoria'] ?? '';
$categoria = in_array($categoria, $categoriasValidas, true) ? $categoria : null;

$pdo = conectar();
if ($categoria) {
    $stmt = $pdo->prepare('SELECT * FROM hoteis WHERE ativo = 1 AND categoria = :cat ORDER BY avaliacao DESC');
    $stmt->execute([':cat' => $categoria]);
} else {
    $stmt = $pdo->query('SELECT * FROM hoteis WHERE ativo = 1 ORDER BY avaliacao DESC');
}
$hoteis = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GrandStays — Hotéis</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600;700&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php require __DIR__ . '/nav.php'; ?>

  <div class="page">
    <div class="section">
      <div class="container">

        <div class="sec-header">
          <p class="badge">Nosso catálogo</p>
          <h2 class="titulo">Escolha o seu hotel</h2>
          <p class="subtitulo">Conforto, localização e experiências únicas para cada tipo de viajante.</p>
          <div class="divider"></div>
        </div>

        <div class="filters">
          <a href="hoteis.php" class="filter-btn <?= !$categoria ? 'ativo' : '' ?>">Todos</a>
          <?php foreach ($categoriasValidas as $c): ?>
            <a href="hoteis.php?categoria=<?= urlencode($c) ?>" class="filter-btn <?= $categoria === $c ? 'ativo' : '' ?>"><?= $c ?></a>
          <?php endforeach; ?>
        </div>

        <div class="grid-3">
          <?php if (empty($hoteis)): ?>
            <p>Nenhum hotel encontrado nessa categoria.</p>
          <?php endif; ?>

          <?php foreach ($hoteis as $hotel): ?>
            <div class="card">
              <div class="card-thumb">
                <img src="img/<?= limpar($hotel['imagem']) ?>" alt="<?= limpar($hotel['nome']) ?>">
                <span class="badge-img"><?= limpar($hotel['categoria']) ?></span>
              </div>
              <div class="card-body">
                <h3 class="card-title"><?= limpar($hotel['nome']) ?></h3>
                <p class="card-location"><?= limpar($hotel['localizacao']) ?></p>
                <p class="card-desc"><?= limpar($hotel['descricao']) ?></p>
                <div class="tags">
                  <?php foreach (explode(',', $hotel['comodidades']) as $item): ?>
                    <span class="tag"><?= limpar($item) ?></span>
                  <?php endforeach; ?>
                </div>
                <div class="card-footer">
                  <div>
                    <span class="price-label">a partir de</span>
                    <span class="price"><?= formatarPreco($hotel['preco_noite']) ?> <span>/ noite</span></span>
                  </div>
                  <span class="rating">★ <?= number_format($hotel['avaliacao'], 1) ?></span>
                </div>
                <a href="hotel.php?id=<?= $hotel['id'] ?>" class="btn-dark">Reservar agora</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

      </div>
    </div>
  </div>

<?php require __DIR__ . '/footer.php'; ?>
</body>
</html>
