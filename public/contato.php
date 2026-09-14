<?php
require_once __DIR__ . '/bootstrap_sessao.php';
require __DIR__ . '/../app/helpers.php';

$hoteis = conectar()->query('SELECT id, nome FROM hoteis WHERE ativo = 1 ORDER BY nome')->fetchAll();

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
  <title>GrandStays — Contato</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600;700&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php require __DIR__ . '/nav.php'; ?>

  <div class="page">
    <div class="container">
      <div class="contact-grid">

        <div class="contact-side">
          <p class="badge">Fale conosco</p>
          <h2 class="titulo">Faça sua<br>reserva</h2>
          <div class="divider divider-left"></div>
          <p class="subtitulo" style="margin-top:20px">
            Preencha o formulário e nossa equipe entrará em contato em até 2 horas úteis para confirmar sua reserva.
          </p>
          <br>
          <div class="contact-item">
            <div class="contact-icon">📞</div>
            <div class="contact-text">
              <strong>Central de Reservas</strong>
              <span>(67) 99292-8122</span>
            </div>
          </div>
          <div class="contact-item">
            <div class="contact-icon">✉️</div>
            <div class="contact-text">
              <strong>E-mail</strong>
              <span>arthursousa.contatop@gmail.com</span>
            </div>
          </div>
          <div class="contact-item">
            <div class="contact-icon">🕐</div>
            <div class="contact-text">
              <strong>Atendimento</strong>
              <span>Segunda a Domingo, 7h às 22h</span>
            </div>
          </div>
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
            <input type="hidden" name="voltar_para" value="contato.php">

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
              <label for="hotel_id">Hotel desejado</label>
              <select id="hotel_id" name="hotel_id" required>
                <option value="" disabled selected>Selecione um hotel</option>
                <?php foreach ($hoteis as $h): ?>
                  <option value="<?= $h['id'] ?>"><?= limpar($h['nome']) ?></option>
                <?php endforeach; ?>
              </select>
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

<?php require __DIR__ . '/footer.php'; ?>
</body>
</html>
