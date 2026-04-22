# 📘 Relatório do Projeto — MangaVerse

## 1. Introdução

O **MangaVerse** é uma plataforma web de e-commerce dedicada à venda de mangás, light novels e merchandise otaku. O projeto foi desenvolvido como MVP (Minimum Viable Product) pronto para produção, seguindo a arquitetura **MVC (Model-View-Controller)** com tecnologias web modernas.

---

## 2. Objetivos do Projeto

- Criar uma loja online completa e funcional para venda de mangás
- Implementar um marketplace P2P (peer-to-peer) para utilizadores venderem entre si
- Sistema de autenticação seguro (login/registo)
- Carrinho de compras com integração de pagamento (Stripe)
- Sistema de suporte com tickets
- Formulário de contacto
- Design profissional e responsivo

---

## 3. Tecnologias Utilizadas

| Tecnologia | Utilização |
|---|---|
| **PHP 8+** | Backend (Controllers & Models) |
| **MySQL/MariaDB** | Base de dados relacional |
| **jQuery 3.7** | Frontend - AJAX, manipulação DOM |
| **Bootstrap 5.3** | Framework CSS responsivo |
| **SweetAlert2** | Alertas e notificações |
| **Stripe.js** | Integração de pagamentos |
| **HTML5/CSS3** | Estrutura e design |
| **XAMPP** | Ambiente de desenvolvimento |

---

## 4. Arquitetura MVC

O projeto segue uma arquitetura **MVC limpa**, onde a comunicação frontend-backend é feita exclusivamente via **AJAX (jQuery)**:

```
jQuery (View/Frontend)
    ↓ AJAX POST/GET
Controller (PHP)
    ↓ Chamadas de métodos
Model (PHP + PDO)
    ↓ Queries SQL
Base de Dados (MySQL)
```

### 4.1 Models (`assets/model/`)

| Ficheiro | Descrição |
|---|---|
| `modelAuth.php` | Autenticação: registo, login, verificação, perfil |
| `modelMangas.php` | Produtos: listagem, filtros, categorias, pesquisa |
| `modelCarrinho.php` | Carrinho: adicionar, remover, encomendas |
| `modelContacto.php` | Contacto: envio e listagem de mensagens |
| `modelSuporte.php` | Suporte: tickets, respostas, gestão |

### 4.2 Controllers (`assets/controller/`)

| Ficheiro | Ações disponíveis |
|---|---|
| `controllerAuth.php` | login, registar, logout, perfil, verificar |
| `controllerMangas.php` | listar, detalhe, categorias, pesquisar, destaques, criar |
| `controllerCarrinho.php` | listar, adicionar, atualizar, remover, finalizar, contar |
| `controllerContacto.php` | enviar |
| `controllerSuporte.php` | criar, listar, detalhe, responder, fechar |

### 4.3 Views (Páginas PHP/HTML)

| Página | Descrição |
|---|---|
| `index.html` | Página principal da loja |
| `marketplace.php` | Marketplace P2P entre utilizadores |
| `carrinho.php` | Carrinho + checkout com Stripe |
| `login.php` | Página de login |
| `registo.php` | Página de registo |
| `contacto.html` | Formulário de contacto |
| `suporte.php` | Sistema de tickets de suporte |

---

## 5. Base de Dados

**Nome:** `mangaverse`

### 5.1 Diagrama de Tabelas

| Tabela | Descrição | Campos principais |
|---|---|---|
| `utilizadores` | Utilizadores registados | id, nome, email, password, tipo, avatar |
| `categorias` | Categorias de produtos | id, nome, slug, descricao |
| `produtos` | Catálogo de produtos | id, nome, autor, preco, stock, categoria_id, imagem |
| `encomendas` | Encomendas realizadas | id, user_id, total, estado, metodo_pagamento |
| `encomenda_itens` | Itens de cada encomenda | id, encomenda_id, produto_id, quantidade, preco |
| `carrinho` | Carrinho ativo (server-side) | id, user_id, produto_id, quantidade |
| `contacto_mensagens` | Mensagens de contacto | id, nome, email, assunto, mensagem |
| `suporte_tickets` | Tickets de suporte | id, user_id, assunto, mensagem, estado, prioridade |
| `suporte_respostas` | Respostas a tickets | id, ticket_id, user_id, mensagem, tipo |
| `marketplace_anuncios` | Anúncios do marketplace | id, vendedor_id, nome, preco, condicao, estado |
| `pagamentos` | Registos de pagamento | id, encomenda_id, metodo, valor, estado, stripe_id |

### 5.2 Dados de teste

- **12 produtos** de exemplo inseridos (mangás populares)
- **5 categorias**: Mangá, Light Novel, Artbook, Merchandise, Edição Especial
- **1 utilizador admin**: admin@mangaverse.pt / 123456

---

## 6. Funcionalidades Implementadas

### 6.1 Autenticação
- Registo com validação de campos e email único
- Login com `password_hash` / `password_verify` (bcrypt)
- Sessões PHP (`$_SESSION`)
- Logout com destruição de sessão
- Proteção contra SQL Injection (PDO prepared statements)

### 6.2 Loja / Catálogo
- Listagem de produtos com filtros por categoria
- Pesquisa por nome/autor
- Filtro de preço (min/max)
- Ordenação (recente, preço, nome)
- Badges (Novo, Hot, Sale)
- Cards com gradientes de cores personalizados

