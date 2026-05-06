<?php
require_once '../assets/config/database.php';
initSession();
requireAdmin('../');

$currentPage = 'admin';
$basePath    = '../';

$pdo = getDB();

// ── Estatísticas ──────────────────────────────────────────────
$stats = [];
foreach ([
    'utilizadores'    => 'SELECT COUNT(*) FROM utilizadores',
    'produtos'        => 'SELECT COUNT(*) FROM produtos',
    'encomendas'      => 'SELECT COUNT(*) FROM encomendas',
    'tickets'         => 'SELECT COUNT(*) FROM suporte_tickets',
    'contactos'       => 'SELECT COUNT(*) FROM contactos',
    'receita'         => 'SELECT COALESCE(SUM(total), 0) FROM encomendas WHERE estado != "cancelada"',
] as $key => $sql) {
    $stats[$key] = $pdo->query($sql)->fetchColumn();
}

// ── Últimas encomendas ─────────────────────────────────────────
$ultimasEncomendas = $pdo->query(
    'SELECT e.id, u.nome AS cliente, e.total, e.estado, e.criado_em
     FROM encomendas e
     JOIN utilizadores u ON u.id = e.utilizador_id
     ORDER BY e.criado_em DESC LIMIT 8'
)->fetchAll(PDO::FETCH_ASSOC);

// ── Últimos tickets abertos ────────────────────────────────────
$ultimosTickets = $pdo->query(
    'SELECT t.id, u.nome AS utilizador, t.assunto, t.estado, t.criado_em
     FROM suporte_tickets t
     JOIN utilizadores u ON u.id = t.utilizador_id
     WHERE t.estado = "aberto"
     ORDER BY t.criado_em DESC LIMIT 6'
)->fetchAll(PDO::FETCH_ASSOC);

// ── Últimas mensagens de contacto ─────────────────────────────
$ultimosContactos = $pdo->query(
    'SELECT id, nome, email, assunto, criado_em
     FROM contactos
     ORDER BY criado_em DESC LIMIT 6'
)->fetchAll(PDO::FETCH_ASSOC);

