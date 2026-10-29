<?php
require_once __DIR__ . '/bootstrap_sessao.php';
require __DIR__ . '/../app/helpers.php';

$categoriasValidas = ['Luxo', 'Resort', 'Boutique', 'Econômico'];
$categoria = $_GET['categoria'] ?? '';
$categoria = in_array($categoria, $categoriasValidas, true) ? $categoria : null;

$busca = trim((string) ($_GET['q'] ?? ''));

$filtros = ['ativo'];
$params = [];
if ($categoria) {
    $filtros[] = 'categoria = :cat';
    $params[':cat'] = $categoria;
}
if ($busca !== '') {
    $filtros[] = '(nome ILIKE :q OR localizacao ILIKE :q2 OR comodidades ILIKE :q3)';
    $params[':q'] = $params[':q2'] = $params[':q3'] = termoBusca($busca);
}
$where = 'WHERE ' . implode(' AND ', $filtros);

$pdo = conectar();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM hoteis {$where}");
$stmt->execute($params);
$total = (int) $stmt->fetchColumn();
$pag = paginar($total, 6, (int) ($_GET['pagina'] ?? 1));

$stmt = $pdo->prepare("SELECT * FROM hoteis {$where} ORDER BY avaliacao DESC, nome LIMIT {$pag['limite']} OFFSET {$pag['offset']}");
$stmt->execute($params);
$hoteis = $stmt->fetchAll();

$link = fn(array $mudancas) => 'hoteis.php?' . http_build_query(array_filter(
    array_merge(['categoria' => $categoria, 'q' => $busca, 'pagina' => null], $mudancas),
    fn($v) => $v !== null && $v !== '' && $v !== 1
));
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

        <form class="busca" method="get" action="hoteis.php" role="search">
          <?php if ($categoria): ?><input type="hidden" name="categoria" value="<?= limpar($categoria) ?>"><?php endif; ?>
          <input type="search" name="q" value="<?= limpar($busca) ?>" placeholder="Buscar por nome, cidade ou comodidade" aria-label="Buscar hotéis" maxlength="80">
          <button type="submit" class="btn-dark">Buscar</button>
        </form>

        <div class="filters">
          <a href="<?= limpar($link(['categoria' => null])) ?>" class="filter-btn <?= !$categoria ? 'ativo' : '' ?>">Todos</a>
          <?php foreach ($categoriasValidas as $c): ?>
            <a href="<?= limpar($link(['categoria' => $c])) ?>" class="filter-btn <?= $categoria === $c ? 'ativo' : '' ?>"><?= $c ?></a>
          <?php endforeach; ?>
        </div>

        <p class="resultado-busca"><?= $total ?> <?= $total === 1 ? 'hotel encontrado' : 'hotéis encontrados' ?><?= $busca !== '' ? ' para “' . limpar($busca) . '”' : '' ?></p>

        <div class="grid-3">
          <?php if (empty($hoteis)): ?>
            <p>Nenhum hotel encontrado. <a href="hoteis.php">Ver todos</a></p>
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

        <?php if ($pag['total_paginas'] > 1): ?>
          <div class="paginacao" role="navigation" aria-label="Páginas do catálogo">
            <?php if ($pag['pagina'] > 1): ?><a href="<?= limpar($link(['pagina' => $pag['pagina'] - 1])) ?>">‹ Anterior</a><?php endif; ?>
            <?php for ($p = 1; $p <= $pag['total_paginas']; $p++): ?>
              <?php if ($p === $pag['pagina']): ?>
                <span aria-current="page"><?= $p ?></span>
              <?php else: ?>
                <a href="<?= limpar($link(['pagina' => $p])) ?>"><?= $p ?></a>
              <?php endif; ?>
            <?php endfor; ?>
            <?php if ($pag['pagina'] < $pag['total_paginas']): ?><a href="<?= limpar($link(['pagina' => $pag['pagina'] + 1])) ?>">Próxima ›</a><?php endif; ?>
          </div>
        <?php endif; ?>

      </div>
    </div>
  </div>

<?php require __DIR__ . '/footer.php'; ?>
</body>
</html>
