<?php
require_once __DIR__ . '/bootstrap_sessao.php';
require __DIR__ . '/../app/helpers.php';

$stmt = conectar()->query(
    'SELECT * FROM hoteis WHERE ativo = 1 ORDER BY avaliacao DESC LIMIT 3'
);
$destaques = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GrandStays — Início</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600;700&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php require __DIR__ . '/nav.php'; ?>

  <div class="page">

    <div class="hero">
      <div class="hero-body">
        <p class="badge">Bem-vindo à GrandStays</p>
        <h1 class="hero-title">Sua próxima<br><em>experiência</em><br>inesquecível</h1>
        <p class="hero-subtitle">Hotéis selecionados em todo o Brasil. Reserve com facilidade, viaje com conforto.</p>
        <a href="hoteis.php" class="btn-primary">Ver hotéis disponíveis</a>
        <a href="contato.php" class="btn-outline">Reservar agora</a>
      </div>
    </div>

    <div class="section">
      <div class="container">

        <div class="grid-3">
          <div class="card-stat">
            <p class="stat-number">120+</p>
            <p class="stat-label">Hotéis parceiros</p>
          </div>
          <div class="card-stat">
            <p class="stat-number">98%</p>
            <p class="stat-label">Clientes satisfeitos</p>
          </div>
          <div class="card-stat">
            <p class="stat-number">15 anos</p>
            <p class="stat-label">De experiência</p>
          </div>
        </div>

        <div class="sec-header">
          <p class="badge">Destaques da semana</p>
          <h2 class="titulo">Hotéis em destaque</h2>
          <p class="subtitulo">Os mais bem avaliados pelos nossos hóspedes.</p>
          <div class="divider"></div>
        </div>

        <div class="grid-3">
          <?php foreach ($destaques as $hotel): ?>
            <a href="hotel.php?id=<?= $hotel['id'] ?>" class="card">
              <div class="card-thumb">
                <img src="img/<?= limpar($hotel['imagem']) ?>" alt="<?= limpar($hotel['nome']) ?>">
                <span class="badge-img"><?= limpar($hotel['categoria']) ?></span>
              </div>
              <div class="card-body">
                <h3 class="card-title"><?= limpar($hotel['nome']) ?></h3>
                <p class="card-location"><?= limpar($hotel['localizacao']) ?></p>
                <div class="card-footer">
                  <div>
                    <span class="price-label">a partir de</span>
                    <span class="price"><?= formatarPreco($hotel['preco_noite']) ?> <span>/ noite</span></span>
                  </div>
                  <span class="rating">★ <?= number_format($hotel['avaliacao'], 1) ?></span>
                </div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>

      </div>
    </div>

  </div>

<?php require __DIR__ . '/footer.php'; ?>
</body>
</html>
