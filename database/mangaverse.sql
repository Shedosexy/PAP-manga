-- ════════════════════════════════════════════════════════
--  MangaVerse — Base de Dados
--  MySQL / MariaDB
-- ════════════════════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS mangaverse_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE mangaverse_db;

-- ─── UTILIZADORES ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS utilizadores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  avatar VARCHAR(255) DEFAULT NULL,
  role ENUM('cliente','vendedor','admin') DEFAULT 'cliente',
  criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─── CATEGORIAS ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS categorias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(50) NOT NULL UNIQUE,
  slug VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT INTO categorias (nome, slug) VALUES
  ('Mangá', 'manga'),
  ('Livro', 'livro'),
  ('Artbook', 'artbook'),
  ('Coleção', 'colecao');

-- ─── PRODUTOS ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS produtos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(200) NOT NULL,
  autor VARCHAR(150) NOT NULL,
  descricao TEXT,
  categoria_id INT NOT NULL,
  preco DECIMAL(10,2) NOT NULL,
  preco_antigo DECIMAL(10,2) DEFAULT NULL,
  stock INT DEFAULT 1,
  volume VARCHAR(50) DEFAULT NULL,
  badge ENUM('new','hot','sale') DEFAULT NULL,
  cor1 VARCHAR(7) DEFAULT '#0a0a0a',
  cor2 VARCHAR(7) DEFAULT '#e8002d',
  condicao ENUM('novo','usado','raro') DEFAULT 'novo',
  condicao_pct INT DEFAULT 100,
  vendedor_id INT DEFAULT NULL,
  imagem VARCHAR(255) DEFAULT NULL,
  ativo TINYINT(1) DEFAULT 1,
  criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (categoria_id) REFERENCES categorias(id),
  FOREIGN KEY (vendedor_id) REFERENCES utilizadores(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ─── ENCOMENDAS ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS encomendas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  utilizador_id INT NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  envio DECIMAL(10,2) DEFAULT 0.00,
  iva DECIMAL(10,2) DEFAULT 0.00,
  desconto DECIMAL(10,2) DEFAULT 0.00,
  total DECIMAL(10,2) NOT NULL,
  metodo_pagamento VARCHAR(50) DEFAULT 'cartao',
  stripe_payment_id VARCHAR(255) DEFAULT NULL,
  morada VARCHAR(255) DEFAULT NULL,
  cidade VARCHAR(100) DEFAULT NULL,
  codigo_postal VARCHAR(20) DEFAULT NULL,
  telefone VARCHAR(20) DEFAULT NULL,
  estado ENUM('pendente','pago','enviado','entregue','cancelado') DEFAULT 'pendente',
  criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (utilizador_id) REFERENCES utilizadores(id)
) ENGINE=InnoDB;

-- ─── ITENS DA ENCOMENDA ─────────────────────────────────
CREATE TABLE IF NOT EXISTS encomenda_itens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  encomenda_id INT NOT NULL,
  produto_id INT NOT NULL,
  quantidade INT NOT NULL DEFAULT 1,
  preco_unitario DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (encomenda_id) REFERENCES encomendas(id) ON DELETE CASCADE,
  FOREIGN KEY (produto_id) REFERENCES produtos(id)
) ENGINE=InnoDB;

-- ─── CARRINHO ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS carrinho (
  id INT AUTO_INCREMENT PRIMARY KEY,
  utilizador_id INT NOT NULL,
  produto_id INT NOT NULL,
  quantidade INT NOT NULL DEFAULT 1,
  criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (utilizador_id) REFERENCES utilizadores(id) ON DELETE CASCADE,
  FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
  UNIQUE KEY unique_cart_item (utilizador_id, produto_id)
) ENGINE=InnoDB;

-- ─── MENSAGENS DE CONTACTO ─────────────────────────────
CREATE TABLE IF NOT EXISTS contactos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  assunto VARCHAR(200) DEFAULT NULL,
  mensagem TEXT NOT NULL,
  lido TINYINT(1) DEFAULT 0,
  criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─── TICKETS DE SUPORTE ────────────────────────────────
