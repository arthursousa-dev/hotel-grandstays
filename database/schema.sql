-- ============================================================
-- GrandStays — Schema PostgreSQL
-- ============================================================

CREATE TYPE categoria_hotel AS ENUM ('Luxo', 'Resort', 'Boutique', 'Econômico');
CREATE TYPE status_reserva AS ENUM ('pendente', 'confirmada', 'cancelada');

-- ------------------------------------------------------------
-- admin_usuarios
-- ------------------------------------------------------------
CREATE TABLE admin_usuarios (
    id                SERIAL PRIMARY KEY,
    nome              VARCHAR(120) NOT NULL,
    email             VARCHAR(150) NOT NULL UNIQUE,
    senha_hash        VARCHAR(255) NOT NULL,
    tentativas_login  SMALLINT NOT NULL DEFAULT 0,
    bloqueado_ate     TIMESTAMP NULL,
    criado_em         TIMESTAMP NOT NULL DEFAULT now()
);

-- ------------------------------------------------------------
-- hoteis
-- ------------------------------------------------------------
CREATE TABLE hoteis (
    id             SERIAL PRIMARY KEY,
    nome           VARCHAR(150) NOT NULL,
    categoria      categoria_hotel NOT NULL,
    localizacao    VARCHAR(150) NOT NULL,
    descricao      TEXT NOT NULL,
    comodidades    VARCHAR(255) NOT NULL, -- lista separada por vírgula
    preco_noite    NUMERIC(10,2) NOT NULL,
    avaliacao      NUMERIC(2,1) NOT NULL DEFAULT 5.0,
    imagem         VARCHAR(255) NULL,
    ativo          BOOLEAN NOT NULL DEFAULT true,
    criado_em      TIMESTAMP NOT NULL DEFAULT now()
);

-- ------------------------------------------------------------
-- reservas
-- ------------------------------------------------------------
CREATE TABLE reservas (
    id           SERIAL PRIMARY KEY,
    hotel_id     INT NOT NULL REFERENCES hoteis(id) ON DELETE CASCADE,
    nome_hospede VARCHAR(150) NOT NULL,
    email        VARCHAR(150) NOT NULL,
    checkin      DATE NOT NULL,
    checkout     DATE NOT NULL,
    hospedes     SMALLINT NOT NULL DEFAULT 1,
    mensagem     TEXT NULL,
    status       status_reserva NOT NULL DEFAULT 'pendente',
    criado_em    TIMESTAMP NOT NULL DEFAULT now(),
    CONSTRAINT chk_datas CHECK (checkout > checkin)
);

CREATE INDEX idx_reservas_hotel ON reservas(hotel_id);
CREATE INDEX idx_hoteis_categoria ON hoteis(categoria);
