<?php

use PHPUnit\Framework\TestCase;

final class AutenticacaoTest extends TestCase
{
    private const EMAIL = 'teste.autenticacao@grandstays.local';
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = conectar();
        $this->tearDown();
        $this->pdo->prepare('INSERT INTO admin_usuarios (nome, email, senha_hash) VALUES (:n, :e, :h)')
            ->execute([':n' => 'Teste', ':e' => self::EMAIL, ':h' => password_hash('senha-certa', PASSWORD_DEFAULT)]);
    }

    protected function tearDown(): void
    {
        $this->pdo->prepare('DELETE FROM admin_usuarios WHERE email = :e')->execute([':e' => self::EMAIL]);
    }

    public function test_senha_certa_autentica(): void
    {
        $this->assertIsArray(autenticarAdmin(self::EMAIL, 'senha-certa'));
    }

    public function test_conta_bloqueia_apos_cinco_senhas_erradas(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->assertFalse(autenticarAdmin(self::EMAIL, 'errada'));
        }
        $this->assertFalse(autenticarAdmin(self::EMAIL, 'senha-certa'), 'durante o bloqueio nem a senha certa entra');
    }
}