CREATE TABLE IF NOT EXISTS suporte_tickets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  utilizador_id INT DEFAULT NULL,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  categoria VARCHAR(50) DEFAULT 'outro',
  prioridade ENUM('baixa','media','alta','urgente') DEFAULT 'media',
  assunto VARCHAR(200) NOT NULL,
  mensagem TEXT NOT NULL,
  estado ENUM('aberto','em_progresso','resolvido','fechado') DEFAULT 'aberto',
  criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (utilizador_id) REFERENCES utilizadores(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ─── RESPOSTAS DE SUPORTE ──────────────────────────────
CREATE TABLE IF NOT EXISTS suporte_respostas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ticket_id INT NOT NULL,
  utilizador_id INT DEFAULT NULL,
  mensagem TEXT NOT NULL,
  is_admin TINYINT(1) DEFAULT 0,
  criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (ticket_id) REFERENCES suporte_tickets(id) ON DELETE CASCADE,
  FOREIGN KEY (utilizador_id) REFERENCES utilizadores(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ─── DADOS DE EXEMPLO ──────────────────────────────────

-- Utilizadores de teste (password: 123456 para todos)
-- Hash gerado com password_hash('123456', PASSWORD_BCRYPT)
INSERT INTO utilizadores (nome, email, password, role) VALUES
  ('Admin MangaVerse', 'admin@mangaverse.pt',    '$2y$10$e9uGbV9xgkRvXwV0yepmTeCTKV8iED98gG71jz.H1piCXypdkbFB2', 'admin'),
  ('João Vendedor',    'vendedor@mangaverse.pt', '$2y$10$e9uGbV9xgkRvXwV0yepmTeCTKV8iED98gG71jz.H1piCXypdkbFB2', 'vendedor'),
  ('Ana Cliente',      'cliente@mangaverse.pt',  '$2y$10$e9uGbV9xgkRvXwV0yepmTeCTKV8iED98gG71jz.H1piCXypdkbFB2', 'cliente');

-- Produtos de exemplo
INSERT INTO produtos (nome, autor, descricao, categoria_id, preco, preco_antigo, stock, volume, badge, cor1, cor2, condicao, condicao_pct) VALUES
  ('One Piece', 'Eiichiro Oda', 'A aventura épica de Monkey D. Luffy para se tornar o Rei dos Piratas.', 1, 7.99, NULL, 50, 'Vol. 104', 'hot', '#e8002d', '#f7a500', 'novo', 100),
  ('Jujutsu Kaisen', 'Gege Akutami', 'Yuji Itadori junta-se à escola de feiticeiros para combater maldições.', 1, 6.99, NULL, 35, 'Vol. 24', 'new', '#0057ff', '#000000', 'novo', 100),
  ('Chainsaw Man', 'Tatsuki Fujimoto', 'Denji funde-se com o seu demónio motosserra para caçar demónios.', 1, 7.49, 9.99, 40, 'Vol. 16', 'sale', '#222222', '#e8002d', 'novo', 100),
  ('Berserk', 'Kentaro Miura', 'A jornada sombria do espadachim Guts num mundo medieval.', 1, 12.99, NULL, 20, 'Vol. 41', NULL, '#1a1a2e', '#c5a028', 'novo', 100),
  ('Attack on Titan', 'Hajime Isayama', 'A humanidade luta pela sobrevivência contra titãs gigantes.', 1, 8.99, 10.99, 30, 'Vol. 34', 'sale', '#3a3a3a', '#8b5a2b', 'novo', 100),
  ('Demon Slayer', 'Koyoharu Gotouge', 'Tanjiro embarca numa jornada para curar a sua irmã e vingar a sua família.', 1, 6.49, NULL, 45, 'Vol. 23', 'new', '#1a472a', '#c21807', 'novo', 100),
  ('Duna', 'Frank Herbert', 'A obra-prima da ficção científica sobre poder, religião e ecologia.', 2, 14.99, 18.99, 25, 'Ed. Especial', 'sale', '#c5a028', '#8b3a0a', 'novo', 100),
  ('Neuromancer', 'William Gibson', 'O romance cyberpunk que definiu um género inteiro.', 2, 11.99, NULL, 15, 'Edição 2026', 'new', '#0d1117', '#00ff88', 'novo', 100),
  ('Vinland Saga', 'Makoto Yukimura', 'A saga viking de Thorfinn na era dos exploradores nórdicos.', 1, 9.99, NULL, 22, 'Vol. 27', NULL, '#2c4a6e', '#d4a017', 'novo', 100),
  ('Tokyo Ghoul', 'Sui Ishida', 'Ken Kaneki torna-se meio-ghoul após um encontro fatídico.', 1, 7.99, 9.49, 35, 'Vol. 14', 'sale', '#1a0a2e', '#8b1a4a', 'novo', 100),
  ('Maus', 'Art Spiegelman', 'A graphic novel vencedora do Pulitzer sobre o Holocausto.', 2, 16.99, NULL, 18, 'Completo', 'hot', '#2d2d2d', '#f0f0f0', 'novo', 100),
  ('Blue Period', 'Tsubasa Yamaguchi', 'Um jovem descobre a sua paixão pela arte e luta para entrar na universidade.', 1, 7.49, NULL, 28, 'Vol. 14', 'new', '#1a3a6e', '#4a90d9', 'novo', 100);
