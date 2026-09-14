<?php
require_once __DIR__ . '/../bootstrap_sessao.php';
require __DIR__ . '/../../app/helpers.php';
exigirAdmin();

$pdo = conectar();
$id = (int) ($_GET['id'] ?? 0);
$hotel = null;

if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM hoteis WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $hotel = $stmt->fetch();
    if (!$hotel) {
        header('Location: hoteis.php');
        exit;
    }
}

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValido($_POST['csrf_token'] ?? null)) {
        $erro = 'Sessão expirada. Recarregue a página e tente novamente.';
    } else {
        $nome        = trim($_POST['nome'] ?? '');
        $categoria   = $_POST['categoria'] ?? '';
        $localizacao = trim($_POST['localizacao'] ?? '');
        $descricao   = trim($_POST['descricao'] ?? '');
        $comodidades = trim($_POST['comodidades'] ?? '');
        $preco       = (float) str_replace(',', '.', $_POST['preco_noite'] ?? '0');
        $avaliacao   = (float) str_replace(',', '.', $_POST['avaliacao'] ?? '5');
        $imagem      = trim($_POST['imagem'] ?? 'hotel1.jfif');

        $categoriasValidas = ['Luxo', 'Resort', 'Boutique', 'Econômico'];

        if ($nome === '' || !in_array($categoria, $categoriasValidas, true) || $localizacao === '' || $preco <= 0) {
            $erro = 'Preencha nome, categoria, localização e um preço válido.';
        } else {
            if ($id > 0) {
                $stmt = $pdo->prepare(
                    'UPDATE hoteis SET nome=:nome, categoria=:categoria, localizacao=:localizacao,
                     descricao=:descricao, comodidades=:comodidades, preco_noite=:preco, avaliacao=:avaliacao, imagem=:imagem
                     WHERE id=:id'
                );
                $stmt->execute([
                    ':nome' => $nome, ':categoria' => $categoria, ':localizacao' => $localizacao,
                    ':descricao' => $descricao, ':comodidades' => $comodidades,
                    ':preco' => $preco, ':avaliacao' => $avaliacao, ':imagem' => $imagem, ':id' => $id,
                ]);
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO hoteis (nome, categoria, localizacao, descricao, comodidades, preco_noite, avaliacao, imagem)
                     VALUES (:nome, :categoria, :localizacao, :descricao, :comodidades, :preco, :avaliacao, :imagem)'
                );
                $stmt->execute([
                    ':nome' => $nome, ':categoria' => $categoria, ':localizacao' => $localizacao,
                    ':descricao' => $descricao, ':comodidades' => $comodidades,
                    ':preco' => $preco, ':avaliacao' => $avaliacao, ':imagem' => $imagem,
                ]);
            }
            header('Location: hoteis.php');
            exit;
        }
    }
}

$v = fn($campo, $default = '') => limpar($hotel[$campo] ?? $default);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GrandStays — <?= $hotel ? 'Editar' : 'Novo' ?> hotel</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600;700&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php require __DIR__ . '/nav.php'; ?>

  <div class="page">
    <div class="container">
      <div class="form-box" style="max-width:640px;margin:0 auto">
        <h3 class="form-title"><?= $hotel ? 'Editar hotel' : 'Novo hotel' ?></h3>

        <?php if ($erro): ?>
          <p style="color:#c0392b;margin-bottom:16px"><?= limpar($erro) ?></p>
        <?php endif; ?>

        <form method="POST">
          <?= csrfCampo() ?>

          <div class="form-field">
            <label for="nome">Nome</label>
            <input type="text" id="nome" name="nome" value="<?= $v('nome') ?>" required>
          </div>

          <div class="form-row">
            <div class="form-field" style="margin-bottom:0">
              <label for="categoria">Categoria</label>
              <select id="categoria" name="categoria" required>
                <?php foreach (['Luxo', 'Resort', 'Boutique', 'Econômico'] as $c): ?>
                  <option value="<?= $c ?>" <?= ($hotel['categoria'] ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-field" style="margin-bottom:0">
              <label for="localizacao">Localização</label>
              <input type="text" id="localizacao" name="localizacao" value="<?= $v('localizacao') ?>" required>
            </div>
          </div>

          <div class="form-field">
            <label for="descricao">Descrição</label>
            <textarea id="descricao" name="descricao"><?= $v('descricao') ?></textarea>
          </div>

          <div class="form-field">
            <label for="comodidades">Comodidades (separadas por vírgula)</label>
            <input type="text" id="comodidades" name="comodidades" value="<?= $v('comodidades') ?>" placeholder="Wi-Fi, Piscina, Spa">
          </div>

          <div class="form-row">
            <div class="form-field" style="margin-bottom:0">
              <label for="preco_noite">Preço por noite (R$)</label>
              <input type="text" id="preco_noite" name="preco_noite" value="<?= $v('preco_noite', '0') ?>" required>
            </div>
            <div class="form-field" style="margin-bottom:0">
              <label for="avaliacao">Avaliação (0 a 5)</label>
              <input type="text" id="avaliacao" name="avaliacao" value="<?= $v('avaliacao', '5.0') ?>">
            </div>
          </div>

          <div class="form-field">
            <label for="imagem">Arquivo de imagem (em /img)</label>
            <input type="text" id="imagem" name="imagem" value="<?= $v('imagem', 'hotel1.jfif') ?>">
          </div>

          <button type="submit" class="btn-submit"><?= $hotel ? 'Salvar alterações' : 'Cadastrar hotel' ?></button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
