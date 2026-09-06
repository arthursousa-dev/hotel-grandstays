<?php
session_start();
require __DIR__ . '/../../app/helpers.php';

if (adminLogado()) {
    header('Location: dashboard.php');
    exit;
}

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValido($_POST['csrf_token'] ?? null)) {
        $erro = 'Sessão expirada. Recarregue a página e tente novamente.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $admin = autenticarAdmin($email, $senha);
        if ($admin) {
            session_regenerate_id(true);
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_nome'] = $admin['nome'];
            header('Location: dashboard.php');
            exit;
        }
        $erro = 'E-mail ou senha incorretos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GrandStays — Login administrativo</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600;700&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/style.css">
<style>
  .login-box { max-width: 380px; margin: 80px auto; }
</style>
</head>
<body>
  <div class="page">
    <div class="container">
      <div class="form-box login-box">
        <h3 class="form-title">Painel administrativo</h3>

        <?php if ($erro): ?>
          <p style="color:#c0392b;margin-bottom:16px"><?= limpar($erro) ?></p>
        <?php endif; ?>

        <form method="POST">
          <?= csrfCampo() ?>
          <div class="form-field">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" required autofocus>
          </div>
          <div class="form-field">
            <label for="senha">Senha</label>
            <input type="password" id="senha" name="senha" required>
          </div>
          <button type="submit" class="btn-submit">Entrar</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
