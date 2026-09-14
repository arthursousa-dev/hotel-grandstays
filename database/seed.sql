-- GrandStays — dados de demonstração

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

-- login: admin@grandstays.com  senha: admin123
INSERT INTO admin_usuarios (nome, email, senha_hash) VALUES
('Administrador', 'admin@grandstays.com', '$2b$10$3qBNBYBKEkuMGkIBQ5SLSeY2fFJVTGj.28sox/eL6EbqBNwQ8QrHe');
