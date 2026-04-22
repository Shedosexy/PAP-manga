<?php
require_once 'assets/config/database.php';
initSession();
$user = getLoggedUser();
$currentPage = 'marketplace';
$basePath    = '';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Marketplace — MangaVerse</title>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Noto+Sans+JP:wght@300;400;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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
    body { font-family: var(--font-body); background: var(--white); color: var(--black); overflow-x: hidden; }

    /* ─── NAVBAR ─── */
    nav {
      position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
      background: rgba(255,255,255,0.92); backdrop-filter: blur(16px);
      border-bottom: 1.5px solid var(--light-grey);
      display: flex; align-items: center; justify-content: space-between;
      padding: 0 48px; height: 72px;
    }
    /* navbar via assets/includes/navbar.php */

    .page-wrap { padding-top: 72px; }

    /* ─── HERO ─── */
    .mp-hero { background: var(--black); color: white; padding: 72px 80px 64px; position: relative; overflow: hidden; }
    .mp-hero::before { content: '市場'; position: absolute; right: 60px; top: 50%; transform: translateY(-50%); font-family: var(--font-display); font-size: 18rem; font-weight: 900; color: rgba(255,255,255,0.03); pointer-events: none; }
    .mp-hero-grid { position: absolute; inset: 0; background-image: linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px); background-size: 60px 60px; pointer-events: none; }
    .mp-hero-inner { position: relative; z-index: 2; display: grid; grid-template-columns: 1fr auto; align-items: end; gap: 40px; }
    .mp-eyebrow { font-family: var(--font-mono); font-size: 0.68rem; letter-spacing: 0.25em; text-transform: uppercase; color: var(--accent); margin-bottom: 18px; display: flex; align-items: center; gap: 12px; }
    .mp-eyebrow::before { content: ''; width: 32px; height: 1.5px; background: var(--accent); }
    .mp-title { font-family: var(--font-display); font-size: clamp(2.2rem, 4.5vw, 3.8rem); font-weight: 900; line-height: 1.05; margin-bottom: 18px; }
    .mp-title em { font-style: normal; color: var(--accent); }
    .mp-desc { font-size: 1rem; line-height: 1.75; color: rgba(255,255,255,0.55); max-width: 540px; }
    .mp-hero-stats { display: flex; gap: 40px; flex-shrink: 0; }
    .mp-stat { text-align: right; }
    .mp-stat-num { font-family: var(--font-display); font-size: 2.2rem; font-weight: 900; color: white; display: block; }
    .mp-stat-label { font-family: var(--font-mono); font-size: 0.62rem; letter-spacing: 0.18em; text-transform: uppercase; color: rgba(255,255,255,0.35); }

    /* ─── SELL BANNER ─── */
    .seller-banner { background: var(--accent); color: white; padding: 20px 80px; display: flex; align-items: center; justify-content: space-between; gap: 24px; }
    .seller-banner-text { font-family: var(--font-mono); font-size: 0.75rem; letter-spacing: 0.12em; text-transform: uppercase; display: flex; align-items: center; gap: 12px; }
    .seller-banner-text::before { content: '★'; font-size: 0.9rem; }
    .seller-banner-cta { background: white; color: var(--accent); padding: 8px 20px; border-radius: 4px; font-family: var(--font-mono); font-size: 0.68rem; letter-spacing: 0.12em; text-transform: uppercase; text-decoration: none; font-weight: 700; transition: all 0.2s; border: none; cursor: pointer; }
    .seller-banner-cta:hover { background: var(--black); color: white; }

    /* ─── LAYOUT ─── */
    .mp-layout { display: grid; grid-template-columns: 280px 1fr; gap: 0; align-items: start; min-height: 80vh; }

    /* ─── SIDEBAR ─── */
    .mp-sidebar { padding: 40px 32px; border-right: 1.5px solid var(--light-grey); position: sticky; top: 72px; max-height: calc(100vh - 72px); overflow-y: auto; }
    .sidebar-section { margin-bottom: 36px; }
    .sidebar-section-title { font-family: var(--font-mono); font-size: 0.6rem; letter-spacing: 0.22em; text-transform: uppercase; color: var(--grey); margin-bottom: 16px; padding-bottom: 10px; border-bottom: 1px solid var(--light-grey); }
    .sidebar-search { position: relative; }
    .sidebar-search input { width: 100%; padding: 10px 14px 10px 36px; border: 1.5px solid var(--card-border); border-radius: 6px; font-family: var(--font-mono); font-size: 0.7rem; letter-spacing: 0.05em; outline: none; color: var(--black); transition: border-color 0.2s; background: var(--white); }
    .sidebar-search input:focus { border-color: var(--black); }
    .sidebar-search::before { content: '⌕'; position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: var(--grey); font-size: 1rem; pointer-events: none; }
    .cat-list { display: flex; flex-direction: column; gap: 4px; }
    .cat-item { display: flex; align-items: center; justify-content: space-between; padding: 9px 12px; border-radius: 6px; cursor: pointer; transition: all 0.18s; border: 1.5px solid transparent; }
    .cat-item:hover { background: var(--off-white); }
    .cat-item.active { border-color: var(--black); background: var(--black); }
    .cat-item.active .cat-name { color: white; }
    .cat-item.active .cat-count { background: var(--accent); color: white; }
    .cat-name { font-family: var(--font-mono); font-size: 0.68rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--black); }
    .cat-count { font-family: var(--font-mono); font-size: 0.6rem; background: var(--light-grey); padding: 2px 8px; border-radius: 100px; color: var(--grey); }
    .price-inputs { display: flex; gap: 10px; align-items: center; }
    .price-input { flex: 1; padding: 8px 10px; border: 1.5px solid var(--card-border); border-radius: 6px; font-family: var(--font-mono); font-size: 0.7rem; outline: none; color: var(--black); transition: border-color 0.2s; }
    .price-input:focus { border-color: var(--black); }
    .price-sep { font-family: var(--font-mono); font-size: 0.65rem; color: var(--grey); }
    .apply-filters-btn { width: 100%; background: var(--black); color: white; border: none; padding: 12px; border-radius: 6px; font-family: var(--font-mono); font-size: 0.68rem; letter-spacing: 0.15em; text-transform: uppercase; cursor: pointer; transition: background 0.2s; margin-top: 8px; }
    .apply-filters-btn:hover { background: var(--accent); }

    /* ─── MAIN ─── */
    .mp-main { padding: 40px 48px; }
    .mp-toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 36px; flex-wrap: wrap; gap: 16px; }
    .mp-results-info { font-family: var(--font-mono); font-size: 0.68rem; letter-spacing: 0.12em; text-transform: uppercase; color: var(--grey); }
    .mp-results-info strong { color: var(--black); }
    .sort-select { padding: 8px 14px; border: 1.5px solid var(--card-border); border-radius: 6px; font-family: var(--font-mono); font-size: 0.68rem; outline: none; color: var(--black); cursor: pointer; background: white; }

    /* ─── PRODUCT GRID ─── */
    .listings-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 24px; }
    .listing-card { background: var(--white); border: 1.5px solid var(--card-border); border-radius: 12px; overflow: hidden; transition: all 0.28s; cursor: pointer; position: relative; }
    .listing-card:hover { border-color: var(--black); transform: translateY(-6px); box-shadow: 0 20px 48px rgba(0,0,0,0.1); }
    .listing-badge { position: absolute; top: 14px; left: 14px; font-family: var(--font-mono); font-size: 0.58rem; letter-spacing: 0.12em; text-transform: uppercase; padding: 4px 10px; border-radius: 4px; z-index: 2; }
    .badge-new { background: var(--accent); color: white; }
    .badge-hot { background: var(--black); color: white; }
    .badge-sale { background: #f0a500; color: white; }
    .listing-img-wrap { aspect-ratio: 3/4; overflow: hidden; position: relative; }
    .listing-cover { width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; padding: 16px 12px; font-family: var(--font-display); font-size: 0.75rem; font-weight: 700; color: white; text-align: center; line-height: 1.25; transition: transform 0.3s; }
    .listing-card:hover .listing-cover { transform: scale(1.04); }
    .listing-info { padding: 18px 18px 16px; }
    .listing-type { font-family: var(--font-mono); font-size: 0.58rem; letter-spacing: 0.18em; text-transform: uppercase; color: var(--accent); margin-bottom: 5px; }
    .listing-name { font-family: var(--font-display); font-size: 0.88rem; font-weight: 700; margin-bottom: 3px; line-height: 1.3; }
    .listing-author { font-size: 0.78rem; color: var(--grey); margin-bottom: 12px; }
    .listing-bottom { display: flex; align-items: center; justify-content: space-between; }
    .listing-price-wrap {}
    .listing-old-price { font-family: var(--font-mono); font-size: 0.65rem; color: var(--grey); text-decoration: line-through; }
    .listing-price { font-family: var(--font-display); font-size: 1.1rem; font-weight: 700; }
    .add-cart-btn { width: 36px; height: 36px; border-radius: 50%; background: var(--black); color: white; border: none; cursor: pointer; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; transition: all 0.2s; flex-shrink: 0; }
    .add-cart-btn:hover { background: var(--accent); transform: scale(1.12); }

    /* Sell your products section */
    .my-products-section { padding: 60px 80px; background: var(--off-white); border-top: 1.5px solid var(--light-grey); }
    .my-products-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 36px; }
    .my-products-title { font-family: var(--font-display); font-size: 1.4rem; font-weight: 700; }
    .btn-sell { background: var(--accent); color: white; border: none; padding: 12px 24px; border-radius: 6px; font-family: var(--font-mono); font-size: 0.72rem; letter-spacing: 0.12em; text-transform: uppercase; cursor: pointer; transition: all 0.2s; }
    .btn-sell:hover { background: var(--black); transform: translateY(-2px); }
    .my-products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; }
    .my-product-card { background: white; border: 1.5px solid var(--card-border); border-radius: 12px; padding: 20px; text-align: center; }
    .my-product-card .mp-card-price { font-family: var(--font-display); font-size: 1rem; font-weight: 700; }

    /* ─── SELL MODAL ─── */
    .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.7); backdrop-filter: blur(6px); z-index: 2000; display: flex; align-items: center; justify-content: center; padding: 24px; opacity: 0; pointer-events: none; transition: opacity 0.3s; }
    .modal-overlay.open { opacity: 1; pointer-events: all; }
    .sell-modal { background: var(--white); border-radius: 16px; width: 100%; max-width: 560px; max-height: 90vh; overflow-y: auto; transform: translateY(24px) scale(0.97); transition: transform 0.3s; }
    .modal-overlay.open .sell-modal { transform: none; }
    .modal-header { padding: 32px 36px 24px; border-bottom: 1.5px solid var(--light-grey); display: flex; align-items: center; justify-content: space-between; }
    .modal-eyebrow { font-family: var(--font-mono); font-size: 0.62rem; letter-spacing: 0.2em; text-transform: uppercase; color: var(--accent); margin-bottom: 6px; }
    .modal-title { font-family: var(--font-display); font-size: 1.4rem; font-weight: 900; }
    .modal-close { background: none; border: 1.5px solid var(--card-border); border-radius: 6px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1rem; color: var(--grey); transition: all 0.18s; }
    .modal-close:hover { border-color: var(--black); background: var(--black); color: white; }
    .modal-body { padding: 28px 36px 36px; }
    .form-group { margin-bottom: 20px; }
    .form-label { display: block; font-family: var(--font-mono); font-size: 0.62rem; letter-spacing: 0.15em; text-transform: uppercase; color: var(--grey); margin-bottom: 8px; }
    .form-input, .form-textarea, .form-select { width: 100%; padding: 12px 16px; border: 1.5px solid var(--card-border); border-radius: 8px; font-family: var(--font-body); font-size: 0.9rem; color: var(--black); outline: none; transition: border-color 0.2s; background: var(--white); }
    .form-input:focus, .form-textarea:focus, .form-select:focus { border-color: var(--black); }
    .form-textarea { min-height: 100px; resize: vertical; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .modal-submit { width: 100%; background: var(--black); color: white; padding: 14px; border: none; border-radius: 8px; font-family: var(--font-mono); font-size: 0.75rem; letter-spacing: 0.15em; text-transform: uppercase; cursor: pointer; transition: all 0.2s; margin-top: 8px; }
    .modal-submit:hover { background: var(--accent); box-shadow: 0 8px 24px var(--glow); }

    /* ─── FLOATING CART ─── */
    .floating-cart { position: fixed; bottom: 32px; right: 32px; z-index: 500; }
    .floating-cart-btn { background: var(--accent); color: white; border: none; border-radius: 50%; width: 58px; height: 58px; font-size: 1.5rem; cursor: pointer; box-shadow: 0 8px 32px rgba(232,0,45,0.4); transition: all 0.25s; display: flex; align-items: center; justify-content: center; text-decoration: none; position: relative; }
    .floating-cart-btn:hover { transform: scale(1.1); }
    .floating-count { position: absolute; top: -4px; right: -4px; background: var(--black); color: white; border-radius: 50%; width: 20px; height: 20px; font-size: 0.6rem; font-family: var(--font-mono); font-weight: 700; display: flex; align-items: center; justify-content: center; border: 2px solid white; }

    .reveal { opacity: 0; transform: translateY(24px); transition: opacity 0.65s, transform 0.65s; }
    .reveal.visible { opacity: 1; transform: none; }

    /* ─── FOOTER (via footer.php) ─── */

    @media (max-width: 1100px) {
      .mp-layout { grid-template-columns: 1fr; }
      .mp-sidebar { position: static; max-height: none; border-right: none; border-bottom: 1.5px solid var(--light-grey); }
    }
    @media (max-width: 900px) {
      nav { padding: 0 24px; }
      .mp-hero { padding: 60px 24px; }
      .mp-hero-inner { grid-template-columns: 1fr; }
      .seller-banner { padding: 16px 24px; flex-wrap: wrap; }
      .mp-main { padding: 24px; }
      .my-products-section { padding: 40px 24px; }
      footer { padding: 32px 24px; flex-direction: column; gap: 12px; }
    }

    /* ─── PRODUCT DRAWER ─── */
    .drawer-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.55); backdrop-filter: blur(4px); z-index: 3000; opacity: 0; pointer-events: none; transition: opacity 0.3s; }
    .drawer-overlay.open { opacity: 1; pointer-events: all; }
    .product-drawer { position: fixed; top: 0; right: 0; bottom: 0; width: 460px; max-width: 100vw; background: var(--white); z-index: 3001; transform: translateX(100%); transition: transform 0.35s cubic-bezier(0.4,0,0.2,1); overflow-y: auto; display: flex; flex-direction: column; box-shadow: -8px 0 40px rgba(0,0,0,0.18); }
    .drawer-overlay.open .product-drawer { transform: translateX(0); }
    .drawer-cover { width: 100%; aspect-ratio: 5/3; display: flex; align-items: flex-end; padding: 28px; position: relative; flex-shrink: 0; }
    .drawer-close { position: absolute; top: 16px; right: 16px; background: rgba(0,0,0,0.45); border: none; border-radius: 50%; width: 38px; height: 38px; color: white; font-size: 1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.18s; }
    .drawer-close:hover { background: var(--accent); }
    .drawer-body { padding: 28px 32px 40px; flex: 1; display: flex; flex-direction: column; }
    .drawer-type { font-family: var(--font-mono); font-size: 0.6rem; letter-spacing: 0.2em; text-transform: uppercase; color: var(--accent); margin-bottom: 6px; }
    .drawer-title { font-family: var(--font-display); font-size: 1.4rem; font-weight: 900; line-height: 1.18; margin-bottom: 4px; }
    .drawer-author { font-size: 0.88rem; color: var(--grey); margin-bottom: 18px; }
    .drawer-sep { border: none; border-top: 1.5px solid var(--light-grey); margin: 2px 0 16px; }
    .drawer-desc { font-size: 0.88rem; line-height: 1.75; color: var(--grey); margin-bottom: 20px; }
    .drawer-meta { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; }
    .drawer-meta-item { font-family: var(--font-mono); font-size: 0.6rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--grey); background: var(--off-white); padding: 5px 11px; border-radius: 4px; }
    .drawer-price-row { display: flex; align-items: baseline; gap: 12px; margin-bottom: 24px; flex-wrap: wrap; }
    .drawer-old-price { font-family: var(--font-mono); font-size: 0.95rem; color: var(--grey); text-decoration: line-through; }
    .drawer-price { font-family: var(--font-display); font-size: 2.1rem; font-weight: 900; }
    .drawer-stock { font-family: var(--font-mono); font-size: 0.6rem; letter-spacing: 0.12em; text-transform: uppercase; padding: 4px 10px; border-radius: 20px; margin-left: auto; align-self: center; }
    .stock-ok  { background: #e8faf0; color: #1a7a45; }
    .stock-low { background: #fff4e0; color: #c07a00; }
    .stock-out { background: #fdecea; color: #c0392b; }
    .drawer-add-btn { background: var(--black); color: white; border: none; padding: 16px; border-radius: 10px; font-family: var(--font-mono); font-size: 0.78rem; letter-spacing: 0.16em; text-transform: uppercase; cursor: pointer; transition: all 0.22s; width: 100%; margin-top: auto; }
    .drawer-add-btn:hover:not([disabled]) { background: var(--accent); box-shadow: 0 8px 24px var(--glow); transform: translateY(-2px); }
    .drawer-add-btn[disabled] { opacity: 0.4; cursor: not-allowed; }
    @media (max-width: 520px) { .product-drawer { width: 100vw; } }
  </style>
</head>
<body>

  <!-- ═══ NAVBAR ═══ -->
  <?php require_once 'assets/includes/navbar.php'; ?>

  <div class="page-wrap">

    <!-- ═══ HERO ═══ -->
    <div class="mp-hero">
      <div class="mp-hero-grid"></div>
      <div class="mp-hero-inner">
        <div>
          <div class="mp-eyebrow">Marketplace · P2P · 2026</div>
          <h1 class="mp-title">Compra e vende<br><em>entre fãs.</em></h1>
          <p class="mp-desc">O mercado peer-to-peer do MangaVerse. Encontra raridades, edições esgotadas e coleções únicas.</p>
        </div>
        <div class="mp-hero-stats">
          <div class="mp-stat"><span class="mp-stat-num" id="stat-produtos">0</span><span class="mp-stat-label">Produtos</span></div>
          <div class="mp-stat"><span class="mp-stat-num">4.9★</span><span class="mp-stat-label">Avaliação</span></div>
        </div>
      </div>
    </div>

    <!-- Sell banner -->
    <div class="seller-banner">
      <span class="seller-banner-text">Tens mangás para vender? Publica o teu anúncio no marketplace.</span>
      <button class="seller-banner-cta" id="open-sell-modal">Vender agora →</button>
    </div>

    <!-- ═══ MAIN LAYOUT ═══ -->
    <div class="mp-layout">

      <!-- Sidebar -->
      <aside class="mp-sidebar">
        <div class="sidebar-section">
          <div class="sidebar-section-title">Pesquisa</div>
          <div class="sidebar-search">
            <input type="text" placeholder="Título, autor..." id="search-input">
          </div>
        </div>
        <div class="sidebar-section">
          <div class="sidebar-section-title">Categoria</div>
          <div class="cat-list" id="cat-list"></div>
        </div>
        <div class="sidebar-section">
          <div class="sidebar-section-title">Preço</div>
          <div class="price-inputs">
            <input type="number" class="price-input" placeholder="0€" id="price-min">
            <span class="price-sep">—</span>
            <input type="number" class="price-input" placeholder="200€" id="price-max">
          </div>
        </div>
        <button class="apply-filters-btn" id="apply-filters">Aplicar Filtros</button>
      </aside>

      <!-- Main content -->
      <div class="mp-main">
        <div class="mp-toolbar">
          <div class="mp-results-info"><strong id="results-num">0</strong> produtos encontrados</div>
          <select class="sort-select" id="sort-select">
            <option value="recente">Mais recentes</option>
            <option value="preco_asc">Preço: ↑</option>
            <option value="preco_desc">Preço: ↓</option>
            <option value="nome">Nome A-Z</option>
          </select>
        </div>
        <div class="listings-grid" id="listings-grid">
          <!-- Rendered by jQuery -->
        </div>
      </div>
    </div>

    <!-- ═══ MY PRODUCTS (se logado) ═══ -->
    <?php if ($user): ?>
    <section class="my-products-section">
      <div class="my-products-header">
        <div class="my-products-title">Os teus produtos no Marketplace</div>
        <button class="btn-sell" id="open-sell-modal-2">+ Novo Produto</button>
      </div>
      <div class="my-products-grid" id="my-products-grid">
        <p style="color:var(--grey); font-family:var(--font-mono); font-size:0.72rem;">Ainda não publicaste nenhum produto.</p>
      </div>
    </section>
    <?php endif; ?>

  </div>

  <!-- ═══ FLOATING CART ═══ -->
  <div class="floating-cart">
    <a href="carrinho.php" class="floating-cart-btn">🛒<span class="floating-count" id="float-cart-count">0</span></a>
  </div>

  <!-- ═══ SELL MODAL ═══ -->
  <div class="modal-overlay" id="sell-modal-overlay">
    <div class="sell-modal">
      <div class="modal-header">
        <div>
          <div class="modal-eyebrow">// Novo anúncio</div>
          <div class="modal-title">Vender no Marketplace</div>
        </div>
        <button class="modal-close" id="close-modal">✕</button>
      </div>
      <div class="modal-body">
        <form id="sell-form">
          <div class="form-group">
            <label class="form-label">Título / Nome do produto</label>
            <input type="text" class="form-input" id="sell-title" placeholder="Ex: Berserk Vol. 1-10" required>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Categoria</label>
              <select class="form-select" id="sell-cat" required>
                <option value="">Selecionar...</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Condição</label>
              <select class="form-select" id="sell-condition" required>
                <option value="novo">Novo</option>
                <option value="usado">Usado</option>
                <option value="raro">Raro</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Autor</label>
            <input type="text" class="form-input" id="sell-author" placeholder="Autor do mangá/livro">
          </div>
          <div class="form-group">
            <label class="form-label">Descrição</label>
            <textarea class="form-textarea" id="sell-desc" placeholder="Descreve o produto..."></textarea>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Preço (€)</label>
              <input type="number" class="form-input" id="sell-price" placeholder="0.00" step="0.01" min="0.01" required>
            </div>
            <div class="form-group">
              <label class="form-label">Stock</label>
              <input type="number" class="form-input" id="sell-stock" value="1" min="1">
            </div>
          </div>
          <button type="submit" class="modal-submit" id="sell-submit">Publicar Anúncio →</button>
        </form>
      </div>
    </div>
  </div>

  <?php require_once 'assets/includes/footer.php'; ?>

  <script>
  $(document).ready(function() {
    var currentFilters = { categoria: '', pesquisa: '', preco_min: '', preco_max: '', ordenar: 'recente' };

    // ── Load categories ──
    $.get('assets/controller/controllerMangas.php', { acao: 'categorias' }, function(res) {
      if (!res.success) return;
      var html = '<div class="cat-item active" data-cat=""><span class="cat-name">Todos</span><span class="cat-count">' + (res.contagem.reduce(function(s,c){return s+parseInt(c.total)},0)) + '</span></div>';
      res.contagem.forEach(function(c) {
        html += '<div class="cat-item" data-cat="' + c.slug + '"><span class="cat-name">' + c.nome + '</span><span class="cat-count">' + c.total + '</span></div>';
      });
      $('#cat-list').html(html);

      // Populate sell modal categories
      res.categorias.forEach(function(c) {
        $('#sell-cat').append('<option value="' + c.id + '">' + c.nome + '</option>');
      });
    }, 'json');

    // ── Load products ──
    function loadProducts() {
      $.get('assets/controller/controllerMangas.php', $.extend({ acao: 'listar' }, currentFilters), function(res) {
        if (!res.success) return;
        $('#results-num').text(res.total);
        $('#stat-produtos').text(res.total);
        var grid = $('#listings-grid');
        grid.empty();

        if (res.produtos.length === 0) {
          grid.html('<p style="color:var(--grey);font-family:var(--font-mono);font-size:0.8rem;grid-column:1/-1;text-align:center;padding:60px 0;">Nenhum produto encontrado.</p>');
          return;
        }

        res.produtos.forEach(function(p, idx) {
          var badgeHtml = '';
          if (p.badge) {
            var badgeClass = p.badge === 'new' ? 'badge-new' : p.badge === 'hot' ? 'badge-hot' : 'badge-sale';
            var badgeLabel = p.badge === 'new' ? 'Novo' : p.badge === 'hot' ? '🔥 Hot' : 'Sale';
            badgeHtml = '<span class="listing-badge ' + badgeClass + '">' + badgeLabel + '</span>';
          }
          var typeLabel = p.categoria_slug === 'manga' ? '// Mangá' : p.categoria_slug === 'livro' ? '// Livro' : '// ' + p.categoria_nome;
          var oldPriceHtml = p.preco_antigo ? '<div class="listing-old-price">' + parseFloat(p.preco_antigo).toFixed(2) + '€</div>' : '';

          var card = $('<div class="listing-card reveal" style="transition-delay:' + (idx * 50) + 'ms">' +
            badgeHtml +
            '<div class="listing-img-wrap"><div class="listing-cover" style="background:linear-gradient(160deg,' + (p.cor1||'#0a0a0a') + ',' + (p.cor2||'#e8002d') + ')">' + $('<span>').text(p.nome).html() + '<br><span style="font-size:0.5rem;opacity:0.7">' + $('<span>').text(p.volume||'').html() + '</span></div></div>' +
            '<div class="listing-info">' +
              '<div class="listing-type">' + typeLabel + '</div>' +
              '<div class="listing-name">' + $('<span>').text(p.nome).html() + '</div>' +
              '<div class="listing-author">' + $('<span>').text(p.autor).html() + '</div>' +
              '<div class="listing-bottom">' +
                '<div class="listing-price-wrap">' + oldPriceHtml + '<div class="listing-price">' + parseFloat(p.preco).toFixed(2) + '€</div></div>' +
                '<button class="add-cart-btn" data-id="' + p.id + '" title="Adicionar ao carrinho">+</button>' +
              '</div>' +
            '</div>' +
          '</div>');

          grid.append(card);
          setTimeout(function() { card.addClass('visible'); }, 60 + idx * 50);
        });
      }, 'json');
    }

    loadProducts();

    // ── Filters ──
    $(document).on('click', '.cat-item', function() {
      $('.cat-item').removeClass('active');
      $(this).addClass('active');
      currentFilters.categoria = $(this).data('cat');
      loadProducts();
    });

    $('#apply-filters').on('click', function() {
      currentFilters.pesquisa = $('#search-input').val().trim();
      currentFilters.preco_min = $('#price-min').val();
      currentFilters.preco_max = $('#price-max').val();
      loadProducts();
    });

    $('#search-input').on('input', function() {
      currentFilters.pesquisa = $(this).val().trim();
      loadProducts();
    });

    $('#sort-select').on('change', function() {
      currentFilters.ordenar = $(this).val();
      loadProducts();
    });

    // ── Add to Cart ──
    $(document).on('click', '.add-cart-btn', function(e) {
      e.stopPropagation();
      var btn = $(this);
      var produtoId = btn.data('id');

      $.ajax({
        url: 'assets/controller/controllerCarrinho.php',
        method: 'POST',
        data: { acao: 'adicionar', produto_id: produtoId },
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            updateCartCount(res.total_itens);
            Swal.fire({ toast: true, position: 'bottom-end', icon: 'success', title: '<span style="font-family:Orbitron;font-size:0.78rem">Adicionado!</span>', text: 'Produto adicionado ao carrinho.', showConfirmButton: false, timer: 2200, timerProgressBar: true, background: '#0a0a0a', color: '#fff', iconColor: '#e8002d' });
            btn.css('background', '#e8002d').text('✓');
            setTimeout(function() { btn.css('background', '').text('+'); }, 1200);
          } else if (res.redirect) {
            Swal.fire({ icon: 'info', title: 'Login necessário', text: 'Precisas de fazer login para adicionar ao carrinho.', confirmButtonColor: '#0a0a0a', confirmButtonText: 'Ir para Login' }).then(function() { window.location.href = res.redirect; });
          } else {
            Swal.fire({ icon: 'error', title: 'Erro', text: res.message, confirmButtonColor: '#e8002d' });
          }
        },
        error: function(xhr) {
          var data = xhr.responseJSON || {};
          if (data.redirect) {
            Swal.fire({ icon: 'info', title: 'Login necessário', text: 'Precisas de fazer login para adicionar ao carrinho.', confirmButtonColor: '#0a0a0a', confirmButtonText: 'Ir para Login' }).then(function() { window.location.href = data.redirect; });
          } else {
            Swal.fire({ icon: 'error', title: 'Erro', text: data.message || 'Não foi possível adicionar ao carrinho.', confirmButtonColor: '#e8002d' });
          }
        }
      });
    });

    // ── Sell Modal ──
    function openModal() {
      <?php if (!$user): ?>
        Swal.fire({ icon: 'info', title: 'Login necessário', text: 'Precisas de fazer login para vender produtos.', confirmButtonColor: '#0a0a0a', confirmButtonText: 'Ir para Login' }).then(function() { window.location.href = 'login.php'; });
        return;
      <?php endif; ?>
      $('#sell-modal-overlay').addClass('open');
    }

    $('#open-sell-modal, #open-sell-modal-2').on('click', openModal);
    $('#close-modal').on('click', function() { $('#sell-modal-overlay').removeClass('open'); });
    $('#sell-modal-overlay').on('click', function(e) { if (e.target === this) $(this).removeClass('open'); });

    $('#sell-form').on('submit', function(e) {
      e.preventDefault();
      $('#sell-submit').prop('disabled', true).text('A publicar...');

      $.ajax({
        url: 'assets/controller/controllerMangas.php',
        method: 'POST',
        data: {
          acao: 'criar',
          nome: $('#sell-title').val().trim(),
          autor: $('#sell-author').val().trim(),
          descricao: $('#sell-desc').val().trim(),
          categoria_id: $('#sell-cat').val(),
          preco: $('#sell-price').val(),
          stock: $('#sell-stock').val(),
          condicao: $('#sell-condition').val()
        },
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            Swal.fire({ icon: 'success', title: 'Publicado!', text: 'O teu produto já está no marketplace.', confirmButtonColor: '#0a0a0a' });
            $('#sell-modal-overlay').removeClass('open');
            $('#sell-form')[0].reset();
            loadProducts();
          } else {
            Swal.fire({ icon: 'error', title: 'Erro', text: res.message, confirmButtonColor: '#e8002d' });
          }
          $('#sell-submit').prop('disabled', false).text('Publicar Anúncio →');
        }
      });
    });

    // ── Cart count (barra flutuante) ──
    function updateCartCount(count) {
      $('#nav-cart-count, #float-cart-count').text(count);
    }

    // ── Scroll reveal ──
    var obs = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) { if (entry.isIntersecting) entry.target.classList.add('visible'); });
    }, { threshold: 0.1 });
    document.querySelectorAll('.reveal').forEach(function(el) { obs.observe(el); });

    // ── Product Drawer ──
    var _drawerId = null;

    function openDrawer(id) {
      _drawerId = id;
      $.getJSON('produto.php', { id: id }, function(res) {
        if (!res.success) return;
        var p = res.produto;

        $('#drawer-cover').css('background', 'linear-gradient(160deg,' + p.cor1 + ',' + p.cor2 + ')');

        var badgeLabel = { new: 'Novo', hot: '🔥 Hot', sale: 'Sale' }[p.badge] || '';
        var badgeClass = { new: 'badge-new', hot: 'badge-hot', sale: 'badge-sale' }[p.badge] || '';
        if (p.badge) {
          $('#drawer-badge').attr('class', 'drawer-badge listing-badge ' + badgeClass).text(badgeLabel).show();
        } else {
          $('#drawer-badge').hide();
        }

        var typeLabel = p.categoria_slug === 'manga' ? '// Mangá' : p.categoria_slug === 'livro' ? '// Livro' : '// ' + p.categoria_nome;
        $('#drawer-type').text(typeLabel);
        $('#drawer-title').text(p.nome + (p.volume ? ' — ' + p.volume : ''));
        $('#drawer-author').text('por ' + p.autor);
        $('#drawer-desc').text(p.descricao || 'Sem descrição disponível.');

        var meta = '';
        var condLabel = p.condicao === 'novo' ? 'Novo' : 'Usado (' + p.condicao_pct + '%)';
        meta += '<span class="drawer-meta-item">✅ ' + condLabel + '</span>';
        if (p.vendedor_nome) meta += '<span class="drawer-meta-item">👤 ' + $('<span>').text(p.vendedor_nome).html() + '</span>';
        $('#drawer-meta').html(meta);

        if (p.preco_antigo) {
          $('#drawer-old-price').text(parseFloat(p.preco_antigo).toFixed(2) + '€').show();
        } else {
          $('#drawer-old-price').hide();
        }
        $('#drawer-price').text(parseFloat(p.preco).toFixed(2) + '€');

        var stockClass = p.stock > 5 ? 'stock-ok' : p.stock > 0 ? 'stock-low' : 'stock-out';
        var stockLabel = p.stock > 5 ? 'Em Stock' : p.stock > 0 ? p.stock + ' restantes' : 'Esgotado';
        $('#drawer-stock').attr('class', 'drawer-stock ' + stockClass).text(stockLabel);
        $('#drawer-add-btn').prop('disabled', p.stock <= 0).text(p.stock > 0 ? '🛒 Adicionar ao Carrinho' : 'Esgotado');

        $('#drawer-overlay').addClass('open');
      });
    }

    $('#drawer-overlay').on('click', function(e) { if (e.target === this) $(this).removeClass('open'); });
    $('#drawer-close').on('click', function() { $('#drawer-overlay').removeClass('open'); });
    $(document).on('keydown', function(e) { if (e.key === 'Escape') $('#drawer-overlay').removeClass('open'); });

    $(document).on('click', '.listing-card', function(e) {
      if ($(e.target).closest('.add-cart-btn').length) return;
      var id = $(this).find('.add-cart-btn').data('id');
      if (id) openDrawer(id);
    });

    $('#drawer-add-btn').on('click', function() {
      if (!_drawerId) return;
      var btn = $(this);
      $.ajax({
        url: 'assets/controller/controllerCarrinho.php',
        method: 'POST',
        data: { acao: 'adicionar', produto_id: _drawerId },
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            updateCartCount(res.total_itens);
            btn.text('✓ Adicionado!');
            setTimeout(function() { btn.text('🛒 Adicionar ao Carrinho'); }, 1800);
            Swal.fire({ toast: true, position: 'bottom-end', icon: 'success', title: '<span style="font-family:Orbitron;font-size:0.78rem">Adicionado!</span>', showConfirmButton: false, timer: 2200, timerProgressBar: true, background: '#0a0a0a', color: '#fff', iconColor: '#e8002d' });
          } else if (res.redirect) {
            $('#drawer-overlay').removeClass('open');
            Swal.fire({ icon: 'info', title: 'Login necessário', text: 'Precisas de fazer login para adicionar ao carrinho.', confirmButtonColor: '#0a0a0a', confirmButtonText: 'Ir para Login' }).then(function() { window.location.href = res.redirect; });
          } else {
            Swal.fire({ icon: 'error', title: 'Erro', text: res.message, confirmButtonColor: '#e8002d' });
          }
        },
        error: function(xhr) {
          var data = xhr.responseJSON || {};
          if (data.redirect) {
            $('#drawer-overlay').removeClass('open');
            Swal.fire({ icon: 'info', title: 'Login necessário', confirmButtonColor: '#0a0a0a', confirmButtonText: 'Ir para Login' }).then(function() { window.location.href = data.redirect; });
          } else {
            Swal.fire({ icon: 'error', title: 'Erro', text: data.message || 'Erro ao adicionar ao carrinho.', confirmButtonColor: '#e8002d' });
          }
        }
      });
    });
  });
  </script>

  <!-- ═══ PRODUCT DRAWER ═══ -->
  <div class="drawer-overlay" id="drawer-overlay">
    <div class="product-drawer" id="product-drawer">
      <div class="drawer-cover" id="drawer-cover">
        <button class="drawer-close" id="drawer-close" title="Fechar">✕</button>
        <span class="drawer-badge" id="drawer-badge" style="display:none"></span>
      </div>
      <div class="drawer-body">
        <div class="drawer-type" id="drawer-type"></div>
        <div class="drawer-title" id="drawer-title"></div>
        <div class="drawer-author" id="drawer-author"></div>
        <hr class="drawer-sep">
        <p class="drawer-desc" id="drawer-desc"></p>
        <div class="drawer-meta" id="drawer-meta"></div>
        <div class="drawer-price-row">
          <span class="drawer-old-price" id="drawer-old-price" style="display:none"></span>
          <span class="drawer-price" id="drawer-price"></span>
          <span class="drawer-stock" id="drawer-stock"></span>
        </div>
        <button class="drawer-add-btn" id="drawer-add-btn">🛒 Adicionar ao Carrinho</button>
      </div>
    </div>
  </div>
</body>
</html>
