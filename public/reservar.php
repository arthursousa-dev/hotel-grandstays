<?php
require_once __DIR__ . '/bootstrap_sessao.php';
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

[$reserva, $erros] = validarReserva($_POST);

$pdo = conectar();
$stmt = $pdo->prepare('SELECT id, nome, localizacao, preco_noite FROM hoteis WHERE id = :id AND ativo');
$stmt->execute([':id' => $reserva['hotel_id']]);
$hotel = $stmt->fetch();
if (!$hotel) {
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
    ':hotel_id' => $reserva['hotel_id'],
    ':nome'     => $reserva['nome'],
    ':email'    => $reserva['email'],
    ':checkin'  => $reserva['checkin'],
    ':checkout' => $reserva['checkout'],
    ':hospedes' => $reserva['hospedes'],
    ':mensagem' => $reserva['mensagem'] !== '' ? $reserva['mensagem'] : null,
]);

enviarConfirmacaoReserva($reserva, $hotel);

$_SESSION['reserva_ok'] = true;
header('Location: ' . $destino);
exit;
