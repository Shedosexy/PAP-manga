# 🏯 MangaVerse — Marketplace de Manga Online

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" />
  <img src="https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white" />
  <img src="https://img.shields.io/badge/jQuery-3.7-0769AD?style=for-the-badge&logo=jquery&logoColor=white" />
  <img src="https://img.shields.io/badge/Chart.js-4.4-FF6384?style=for-the-badge&logo=chartdotjs&logoColor=white" />
</p>

---

## 📋 Índice

1. [Sobre o Projeto](#sobre-o-projeto)
2. [Funcionalidades](#funcionalidades)
3. [Arquitetura & Tecnologias](#arquitetura--tecnologias)
4. [Base de Dados](#base-de-dados)
5. [Instalação & Configuração](#instalação--configuração)
6. [Estrutura do Projeto](#estrutura-do-projeto)
7. [Utilizadores de Teste](#utilizadores-de-teste)
8. [Screenshots](#screenshots)
9. [Segurança](#segurança)
10. [Conclusão](#conclusão)

---

## Sobre o Projeto

O **MangaVerse** é uma plataforma web de e-commerce dedicada à compra e venda de manga. Desenvolvido como **Prova de Aptidão Profissional (PAP)**, o projeto demonstra competências full-stack em desenvolvimento web, desde o design da base de dados até à experiência do utilizador final.

### Objetivos

- Criar um marketplace funcional e visualmente apelativo para entusiastas de manga
- Implementar um sistema completo de autenticação com três perfis de utilizador
- Desenvolver um painel de administração com estatísticas e gestão de conteúdos
- Garantir uma experiência responsiva e moderna com suporte a **dark mode**

---

## Funcionalidades

### 🛒 Para Clientes
- **Catálogo de Manga** — Pesquisa, filtragem por categoria e visualização detalhada de produtos
- **Carrinho de Compras** — Adição, remoção e atualização de quantidades em tempo real (AJAX)
- **Checkout com Stripe** — Pagamento seguro com confirmação visual (página de sucesso/erro)
- **Sistema de Encomendas** — Registo automático de encomendas com morada, telefone e método de pagamento
- **Suporte por Tickets** — Criação de tickets com categorias e prioridades, chat em tempo real com o administrador

### 🏪 Para Vendedores
- **Gestão de Produtos** — Publicação de mangas no marketplace com imagem, preço e descrição
- **Painel Pessoal** — Visualização de vendas e produtos publicados

### 🔧 Para Administradores
- **Dashboard Analítico** — Cards com estatísticas gerais + gráficos interativos:
  - 📈 Receita mensal (gráfico de linha)
  - 🍩 Produtos por categoria (doughnut)
  - 🥧 Encomendas por estado (pie)
  - 🥧 Tickets por estado (pie)
  - 📊 Registos diários (barras)
- **Gestão de Encomendas** — Atualização de estados (pendente → enviado → entregue)
- **Gestão de Tickets** — Resposta e fecho de tickets de suporte
- **Gestão de Utilizadores** — Listagem e moderação de contas

### 🌙 Experiência de Utilizador
- **Dark Mode** — Alternância com persistência via `localStorage`
- **Design Responsivo** — Adaptação total a desktop, tablet e mobile
- **Feedback Visual** — Notificações com SweetAlert2 em todas as ações

---

## Arquitetura & Tecnologias

O projeto segue o padrão **MVC (Model-View-Controller)** adaptado para PHP nativo:

| Camada | Tecnologia | Descrição |
|--------|-----------|-----------|
| **Frontend** | HTML5, CSS3, Bootstrap 5.3, jQuery 3.7 | Interface responsiva e interativa |
| **Backend** | PHP 8.x | Lógica de negócio e API REST (AJAX/JSON) |
| **Base de Dados** | MySQL 8.0 (PDO) | Armazenamento relacional seguro |
| **Gráficos** | Chart.js 4.4 | Visualização de dados no admin |
| **Pagamentos** | Stripe.js | Processamento de pagamentos |
| **Alertas** | SweetAlert2 | Notificações elegantes |
| **Servidor** | XAMPP (Apache) | Ambiente de desenvolvimento local |

### Fontes Tipográficas
- **Orbitron** — Títulos e elementos display
- **Noto Sans JP** — Corpo de texto (referência à cultura japonesa)
- **Space Mono** — Elementos monospaced e técnicos

---

## Base de Dados

A base de dados `mangaverse_db` contém as seguintes tabelas:

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│  utilizadores   │     │    categorias    │     │    produtos     │
├─────────────────┤     ├─────────────────┤     ├─────────────────┤
│ id (PK)         │     │ id (PK)         │     │ id (PK)         │
│ nome            │     │ nome            │     │ nome            │
│ email (UNIQUE)  │     │ descricao       │     │ descricao       │
│ password (hash) │     │ imagem          │     │ preco           │
│ role            │     └─────────────────┘     │ imagem          │
│ data_criacao    │                             │ stock           │
└─────────────────┘                             │ categoria_id(FK)│
                                                │ vendedor_id(FK) │
                                                └─────────────────┘

┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│   encomendas    │     │ encomenda_itens  │     │    carrinho     │
├─────────────────┤     ├─────────────────┤     ├─────────────────┤
│ id (PK)         │     │ id (PK)         │     │ id (PK)         │
│ utilizador_id   │     │ encomenda_id(FK)│     │ utilizador_id   │
│ total           │     │ produto_id (FK) │     │ produto_id (FK) │
│ estado          │     │ quantidade      │     │ quantidade      │
│ morada          │     │ preco_unitario  │     └─────────────────┘
│ cidade          │     └─────────────────┘
│ codigo_postal   │
│ telefone        │     ┌─────────────────┐     ┌─────────────────┐
│ metodo_pagamento│     │ suporte_tickets │     │suporte_respostas│
│ data_criacao    │     ├─────────────────┤     ├─────────────────┤
└─────────────────┘     │ id (PK)         │     │ id (PK)         │
                        │ utilizador_id   │     │ ticket_id (FK)  │
┌─────────────────┐     │ assunto         │     │ utilizador_id   │
│    contactos    │     │ mensagem        │     │ mensagem        │
├─────────────────┤     │ categoria       │     │ is_admin        │
│ id (PK)         │     │ prioridade      │     │ data_criacao    │
│ nome            │     │ estado          │     └─────────────────┘
│ email           │     │ data_criacao    │
│ assunto         │     └─────────────────┘
│ mensagem        │
│ data_criacao    │
└─────────────────┘
```

---

## Instalação & Configuração

### Pré-requisitos
- **XAMPP** (Apache + MySQL + PHP 8.x)

### Passos

1. **Clonar o repositório** para a pasta `htdocs` do XAMPP:
   ```bash
   cd C:\xampp\htdocs
   git clone <repo-url> PAP-manga
   ```

2. **Criar a base de dados**:
   - Abrir o **phpMyAdmin** (`http://localhost/phpmyadmin`)
   - Criar base de dados: `mangaverse_db`
   - Importar o ficheiro `database/mangaverse.sql`

3. **Configurar a ligação** em `assets/config/database.php`:
   ```php
   $host = 'localhost';
   $dbname = 'mangaverse_db';
   $username = 'root';
   $password = '';
   ```

4. **Iniciar os serviços** Apache e MySQL no painel XAMPP

5. **Aceder ao site**: `http://localhost/PAP-manga/`

---

## Estrutura do Projeto

```
PAP-manga/
├── index.html              # Página principal
├── marketplace.html/.php   # Catálogo de produtos
├── carrinho.html/.php      # Carrinho de compras
├── login.php               # Autenticação
├── registo.php             # Registo de utilizadores
├── suporte.php             # Sistema de tickets
├── sucesso-pagamento.php   # Confirmação de pagamento
├── erro-pagamento.php      # Erro no pagamento
├── contacto.html           # Formulário de contacto
├── admin/
│   └── index.php           # Painel de administração
├── assets/
│   ├── config/
│   │   ├── database.php    # Configuração da BD
│   │   └── error_handler.php
│   ├── controller/         # Controladores (lógica)
│   │   ├── controllerAuth.php
│   │   ├── controllerCarrinho.php
│   │   ├── controllerContacto.php
│   │   ├── controllerMangas.php
│   │   └── controllerSuporte.php
│   ├── model/              # Modelos (acesso a dados)
│   │   ├── modelAuth.php
│   │   ├── modelCarrinho.php
│   │   ├── modelContacto.php
│   │   ├── modelmangas.php
│   │   └── modelSuporte.php
│   ├── includes/
│   │   ├── navbar.php      # Navegação c/ dark mode
│   │   └── footer.php      # Rodapé partilhado
│   ├── css/style.css       # Estilos globais
│   └── js/script.js        # Scripts globais
└── database/
    └── mangaverse.sql      # Schema + dados iniciais
```

---

## Utilizadores de Teste

| Role | Email | Password |
|------|-------|----------|
| **Admin** | admin@mangaverse.pt | 123456 |
| **Vendedor** | vendedor@mangaverse.pt | 123456 |
| **Cliente** | cliente@mangaverse.pt | 123456 |

---

## Screenshots

> *Inserir screenshots das seguintes páginas:*
> - Página inicial
> - Marketplace com filtros
> - Carrinho de compras
> - Página de sucesso de pagamento
> - Painel de administração com gráficos
> - Sistema de suporte (tickets)
> - Dark mode ativado

---

## Segurança

| Medida | Implementação |
|--------|---------------|
| **Hashing de passwords** | `password_hash()` com bcrypt |
| **Prepared Statements** | PDO com parâmetros vinculados em todas as queries |
| **Validação de sessão** | Verificação de autenticação em todas as rotas protegidas |
| **XSS Prevention** | `htmlspecialchars()` em todos os outputs |
| **Controlo de acesso** | Verificação de role (admin/vendedor/cliente) por funcionalidade |

---

## Conclusão

O **MangaVerse** representa a aplicação prática de conhecimentos adquiridos ao longo do curso, abrangendo:

- **Desenvolvimento full-stack** com PHP, MySQL, HTML/CSS/JS
- **Padrão MVC** para organização e manutenção do código
- **Integração de APIs** externas (Stripe para pagamentos)
- **UX/UI moderna** com design responsivo e dark mode
- **Segurança web** com proteção contra as principais vulnerabilidades
- **Gestão de projeto** com estrutura organizada e documentação completa

O projeto demonstra capacidade de conceber, desenvolver e entregar uma aplicação web completa, desde a modelação da base de dados até à interface final do utilizador.

---

<p align="center">
  <strong>MangaVerse</strong> — Desenvolvido como Prova de Aptidão Profissional<br>
  <sub>PHP • MySQL • Bootstrap • jQuery • Chart.js • Stripe</sub>
</p>




 