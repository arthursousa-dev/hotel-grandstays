<?php
/**
 * app/helpers.php
 * Funções compartilhadas: conexão, autenticação, CSRF, formatação.
 */

use App\Config\Database;

require_once __DIR__ . '/Config/Database.php';

function conectar(): PDO
{
    return Database::getConnection();
}

function limpar(string $valor): string
{
    return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
}

function formatarPreco(float $valor): string
{
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

/**
 * Converte o valor digitado em número. Aceita "1250", "1.250", "1.250,50" e o formato
 * do banco ("1250.00", que o formulário de edição traz preenchido).
 */
function lerDecimal(string $valor): float
{
    $valor = str_replace(['R$', ' '], '', trim($valor));
    if (preg_match('/^\d+(\.\d{1,2})?$/', $valor)) {
        return (float) $valor;
    }
    return (float) str_replace(['.', ','], ['', '.'], $valor);
}

/** Data real no formato AAAA-MM-DD; texto livre ou "2026-02-30" derrubava o INSERT com erro 500. */
function dataValida(string $data): bool
{
    $dt = DateTimeImmutable::createFromFormat('!Y-m-d', $data);
    return $dt !== false && $dt->format('Y-m-d') === $data;
}

/**
 * Valida o formulário de reserva. Devolve [dados limpos, lista de campos inválidos].
 * A existência do hotel é conferida à parte, no banco.
 */
function validarReserva(array $entrada, ?string $hoje = null): array
{
    $hoje ??= date('Y-m-d');
    $dados = [
        'hotel_id' => (int) ($entrada['hotel_id'] ?? 0),
        'nome'     => trim((string) ($entrada['nome'] ?? '')),
        'email'    => trim((string) ($entrada['email'] ?? '')),
        'checkin'  => trim((string) ($entrada['checkin'] ?? '')),
        'checkout' => trim((string) ($entrada['checkout'] ?? '')),
        'hospedes' => (int) ($entrada['hospedes'] ?? 1),
        'mensagem' => trim((string) ($entrada['mensagem'] ?? '')),
    ];

    $erros = [];
    if ($dados['nome'] === '' || preg_match_all('/./su', $dados['nome']) > 150) $erros[] = 'nome';
    if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL) || strlen($dados['email']) > 150) $erros[] = 'e-mail';
    if (!dataValida($dados['checkin']) || !dataValida($dados['checkout'])
        || $dados['checkin'] < $hoje || $dados['checkout'] <= $dados['checkin']) $erros[] = 'datas';
    if ($dados['hospedes'] < 1 || $dados['hospedes'] > 10) $erros[] = 'hóspedes';

    return [$dados, $erros];
}

/** Número de diárias entre check-in e check-out (datas já validadas). */
function noites(string $checkin, string $checkout): int
{
    return (new DateTimeImmutable($checkin))->diff(new DateTimeImmutable($checkout))->days;
}

/**
 * Metadados de paginação a partir do total de itens e da página pedida
 * (fora do intervalo vira a primeira ou a última página).
 */
function paginar(int $total, int $porPagina, int $pagina): array
{
    $totalPaginas = max(1, (int) ceil($total / $porPagina));
    $pagina = max(1, min($totalPaginas, $pagina));
    return ['pagina' => $pagina, 'total_paginas' => $totalPaginas, 'offset' => ($pagina - 1) * $porPagina, 'limite' => $porPagina];
}

/** Escapa % e _ para que o texto digitado na busca seja procurado literalmente no ILIKE. */
function termoBusca(string $texto): string
{
    return '%' . addcslashes(trim($texto), '%_\\') . '%';
}

/**
 * Envia ao hóspede o resumo da solicitação de reserva. Só envia com RESERVAS_EMAIL_REMETENTE
 * definido; falha do mail() vai para o log e não desfaz a reserva.
 */
function enviarConfirmacaoReserva(array $reserva, array $hotel): bool
{
    $remetente = getenv('RESERVAS_EMAIL_REMETENTE') ?: '';
    if (!filter_var($remetente, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $noites = noites($reserva['checkin'], $reserva['checkout']);
    $total = $noites * (float) $hotel['preco_noite'];
    $corpo = implode("\r\n", [
        "Olá, {$reserva['nome']}!",
        '',
        'Recebemos sua solicitação de reserva. Nossa equipe confirma a disponibilidade em até 2 horas úteis.',
        '',
        'Hotel: ' . $hotel['nome'] . ' — ' . $hotel['localizacao'],
        'Check-in: ' . formatarDataBr($reserva['checkin']),
        'Check-out: ' . formatarDataBr($reserva['checkout']),
        "Hóspedes: {$reserva['hospedes']}",
        "Diárias: {$noites} × " . formatarPreco((float) $hotel['preco_noite']) . ' = ' . formatarPreco($total) . ' (estimativa)',
        '',
        'GrandStays',
    ]);
    $assunto = '=?UTF-8?B?' . base64_encode('Recebemos sua reserva — ' . $hotel['nome']) . '?=';
    $cabecalhos = "From: GrandStays <{$remetente}>\r\nContent-Type: text/plain; charset=UTF-8";

    // O destinatário já passou por FILTER_VALIDATE_EMAIL, que não aceita quebra de linha.
    $enviado = @mail($reserva['email'], $assunto, $corpo, $cabecalhos);
    if (!$enviado) {
        error_log('enviarConfirmacaoReserva: mail() falhou para ' . $reserva['email']);
    }
    return $enviado;
}

function formatarDataBr(string $data): string
{
    $dt = date_create($data);
    return $dt ? date_format($dt, 'd/m/Y') : $data;
}

/** Autentica um admin contra admin_usuarios (senha em hash bcrypt). */
function autenticarAdmin(string $email, string $senha): array|false
{
    $pdo = conectar();
    $email = strtolower(trim($email));

    $stmt = $pdo->prepare('SELECT *, CASE WHEN bloqueado_ate > now() THEN 1 ELSE 0 END AS bloqueado FROM admin_usuarios WHERE email = :email');
    $stmt->execute([':email' => $email]);
    $admin = $stmt->fetch();

    if (!$admin) {
        return false;
    }
    // Comparado no banco: bloqueado_ate vem do now() do PostgreSQL, e strtotime() usaria o fuso do PHP.
    if ((int) $admin['bloqueado'] === 1) {
        return false;
    }
    if (!password_verify($senha, $admin['senha_hash'])) {
        $tentativas = (int) $admin['tentativas_login'] + 1;
        $bloqueio = $tentativas >= 5 ? "now() + interval '15 minutes'" : 'NULL';
        $pdo->prepare("UPDATE admin_usuarios SET tentativas_login = :t, bloqueado_ate = {$bloqueio} WHERE id = :id")
            ->execute([':t' => $tentativas, ':id' => $admin['id']]);
        return false;
    }

    $pdo->prepare('UPDATE admin_usuarios SET tentativas_login = 0, bloqueado_ate = NULL WHERE id = :id')
        ->execute([':id' => $admin['id']]);

    return $admin;
}

function adminLogado(): bool
{
    return !empty($_SESSION['admin_id']);
}

function exigirAdmin(): void
{
    if (!adminLogado()) {
        header('Location: login.php');
        exit;
    }
}

/** Token CSRF por sessão. */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfCampo(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

function csrfValido(?string $token): bool
{
    return !empty($_SESSION['csrf_token']) && !empty($token) && hash_equals($_SESSION['csrf_token'], $token);
}
