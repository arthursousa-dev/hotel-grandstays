<?php
session_start();
require __DIR__ . '/../app/helpers.php';

/**
 * Redirecionamento restrito a um destino conhecido (evita open redirect):
 * só aceita 'contato.php' ou 'hotel.php?id=<inteiro>'.
 */
function destinoSeguro(?string $bruto, int $hotelIdFallback = 0): string
{
    if ($bruto === 'contato.php') {
        return 'contato.php';
    }
    if ($bruto && preg_match('/^hotel\.php\?id=(\d+)$/', $bruto, $m)) {
        return 'hotel.php?id=' . (int) $m[1];
    }
    return $hotelIdFallback > 0 ? 'hotel.php?id=' . $hotelIdFallback : 'hoteis.php';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: hoteis.php');
    exit;
}

$hotelId = (int) ($_POST['hotel_id'] ?? 0);
$destino = destinoSeguro($_POST['voltar_para'] ?? null, $hotelId);

if (!csrfValido($_POST['csrf_token'] ?? null)) {
    $_SESSION['reserva_erro'] = 'Sessão expirada. Recarregue a página e tente novamente.';
    header('Location: ' . $destino);
    exit;
}

$nome     = trim($_POST['nome'] ?? '');
$email    = trim($_POST['email'] ?? '');
$checkin  = $_POST['checkin'] ?? '';
$checkout = $_POST['checkout'] ?? '';
$hospedes = (int) ($_POST['hospedes'] ?? 1);
$mensagem = trim($_POST['mensagem'] ?? '');

$erros = [];
if ($nome === '') $erros[] = 'nome';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = 'e-mail';
if (!$checkin || !$checkout || $checkout <= $checkin) $erros[] = 'datas';
if ($hospedes < 1 || $hospedes > 10) $erros[] = 'hóspedes';

$pdo = conectar();
$stmt = $pdo->prepare('SELECT id FROM hoteis WHERE id = :id AND ativo = 1');
$stmt->execute([':id' => $hotelId]);
if (!$stmt->fetch()) {
    $erros[] = 'hotel';
}

if (!empty($erros)) {
    $_SESSION['reserva_erro'] = 'Verifique os dados informados (' . implode(', ', $erros) . ') e tente novamente.';
    header('Location: ' . $destino);
    exit;
}

$insere = $pdo->prepare(
    'INSERT INTO reservas (hotel_id, nome_hospede, email, checkin, checkout, hospedes, mensagem)
     VALUES (:hotel_id, :nome, :email, :checkin, :checkout, :hospedes, :mensagem)'
);
$insere->execute([
    ':hotel_id' => $hotelId,
    ':nome'     => $nome,
    ':email'    => $email,
    ':checkin'  => $checkin,
    ':checkout' => $checkout,
    ':hospedes' => $hospedes,
    ':mensagem' => $mensagem !== '' ? $mensagem : null,
]);

$_SESSION['reserva_ok'] = true;
header('Location: ' . $destino);
exit;