// ── Livros comprados por cliente ───────────────────────────────
$comprasPorCliente = $pdo->query("
    SELECT u.id AS cliente_id, u.nome AS cliente, u.email,
           GROUP_CONCAT(DISTINCT p.nome SEPARATOR '||') AS livros,
           COUNT(DISTINCT e.id) AS total_encomendas,
           SUM(ei.quantidade) AS total_itens
    FROM encomendas e
    JOIN encomenda_itens ei ON ei.encomenda_id = e.id
    JOIN produtos p ON p.id = ei.produto_id
    JOIN utilizadores u ON u.id = e.utilizador_id
    WHERE e.estado != 'cancelada'
    GROUP BY u.id
    ORDER BY total_encomendas DESC
    LIMIT 20
")->fetchAll(PDO::FETCH_ASSOC);

// ── Dados para gráficos ───────────────────────────────────────

// Vendas dos últimos 7 meses
$vendasMensais = $pdo->query("
    SELECT DATE_FORMAT(criado_em, '%Y-%m') AS mes,
           COUNT(*) AS total_encomendas,
           COALESCE(SUM(total), 0) AS receita
    FROM encomendas
    WHERE estado != 'cancelado'
    GROUP BY mes
    ORDER BY mes DESC
    LIMIT 7
")->fetchAll(PDO::FETCH_ASSOC);
$vendasMensais = array_reverse($vendasMensais);

// Produtos por categoria
$produtosPorCategoria = $pdo->query("
    SELECT c.nome, COUNT(p.id) AS total
    FROM categorias c
    LEFT JOIN produtos p ON p.categoria_id = c.id AND p.ativo = 1
    GROUP BY c.id
    ORDER BY total DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Encomendas por estado
$encomendasPorEstado = $pdo->query("
    SELECT estado, COUNT(*) AS total
    FROM encomendas
    GROUP BY estado
")->fetchAll(PDO::FETCH_ASSOC);

// Tickets por estado
$ticketsPorEstado = $pdo->query("
    SELECT estado, COUNT(*) AS total
    FROM suporte_tickets
    GROUP BY estado
")->fetchAll(PDO::FETCH_ASSOC);

// Novos registos últimos 7 dias
$registosDiarios = $pdo->query("
    SELECT DATE(criado_em) AS dia, COUNT(*) AS total
    FROM utilizadores
    WHERE criado_em >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY dia
    ORDER BY dia
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Painel Admin — MangaVerse</title>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Noto+Sans+JP:wght@300;400;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
  <style>
    :root {
      --white: #ffffff; --off-white: #f7f7f5; --black: #0a0a0a;
      --accent: #e8002d; --accent2: #0057ff; --grey: #8a8a8a;
      --light-grey: #ececec; --card-border: #e0e0e0;
      --glow: rgba(232,0,45,0.18);
      --font-display: 'Orbitron', sans-serif;
      --font-body: 'Noto Sans JP', sans-serif;
      --font-mono: 'Space Mono', monospace;
    }
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
    html { scroll-behavior: smooth; }
    body {
      font-family: var(--font-body);
      background:
        radial-gradient(circle at top right, rgba(232,0,45,0.05), transparent 28%),
        linear-gradient(180deg, #faf9f7 0%, #f4f3f1 100%);
      color: var(--black);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* ─── ADMIN LAYOUT ─── */
    .admin-wrap { padding-top: 72px; padding-bottom: 32px; flex: 1; }

    .admin-header {
      padding: 24px 32px 0;
    }
    .admin-header-inner {
      max-width: 1440px;
      margin: 0 auto;
      background: var(--black);
      color: white;
      padding: 36px clamp(24px, 4vw, 44px) 30px;
      border-radius: 24px;
      position: relative;
      overflow: hidden;
      box-shadow: 0 24px 60px rgba(0,0,0,0.12);
    }
    .admin-header-inner::before {
      content: 'ADMIN'; position: absolute; right: -20px; top: 50%;
      transform: translateY(-50%); font-family: var(--font-display);
      font-size: 9rem; font-weight: 900; color: rgba(255,255,255,0.04);
      pointer-events: none; letter-spacing: -4px;
    }
    .admin-eyebrow {
      font-family: var(--font-mono); font-size: 0.65rem; letter-spacing: 0.22em;
      text-transform: uppercase; color: var(--accent); margin-bottom: 12px;
      display: flex; align-items: center; gap: 10px;
    }
    .admin-eyebrow::before { content: ''; width: 24px; height: 1.5px; background: var(--accent); }
    .admin-header h1 {
      font-family: var(--font-display); font-size: clamp(1.6rem, 3vw, 2.4rem);
      font-weight: 900; margin-bottom: 6px;
    }
    .admin-header p { color: rgba(255,255,255,0.45); font-size: 0.9rem; }

    .stats-section,
    .charts-section,
    .tables-section,
    .contactos-section,
    .compras-section {
      width: min(1440px, calc(100% - 64px));
      margin: 20px auto 0;
      padding: 0;
    }

    /* ─── STATS GRID ─── */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(6, minmax(0, 1fr));
      gap: 12px;
    }
    .stat-card {
      background: #fff;
      border: 1.5px solid var(--card-border);
      border-radius: 14px;
      padding: 14px 16px;
      display: flex;
      flex-direction: column;
      gap: 6px;
      overflow: hidden;
      position: relative;
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 3px;
      background: var(--sc-color, var(--card-border));
      border-radius: 14px 14px 0 0;
    }
    .stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(0,0,0,0.07);
    }
    .stat-card.sc-red    { --sc-color: #e8002d; }
    .stat-card.sc-blue   { --sc-color: #0057ff; }
    .stat-card.sc-green  { --sc-color: #1a9c5b; }
    .stat-card.sc-orange { --sc-color: #f97316; }
    .stat-card.sc-purple { --sc-color: #7c3aed; }
    .stat-card.sc-teal   { --sc-color: #0ea5e9; }
    .stat-icon {
      font-family: var(--font-mono);
      font-size: 0.58rem;
      letter-spacing: 0.14em;
      text-transform: uppercase;
      color: var(--sc-color, var(--grey));
      font-weight: 700;
    }
    .stat-value {
      font-family: var(--font-display);
      font-size: clamp(1.1rem, 1.4vw, 1.5rem);
      font-weight: 900;
      line-height: 1;
      color: var(--black);
    }
    .stat-label {
      font-family: var(--font-mono); font-size: 0.55rem; letter-spacing: 0.14em;
      text-transform: uppercase; color: var(--grey);
    }
    .stat-link {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      margin-top: 4px;
      font-family: var(--font-mono);
      font-size: 0.52rem;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      color: var(--sc-color, var(--accent));
      text-decoration: none;
      opacity: 0.75;
      transition: opacity 0.2s;
    }
    .stat-link:hover { opacity: 1; }

    /* ─── TABLES SECTION ─── */
    .tables-grid { display: grid; grid-template-columns: minmax(0, 1.35fr) minmax(340px, 0.92fr); gap: 18px; }
    .panel-card {
      background: rgba(255,255,255,0.96);
      border: 1.5px solid var(--card-border);
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 14px 36px rgba(0,0,0,0.04);
    }
    .panel-card-header {
      padding: 16px 18px 14px; border-bottom: 1.5px solid var(--light-grey);
      display: flex; align-items: center; justify-content: space-between;
      gap: 12px;
    }
    .panel-card-title {
      font-family: var(--font-mono); font-size: 0.68rem; letter-spacing: 0.18em;
      text-transform: uppercase; color: var(--black); font-weight: 700;
    }
    .panel-card-more {
      font-family: var(--font-mono); font-size: 0.58rem; letter-spacing: 0.12em;
      text-transform: uppercase; color: var(--accent); text-decoration: none;
      transition: opacity 0.2s; opacity: 0.7;
    }
    .panel-card-more:hover { opacity: 1; }
    .panel-table-wrap { overflow-x: auto; }
    .panel-table { width: 100%; border-collapse: collapse; }
    .panel-table--wide { min-width: 760px; }
    .panel-table th {
      font-family: var(--font-mono); font-size: 0.58rem; letter-spacing: 0.15em;
      text-transform: uppercase; color: var(--grey); padding: 12px 18px 10px;
      text-align: left; border-bottom: 1px solid var(--light-grey);
    }
    .panel-table td { padding: 12px 18px; font-size: 0.84rem; border-bottom: 1px solid var(--off-white); vertical-align: middle; }
    .panel-table tr:last-child td { border-bottom: none; }
    .panel-table tr:hover td { background: var(--off-white); }
    .estado-badge {
      display: inline-block; padding: 3px 10px; border-radius: 20px;
      font-family: var(--font-mono); font-size: 0.58rem; letter-spacing: 0.1em; text-transform: uppercase;
    }
    .estado-pendente  { background: #fff3cd; color: #856404; }
    .estado-pago      { background: #d1e7dd; color: #0f5132; }
    .estado-enviado   { background: #cfe2ff; color: #084298; }
    .estado-entregue  { background: #d1e7dd; color: #0f5132; }
    .estado-cancelada { background: #f8d7da; color: #842029; }
    .estado-aberto    { background: #fff3cd; color: #856404; }
    .estado-em_analise{ background: #cfe2ff; color: #084298; }
    .estado-resolvido { background: #d1e7dd; color: #0f5132; }
    .td-mono { font-family: var(--font-mono); font-size: 0.78rem; color: var(--grey); }

    /* ─── CONTACTOS SECTION ─── */
    .contactos-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px; }
    .contacto-card {
      background: rgba(255,255,255,0.96); border: 1.5px solid var(--card-border); border-radius: 16px;
      padding: 18px 18px 16px;
    }
    .contacto-card .nome { font-weight: 700; font-size: 0.92rem; margin-bottom: 2px; }
    .contacto-card .email { font-family: var(--font-mono); font-size: 0.62rem; color: var(--grey); margin-bottom: 8px; }
    .contacto-card .assunto { font-size: 0.85rem; color: var(--black); margin-bottom: 6px; }
    .contacto-card .data { font-family: var(--font-mono); font-size: 0.58rem; color: var(--grey); letter-spacing: 0.08em; }

    /* ─── COMPRAS POR CLIENTE ─── */
    .livros-list { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
    .livro-badge {
      background: var(--off-white); border: 1px solid var(--card-border); border-radius: 6px;
      padding: 3px 10px; font-size: 0.72rem; font-family: var(--font-body); color: var(--black);
    }
    .btn-toggle-livros {
      background: var(--black); color: var(--white); border: none; border-radius: 4px;
      padding: 5px 14px; font-family: var(--font-mono); font-size: 0.6rem;
      letter-spacing: 0.1em; text-transform: uppercase; cursor: pointer;
      transition: background 0.2s;
    }
    .btn-toggle-livros:hover { background: var(--accent); }

    /* ─── EMPTY STATE ─── */
    .empty-row td { text-align: center; color: var(--grey); font-size: 0.82rem; padding: 28px; }

    /* ─── CHARTS ─── */
    .charts-grid { display: grid; grid-template-columns: minmax(0, 1.35fr) minmax(300px, 0.95fr); gap: 16px; }
    .chart-card {
      background: rgba(255,255,255,0.96);
      border: 1.5px solid var(--card-border);
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 14px 36px rgba(0,0,0,0.04);
    }
    .chart-card-header {
      padding: 14px 16px 12px; border-bottom: 1.5px solid var(--light-grey);
    }
    .chart-card-title {
      font-family: var(--font-mono); font-size: 0.56rem; letter-spacing: 0.18em;
      text-transform: uppercase; color: var(--black); font-weight: 700;
    }
    .chart-card-body { padding: 14px 16px 18px; position: relative; min-height: 250px; }
    .chart-card-body.compact { min-height: 210px; }
    .chart-card-body canvas { width: 100% !important; }
    .charts-grid-3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; margin-top: 16px; }

    @media (max-width: 1400px) {
      .stats-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    @media (max-width: 1100px) {
      .admin-header { padding-left: 20px; padding-right: 20px; }
      .stats-section,
      .charts-section,
      .tables-section,
      .contactos-section,
      .compras-section { width: calc(100% - 40px); }
      .tables-grid { grid-template-columns: 1fr; }
      .charts-grid { grid-template-columns: 1fr; }
      .charts-grid-3 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 768px) {
      .admin-header-inner { border-radius: 20px; padding: 28px 22px 24px; }
      .admin-header-inner::before { font-size: 5.5rem; right: -8px; }
      .stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
      .charts-grid-3 { grid-template-columns: 1fr; }
      .panel-card-header { align-items: flex-start; flex-direction: column; }
      .panel-table--wide { min-width: 680px; }
      .chart-card-body { min-height: 220px; }
    }
    @media (max-width: 560px) {
      .admin-header,
      .stats-section,
      .charts-section,
      .tables-section,
      .contactos-section,
      .compras-section { width: calc(100% - 28px); padding-left: 0; padding-right: 0; }
      .admin-header { padding-top: 14px; }
      .stats-grid { grid-template-columns: 1fr; }
      .stat-card { padding: 14px; }
      .panel-table--wide { min-width: 620px; }
    }
  </style>
</head>
<body>

<?php require_once '../assets/includes/navbar.php'; ?>

<div class="admin-wrap">

  <!-- ─── HEADER ─── -->
  <div class="admin-header">
    <div class="admin-header-inner">
      <div class="admin-eyebrow">// Painel de Controlo</div>
      <h1>Admin Dashboard</h1>
      <p>Bem-vindo, <?= htmlspecialchars($_SESSION['user_nome']) ?>. Aqui tens uma visão rápida da plataforma, com foco nos dados mais úteis.</p>
    </div>
  </div>

  <!-- ─── STATS ─── -->
  <section class="stats-section">
    <div class="stats-grid">
      <div class="stat-card sc-blue">
        <div class="stat-icon">UT</div>
        <div class="stat-value"><?= number_format($stats['utilizadores']) ?></div>
        <div class="stat-label">Utilizadores</div>
        <a href="#" class="stat-link">Ver todos →</a>
      </div>
      <div class="stat-card sc-teal">
        <div class="stat-icon">PD</div>
        <div class="stat-value"><?= number_format($stats['produtos']) ?></div>
        <div class="stat-label">Produtos</div>
        <a href="../marketplace.php" class="stat-link">Ver loja →</a>
      </div>
      <div class="stat-card sc-orange">
        <div class="stat-icon">EC</div>
        <div class="stat-value"><?= number_format($stats['encomendas']) ?></div>
        <div class="stat-label">Encomendas</div>
        <a href="#" class="stat-link">Ver todas →</a>
      </div>
      <div class="stat-card sc-purple">
        <div class="stat-icon">TK</div>
        <div class="stat-value"><?= number_format($stats['tickets']) ?></div>
        <div class="stat-label">Tickets Suporte</div>
        <a href="../suporte.php" class="stat-link">Ver suporte →</a>
      </div>
      <div class="stat-card sc-green">
        <div class="stat-icon">CT</div>
        <div class="stat-value"><?= number_format($stats['contactos']) ?></div>
        <div class="stat-label">Contactos</div>
        <a href="../contacto.php" class="stat-link">Ver formulário →</a>
      </div>
      <div class="stat-card sc-red">
        <div class="stat-icon">€</div>
        <div class="stat-value"><?= number_format($stats['receita'], 2, ',', '.') ?>€</div>
        <div class="stat-label">Receita Total</div>
      </div>
    </div>
  </section>

  <!-- ─── CHARTS ─── -->
  <section class="charts-section">
    <div class="charts-grid" style="margin-bottom:14px;">
      <!-- Receita Mensal -->
      <div class="chart-card">
        <div class="chart-card-header">
          <span class="chart-card-title">// Receita Mensal</span>
        </div>
        <div class="chart-card-body">
          <canvas id="chart-receita" height="80"></canvas>
        </div>
      </div>
      <!-- Produtos por Categoria -->
      <div class="chart-card">
        <div class="chart-card-header">
          <span class="chart-card-title">// Produtos por Categoria</span>
        </div>
        <div class="chart-card-body compact" style="display:flex;align-items:center;justify-content:center;">
          <canvas id="chart-categorias" height="80"></canvas>
        </div>
      </div>
    </div>
    <div class="charts-grid-3">
      <!-- Encomendas por Estado -->
      <div class="chart-card">
        <div class="chart-card-header">
          <span class="chart-card-title">// Encomendas por Estado</span>
        </div>
        <div class="chart-card-body compact" style="display:flex;align-items:center;justify-content:center;">
          <canvas id="chart-encomendas" height="60"></canvas>
        </div>
      </div>
      <!-- Tickets por Estado -->
      <div class="chart-card">
        <div class="chart-card-header">
          <span class="chart-card-title">// Tickets por Estado</span>
        </div>
        <div class="chart-card-body compact" style="display:flex;align-items:center;justify-content:center;">
          <canvas id="chart-tickets" height="60"></canvas>
        </div>
      </div>
      <!-- Novos Registos -->
      <div class="chart-card">
        <div class="chart-card-header">
          <span class="chart-card-title">// Registos (7 dias)</span>
        </div>
        <div class="chart-card-body">
          <canvas id="chart-registos" height="60"></canvas>
        </div>
      </div>
    </div>
  </section>

  <!-- ─── ENCOMENDAS + TICKETS ─── -->
  <section class="tables-section">
    <div class="tables-grid">

      <!-- Últimas encomendas -->
      <div class="panel-card">
        <div class="panel-card-header">
          <span class="panel-card-title">// Últimas Encomendas</span>
          <a href="#" class="panel-card-more">Ver todas →</a>
        </div>
        <div class="panel-table-wrap">
        <table class="panel-table panel-table--wide">
          <thead>
            <tr>
              <th>#</th>
              <th>Cliente</th>
              <th>Total</th>
              <th>Estado</th>
              <th>Data</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($ultimasEncomendas)): ?>
              <tr class="empty-row"><td colspan="5">Sem encomendas ainda.</td></tr>
            <?php else: foreach ($ultimasEncomendas as $enc): ?>
              <tr>
                <td class="td-mono">#<?= $enc['id'] ?></td>
                <td><?= htmlspecialchars($enc['cliente']) ?></td>
                <td class="td-mono"><?= number_format($enc['total'], 2, ',', '.') ?>€</td>
                <td><span class="estado-badge estado-<?= htmlspecialchars($enc['estado']) ?>"><?= htmlspecialchars($enc['estado']) ?></span></td>
                <td class="td-mono"><?= date('d/m/y', strtotime($enc['criado_em'])) ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
        </div>
      </div>

      <!-- Tickets abertos -->
      <div class="panel-card">
        <div class="panel-card-header">
          <span class="panel-card-title">// Tickets Abertos</span>
          <a href="../suporte.php" class="panel-card-more">Ver suporte →</a>
        </div>
        <div class="panel-table-wrap">
        <table class="panel-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Utilizador</th>
              <th>Assunto</th>
              <th>Data</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($ultimosTickets)): ?>
              <tr class="empty-row"><td colspan="4">Nenhum ticket aberto.</td></tr>
            <?php else: foreach ($ultimosTickets as $t): ?>
              <tr>
                <td class="td-mono">#<?= $t['id'] ?></td>
                <td><?= htmlspecialchars($t['utilizador']) ?></td>
                <td><?= htmlspecialchars(mb_strimwidth($t['assunto'], 0, 30, '…')) ?></td>
                <td class="td-mono"><?= date('d/m/y', strtotime($t['criado_em'])) ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
        </div>
      </div>

    </div>
  </section>

  <!-- ─── CONTACTOS ─── -->
  <?php if (!empty($ultimosContactos)): ?>
  <section class="contactos-section">
    <div class="panel-card-header" style="padding: 0 0 16px;">
      <span class="panel-card-title">// Últimas Mensagens de Contacto</span>
    </div>
    <div class="contactos-grid">
      <?php foreach ($ultimosContactos as $c): ?>
      <div class="contacto-card">
        <div class="nome"><?= htmlspecialchars($c['nome']) ?></div>
        <div class="email"><?= htmlspecialchars($c['email']) ?></div>
        <div class="assunto"><?= htmlspecialchars($c['assunto']) ?></div>
        <div class="data"><?= date('d/m/Y H:i', strtotime($c['criado_em'])) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- ─── COMPRAS POR CLIENTE ─── -->
  <?php if (!empty($comprasPorCliente)): ?>
  <section class="compras-section">
    <div class="panel-card">
      <div class="panel-card-header">
        <span class="panel-card-title">// Livros Comprados por Cliente</span>
      </div>
      <div class="panel-table-wrap">
      <table class="panel-table panel-table--wide">
        <thead>
          <tr>
            <th>Cliente</th>
            <th>Email</th>
            <th>Encomendas</th>
            <th>Itens</th>
            <th>Livros</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($comprasPorCliente as $cc): ?>
            <?php $livros = explode('||', $cc['livros']); ?>
            <tr>
              <td><?= htmlspecialchars($cc['cliente']) ?></td>
              <td class="td-mono" style="font-size:0.7rem"><?= htmlspecialchars($cc['email']) ?></td>
              <td class="td-mono"><?= (int)$cc['total_encomendas'] ?></td>
              <td class="td-mono"><?= (int)$cc['total_itens'] ?></td>
              <td>
                <button class="btn-toggle-livros" onclick="this.nextElementSibling.style.display=this.nextElementSibling.style.display==='none'?'flex':'none'">
                  Ver Livros (<?= count($livros) ?>) →
                </button>
                <div class="livros-list" style="display:none">
                  <?php foreach ($livros as $livro): ?>
                    <span class="livro-badge"><?= htmlspecialchars($livro) ?></span>
                  <?php endforeach; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      </div>
    </div>
  </section>
  <?php endif; ?>

</div><!-- /.admin-wrap -->

<!-- ─── FOOTER ─── -->
<?php require_once '../assets/includes/footer.php'; ?>

<script>
// ── Chart.js Init ──
const accent = '#e8002d';
const accentSoft = 'rgba(232,0,45,.15)';
const chartColors = ['#e8002d','#ff6384','#36a2eb','#ffce56','#4bc0c0','#9966ff','#ff9f40'];

// Receita Mensal (line)
const receitaRaw = <?= json_encode($vendasMensais) ?>;
const receitaLabels = receitaRaw.map(r => r.mes);
const receitaData = receitaRaw.map(r => parseFloat(r.receita));
new window.Chart(document.getElementById('chart-receita'),{type:'line',data:{labels:receitaLabels,datasets:[{label:'Receita (€)',data:receitaData,borderColor:accent,backgroundColor:accentSoft,fill:true,tension:.35,pointRadius:4,pointBackgroundColor:accent}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{callback:v=>'€'+v}},x:{grid:{display:false}}}}});

// Produtos por Categoria (doughnut)
const catRaw = <?= json_encode($produtosPorCategoria) ?>;
const catLabels = catRaw.map(c => c.nome);
const catData = catRaw.map(c => parseInt(c.total));
new window.Chart(document.getElementById('chart-categorias'),{type:'doughnut',data:{labels:catLabels,datasets:[{data:catData,backgroundColor:chartColors.slice(0,catLabels.length),borderWidth:0}]},options:{responsive:true,plugins:{legend:{position:'bottom',labels:{color:'#999',font:{size:11}}}}}});

// Encomendas por Estado (pie)
const encRaw = <?= json_encode($encomendasPorEstado) ?>;
const encLabels = encRaw.map(e => e.estado);
const encData = encRaw.map(e => parseInt(e.total));
new window.Chart(document.getElementById('chart-encomendas'),{type:'pie',data:{labels:encLabels,datasets:[{data:encData,backgroundColor:chartColors.slice(0,encLabels.length),borderWidth:0}]},options:{responsive:true,plugins:{legend:{position:'bottom',labels:{color:'#999',font:{size:11}}}}}});

// Tickets por Estado (pie)
const tickRaw = <?= json_encode($ticketsPorEstado) ?>;
const tickLabels = tickRaw.map(t => t.estado);
const tickData = tickRaw.map(t => parseInt(t.total));
new window.Chart(document.getElementById('chart-tickets'),{type:'pie',data:{labels:tickLabels,datasets:[{data:tickData,backgroundColor:chartColors.slice(0,tickLabels.length),borderWidth:0}]},options:{responsive:true,plugins:{legend:{position:'bottom',labels:{color:'#999',font:{size:11}}}}}});

// Registos Diários (bar)
const regRaw = <?= json_encode($registosDiarios) ?>;
const regLabels = regRaw.map(r => r.dia);
const regData = regRaw.map(r => parseInt(r.total));
new window.Chart(document.getElementById('chart-registos'),{type:'bar',data:{labels:regLabels,datasets:[{label:'Novos Utilizadores',data:regData,backgroundColor:accent,borderRadius:6,barPercentage:.6}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{stepSize:1}},x:{grid:{display:false}}}}});
</script>

</body>
</html>
