<?php
require_once __DIR__ . '/bootstrap_sessao.php';
require __DIR__ . '/../app/helpers.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = conectar()->prepare('SELECT * FROM hoteis WHERE id = :id AND ativo = 1');
$stmt->execute([':id' => $id]);
$hotel = $stmt->fetch();

if (!$hotel) {
    header('Location: hoteis.php');
    exit;
}

$erro = $_SESSION['reserva_erro'] ?? null;
unset($_SESSION['reserva_erro']);
$sucesso = $_SESSION['reserva_ok'] ?? null;
unset($_SESSION['reserva_ok']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GrandStays — <?= limpar($hotel['nome']) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600;700&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php require __DIR__ . '/nav.php'; ?>

  <div class="page">
    <div class="section">
      <div class="container">

        <div class="card" style="margin-bottom:40px">
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
          </div>
        </div>

        <div class="contact-grid">
          <div class="contact-side">
            <p class="badge">Reserva</p>
            <h2 class="titulo">Solicitar<br>reserva</h2>
            <div class="divider divider-left"></div>
            <p class="subtitulo" style="margin-top:20px">
              Preencha o formulário e nossa equipe entrará em contato em até 2 horas úteis para confirmar sua reserva.
            </p>
          </div>

          <div class="form-box">
            <h3 class="form-title">Dados da reserva</h3>

            <?php if ($erro): ?>
              <p style="color:#c0392b;margin-bottom:16px"><?= limpar($erro) ?></p>
            <?php endif; ?>
            <?php if ($sucesso): ?>
              <p style="color:#1e824c;margin-bottom:16px">Reserva enviada! Entraremos em contato para confirmar.</p>
            <?php endif; ?>

            <form action="reservar.php" method="post">
              <?= csrfCampo() ?>
              <input type="hidden" name="hotel_id" value="<?= $hotel['id'] ?>">
              <input type="hidden" name="voltar_para" value="hotel.php?id=<?= $hotel['id'] ?>">

              <div class="form-row">
                <div class="form-field" style="margin-bottom:0">
                  <label for="nome">Nome completo</label>
                  <input type="text" id="nome" name="nome" placeholder="Seu nome completo" required>
                </div>
                <div class="form-field" style="margin-bottom:0">
                  <label for="email">E-mail</label>
                  <input type="email" id="email" name="email" placeholder="seu@email.com" required>
                </div>
              </div>

              <div class="form-row">
                <div class="form-field" style="margin-bottom:0">
                  <label for="checkin">Data de check-in</label>
                  <input type="date" id="checkin" name="checkin" required>
                </div>
                <div class="form-field" style="margin-bottom:0">
                  <label for="checkout">Data de check-out</label>
                  <input type="date" id="checkout" name="checkout" required>
                </div>
              </div>

              <div class="form-field">
                <label for="hospedes">Número de hóspedes</label>
                <input type="number" id="hospedes" name="hospedes" min="1" max="10" value="2" required>
              </div>

              <div class="form-field">
                <label for="mensagem">Mensagem / Pedidos especiais</label>
                <textarea id="mensagem" name="mensagem" placeholder="Descreva seu pedido, preferências de quarto, horário de chegada..."></textarea>
              </div>

              <button type="submit" class="btn-submit">Enviar solicitação</button>
            </form>
          </div>
        </div>

      </div>
    </div>
  </div>

<?php require __DIR__ . '/footer.php'; ?>
</body>
</html>
