-- ============================================================
-- GrandStays — Plataforma de Reservas de Hotel
-- Schema MySQL
-- ============================================================

CREATE DATABASE IF NOT EXISTS grandstays
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE grandstays;

-- ------------------------------------------------------------
-- Tabela: admin_usuarios
-- Usuários com acesso ao painel administrativo
-- ------------------------------------------------------------
CREATE TABLE admin_usuarios (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nome        VARCHAR(120)  NOT NULL,
    email       VARCHAR(150)  NOT NULL UNIQUE,
    senha_hash  VARCHAR(255)  NOT NULL,
    criado_em   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabela: hoteis
-- ------------------------------------------------------------
CREATE TABLE hoteis (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    nome           VARCHAR(150)   NOT NULL,
    categoria      ENUM('Luxo','Resort','Boutique','Econômico') NOT NULL,
    localizacao    VARCHAR(150)   NOT NULL,
    descricao      TEXT           NOT NULL,
    comodidades    VARCHAR(255)   NOT NULL COMMENT 'lista separada por vírgula',
    preco_noite    DECIMAL(10,2)  NOT NULL,
    avaliacao      DECIMAL(2,1)   NOT NULL DEFAULT 5.0,
    imagem         VARCHAR(255)   NULL,
    ativo          TINYINT(1)     NOT NULL DEFAULT 1,
    criado_em      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabela: reservas
-- ------------------------------------------------------------
CREATE TABLE reservas (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id     INT            NOT NULL,
    nome_hospede VARCHAR(150)   NOT NULL,
    email        VARCHAR(150)   NOT NULL,
    checkin      DATE           NOT NULL,
    checkout     DATE           NOT NULL,
    hospedes     TINYINT UNSIGNED NOT NULL DEFAULT 1,
    mensagem     TEXT           NULL,
    status       ENUM('pendente','confirmada','cancelada') NOT NULL DEFAULT 'pendente',
    criado_em    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reservas_hotel FOREIGN KEY (hotel_id) REFERENCES hoteis(id)
        ON DELETE CASCADE,
    CONSTRAINT chk_datas CHECK (checkout > checkin)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Dados de demonstração — hotéis
-- ------------------------------------------------------------
INSERT INTO hoteis (nome, categoria, localizacao, descricao, comodidades, preco_noite, avaliacao, imagem) VALUES
('Grand Palace São Paulo', 'Luxo', 'Jardins, São Paulo — SP',
 'Um ícone de sofisticação no coração da capital paulistana. Quartos espaçosos, spa completo e restaurante premiado.',
 'Wi-Fi,Spa,Piscina,Academia', 650.00, 4.9, 'hotel1.jfif'),

('Maresias Resort', 'Resort', 'Praia de Maresias — SP',
 'Frente ao mar, com bangalôs privativos, esportes aquáticos e culinária de frutos do mar frescos todos os dias.',
 'Frente ao mar,Bangalô,Surfe', 890.00, 4.8, 'hotel2.jpg'),

('Villa Ouro Preto', 'Boutique', 'Centro Histórico — MG',
 'Casarão colonial restaurado com charme histórico, vista para as igrejas barrocas e café da manhã artesanal.',
 'Histórico,Café incluso,Vista', 380.00, 4.7, 'hotel3.jfif'),

('Central Inn Curitiba', 'Econômico', 'Centro, Curitiba — PR',
 'Localização central, quartos confortáveis e acesso fácil às principais atrações da cidade das flores.',
 'Wi-Fi,Estacionamento,AC', 210.00, 4.5, 'hotel4.jfif'),

('Atlântica Tower Rio', 'Luxo', 'Ipanema, Rio de Janeiro — RJ',
 'Vista panorâmica para o Cristo e o mar, rooftop bar exclusivo e serviço de concierge 24 horas.',
 'Rooftop,Piscina,Concierge', 780.00, 5.0, 'hotel5.jfif'),

('Pantanal Ecolodge', 'Resort', 'Corumbá, Mato Grosso do Sul — MS',
 'Imersão total na natureza com safáris fotográficos, trilhas guiadas e acomodações ecológicas de alto padrão.',
 'Ecoturismo,Safári,Pesca', 560.00, 4.9, 'hotel6.jfif');

-- ------------------------------------------------------------
-- Dados de demonstração — usuário administrativo
-- login: admin@grandstays.com  senha: admin123
-- ------------------------------------------------------------
INSERT INTO admin_usuarios (nome, email, senha_hash) VALUES
('Administrador', 'admin@grandstays.com', '$2b$10$3qBNBYBKEkuMGkIBQ5SLSeY2fFJVTGj.28sox/eL6EbqBNwQ8QrHe');
