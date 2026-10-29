<?php

use PHPUnit\Framework\TestCase;

final class ReservaTest extends TestCase
{
    private const HOJE = '2026-10-29';

    private function reserva(array $sobrescreve = []): array
    {
        return validarReserva($sobrescreve + [
            'hotel_id' => '2',
            'nome' => 'Ana Souza',
            'email' => 'ana@exemplo.com',
            'checkin' => '2026-12-10',
            'checkout' => '2026-12-15',
            'hospedes' => '2',
            'mensagem' => '',
        ], self::HOJE);
    }

    public function test_reserva_valida(): void
    {
        [$dados, $erros] = $this->reserva();
        $this->assertSame([], $erros);
        $this->assertSame(2, $dados['hotel_id']);
        $this->assertSame(5, noites($dados['checkin'], $dados['checkout']));
    }

    public function test_datas_invalidas_no_passado_ou_invertidas(): void
    {
        $casos = [
            ['checkin' => '2026-02-30'],
            ['checkin' => '10/12/2026'],
            ['checkin' => '2026-10-28', 'checkout' => '2026-10-30'],
            ['checkin' => '2026-12-15', 'checkout' => '2026-12-15'],
            ['checkin' => '2026-12-15', 'checkout' => '2026-12-10'],
        ];
        foreach ($casos as $caso) {
            [, $erros] = $this->reserva($caso);
            $this->assertSame(['datas'], $erros, json_encode($caso));
        }
    }

    public function test_hospedes_fora_do_limite_e_campos_obrigatorios(): void
    {
        [, $erros] = $this->reserva(['hospedes' => '0', 'nome' => '', 'email' => 'x']);
        $this->assertSame(['nome', 'e-mail', 'hóspedes'], $erros);
        [, $erros] = $this->reserva(['hospedes' => '11']);
        $this->assertSame(['hóspedes'], $erros);
    }

    public function test_paginacao_limita_a_pagina_ao_intervalo(): void
    {
        $this->assertSame(['pagina' => 1, 'total_paginas' => 1, 'offset' => 0, 'limite' => 6], paginar(0, 6, 3));
        $this->assertSame(['pagina' => 2, 'total_paginas' => 2, 'offset' => 6, 'limite' => 6], paginar(7, 6, 2));
        $this->assertSame(2, paginar(7, 6, 99)['pagina']);
        $this->assertSame(1, paginar(7, 6, -4)['pagina']);
    }

    public function test_busca_trata_curingas_como_texto(): void
    {
        $this->assertSame('%Rio%', termoBusca('  Rio '));
        $this->assertSame('%100\%%', termoBusca('100%'));
        $this->assertSame('%a\_b%', termoBusca('a_b'));
    }

    public function test_preco_e_avaliacao_digitados_ou_do_banco(): void
    {
        $this->assertSame(1250.0, lerDecimal('1.250,00'));
        $this->assertSame(650.0, lerDecimal('650.00'));
        $this->assertSame(4.9, lerDecimal('4,9'));
    }

    public function test_sem_remetente_configurado_nao_envia_email(): void
    {
        putenv('RESERVAS_EMAIL_REMETENTE');
        [$dados] = $this->reserva();
        $this->assertFalse(enviarConfirmacaoReserva($dados, ['nome' => 'Hotel', 'localizacao' => 'SP', 'preco_noite' => '100.00']));
    }

    public function test_busca_no_banco_encontra_por_cidade_e_ignora_curinga(): void
    {
        $stmt = conectar()->prepare('SELECT nome FROM hoteis WHERE ativo AND (nome ILIKE :q OR localizacao ILIKE :q2) ORDER BY nome');
        $stmt->execute([':q' => termoBusca('curitiba'), ':q2' => termoBusca('curitiba')]);
        $this->assertSame(['Central Inn Curitiba'], $stmt->fetchAll(PDO::FETCH_COLUMN));

        $stmt->execute([':q' => termoBusca('%'), ':q2' => termoBusca('%')]);
        $this->assertSame([], $stmt->fetchAll(PDO::FETCH_COLUMN), '% digitado não pode virar "tudo"');
    }
}