### 6.3 Marketplace P2P
- Listagem de todos os produtos
- Filtros laterais (categoria, preço, pesquisa)
- Formulário de venda para utilizadores autenticados
- Secção "Os teus produtos" para vendedores

### 6.4 Carrinho de Compras
- Adicionar/remover produtos
- Atualizar quantidades
- Resumo com subtotal, envio e descontos
- Códigos promocionais (MANGA10, OTAKU20, WELCOME)
- Envio grátis para encomendas ≥ 30€

### 6.5 Checkout / Pagamento
- Checkout multi-step (Dados → Pagamento → Confirmação)
- Integração Stripe (cartão de crédito/débito)
- MB WAY (simulado)
- Transferência bancária
- Criação de encomenda na base de dados

### 6.6 Contacto
- Formulário de contacto com validação
- Informações de contacto (morada, email, telefone, horário)
- Secção FAQ
- Mapa placeholder

### 6.7 Suporte
- Sistema de tickets com 3 tabs (Criar, Os meus tickets, FAQ)
- Criação de ticket com assunto, categoria, prioridade, descrição
- Listagem de tickets do utilizador
- Detalhe de ticket com histórico de mensagens
- Resposta a tickets (chat-like)
- Fechar tickets
- 8 perguntas frequentes

---

## 7. Design & UI/UX

### 7.1 Identidade Visual

| Elemento | Valor |
|---|---|
| **Cor principal** | `#e8002d` (vermelho MangaVerse) |
| **Cor secundária** | `#0a0a0a` (preto) |
| **Font display** | Orbitron (títulos) |
| **Font body** | Noto Sans JP (texto) |
| **Font mono** | Space Mono (labels, código) |
| **Tema** | Light/White com acentos vermelhos |

### 7.2 Características do Design
- Tema claro e moderno
- Animação do logo dot (pulsante)
- Cards com gradientes nos covers
- Hover effects com elevação (translateY)
- Backdrop blur na navbar
- Reveal animations no scroll
- SweetAlert2 toasts com tema escuro
- Grid responsivo com breakpoints 768px, 900px e 1100px

---

## 8. Segurança

- **Password hashing**: bcrypt via `password_hash()`
- **SQL Injection**: PDO prepared statements em todas as queries
- **XSS Prevention**: `htmlspecialchars()` em todos os outputs
- **Session security**: regeneração de ID, validação server-side
- **CSRF**: Tokens em formulários sensíveis
- **Input validation**: Sanitização de inputs no backend

---

## 9. Estrutura de Ficheiros

```
PAP-manga/
├── index.html              # Página principal
├── marketplace.php         # Marketplace P2P
├── carrinho.php            # Carrinho + Checkout
├── login.php               # Login
├── registo.php             # Registo
├── contacto.html           # Contacto
├── suporte.php             # Suporte (tickets)
├── relatorio.md            # Este relatório
├── database/
│   └── mangaverse.sql      # Schema + dados de teste
├── assets/
│   ├── config/
│   │   └── database.php    # Configuração BD + helpers
│   ├── controller/
│   │   ├── controllerAuth.php
│   │   ├── controllerMangas.php
│   │   ├── controllerCarrinho.php
│   │   ├── controllerContacto.php
│   │   └── controllerSuporte.php
│   ├── model/
│   │   ├── modelAuth.php
│   │   ├── modelMangas.php
│   │   ├── modelCarrinho.php
│   │   ├── modelContacto.php
│   │   └── modelSuporte.php
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── script.js
```

---

## 10. Como Executar

### Pré-requisitos
- XAMPP (Apache + MySQL)
- PHP 8.0+
- Browser moderno

### Instalação

1. Copiar o projeto para `C:\xampp\htdocs\PAP-manga\`
2. Iniciar Apache e MySQL no XAMPP
3. Abrir o phpMyAdmin (`http://localhost/phpmyadmin`)
4. Importar o ficheiro `database/mangaverse.sql`
5. Aceder a `http://localhost/PAP-manga/`

### Credenciais de teste
- **Email:** admin@mangaverse.pt
- **Password:** z

### Códigos promocionais
- `MANGA10` — 10% desconto
- `OTAKU20` — 20% desconto
- `WELCOME` — 5% desconto

---

## 11. Fluxo de Utilização

```
1. Utilizador acede à loja (index.html)
2. Regista-se (registo.php) ou faz login (login.php)
3. Navega pelo catálogo e marketplace
4. Adiciona produtos ao carrinho
5. Vai ao carrinho (carrinho.php)
6. Aplica código promo (opcional)
7. Clica "Finalizar Compra"
8. Preenche dados de entrega
9. Seleciona método de pagamento
10. Confirma o pagamento
11. Recebe confirmação da encomenda
```

---

## 12. Conclusão

O MangaVerse é um MVP completo e funcional que demonstra competências em:

- Desenvolvimento full-stack (PHP + MySQL + jQuery)
- Arquitetura MVC bem estruturada
- Design UI/UX moderno e responsivo
- Integração de pagamentos (Stripe)
- Segurança web (OWASP best practices)
- Sistema de suporte ao cliente

O projeto está pronto para expansão com funcionalidades adicionais como gestão de inventário avançada, notificações por email, painel de administração, e integração com APIs de envio.

---

**Autor:** MangaVerse Team  
**Data:** 2026  
**Versão:** 1.0 MVP
