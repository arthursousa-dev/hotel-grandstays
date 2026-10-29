# GrandStays

Plataforma de reservas de hotel: catálogo dinâmico com filtro por categoria, solicitação de reserva persistida em banco de dados, e painel administrativo completo (CRUD de hotéis, gestão de status de reserva).

Convertido de um site estático (HTML/CSS puro, com hotéis fixos no markup) para uma aplicação PHP + PostgreSQL com backend funcional de verdade.

## Arquitetura

```
app/
├── Config/
│   └── Database.php     # conexão PDO em Singleton
└── helpers.php            # autenticação admin, CSRF, formatação

database/
└── schema.sql              # hoteis, reservas, admin_usuarios + seed

public/                     # document root
├── index.php, hoteis.php, hotel.php, contato.php
├── reservar.php             # processa a reserva (POST + CSRF + validação)
├── admin/
│   ├── login.php, logout.php, dashboard.php
│   ├── hoteis.php, hotel-form.php   # CRUD de hotéis
│   └── reservas.php                  # gestão de status
└── css/, img/
```

## Decisões técnicas

- **PDO com prepared statements** em toda query, contra **PostgreSQL**.
- **Senha do admin em bcrypt** (`password_hash`/`password_verify`), com **bloqueio de conta** após 5 tentativas de login incorretas seguidas (15 minutos).
- **Cookies de sessão** com `httponly`, `samesite=Lax` e `secure` (quando em HTTPS).
- **CSRF** em todo formulário que altera dado (reserva, login admin, CRUD de hotel, exclusão, mudança de status).
- **Redirecionamento pós-reserva restrito por allowlist** (`destinoSeguro()` em `reservar.php`) — evita open redirect, já que o destino de retorno vem de um campo do formulário.
- **Exclusão lógica** de hotel (`ativo = false`) em vez de `DELETE`, preservando o histórico de reservas já feitas para aquele hotel.
- **Busca e paginação no catálogo**: texto livre em nome, cidade e comodidades (`ILIKE` com `%` e `_` escapados, então o que o usuário digita é procurado literalmente), combinável com o filtro de categoria, 6 hotéis por página.
- **E-mail de confirmação ao hóspede** com datas, número de diárias e estimativa de valor (`RESERVAS_EMAIL_REMETENTE` no `.env`); falha de envio vai para o log e não desfaz a reserva.
- **Validação de datas no servidor**: checkout precisa ser depois do checkin, tanto via `CHECK` constraint no banco quanto em PHP antes do insert.

## Como rodar localmente

```bash
createdb grandstays
psql grandstays < database/schema.sql
psql grandstays < database/seed.sql

cp .env.example .env        # preencha DB_PASS; variáveis de ambiente do servidor têm prioridade

php -S localhost:8000 -t public
```

Acesse `http://localhost:8000`.

### Login administrativo de demonstração

| E-mail | Senha |
|---|---|
| admin@grandstays.com | admin123 |

## Testes

```bash
composer install
createdb grandstays_test && psql grandstays_test < database/schema.sql && psql grandstays_test < database/seed.sql
DB_NAME=grandstays_test vendor/bin/phpunit
```

Cobrem a validação da reserva (datas inexistentes, no passado ou invertidas, limite de hóspedes), a paginação, a busca com curingas digitados, a leitura de preços e o bloqueio de login. O GitHub Actions roda a suíte contra um PostgreSQL a cada push.

## Roadmap

- [ ] Integração com gateway de pagamento real (a página de pagamento anterior era só uma simulação visual)
- [x] E-mail de confirmação automático ao hóspede
- [x] Paginação e busca por texto na listagem de hotéis

---

Feito por Arthur Sousa — [LinkedIn](https://www.linkedin.com/in/arthur-sousa-ads/) · [GitHub](https://github.com/arthursousa-dev)
