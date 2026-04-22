<?php
// ════════════════════════════════════════════════════════
//  MangaVerse — Navbar Partilhada com RBAC
//
//  Como usar em cada página:
//    $currentPage = 'marketplace';   // → loja | marketplace | contacto
//                                    //   suporte | carrinho | login | registo | admin
//    $basePath    = '';              // '' = raiz; '../' = pasta admin/
//    require_once 'assets/includes/navbar.php';  (ou caminho equivalente)
// ════════════════════════════════════════════════════════

// Valores default se a página não os definiu
$currentPage = $currentPage ?? '';
$basePath    = $basePath    ?? '';

// Utilizador atual (já carregado pelo require_once config/database.php na página)
$_nav_user = getLoggedUser();
$_nav_role = $_nav_user['role'] ?? '';

// Helper: classe active
function navActive(string $page, string $current): string {
    return $page === $current ? ' class="active"' : '';
}

// Cart count via PHP session (admin não precisa de carrinho)
$_nav_cartCount = 0;
if (isLoggedIn() && $_nav_role !== 'admin') {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT COALESCE(SUM(quantidade),0) FROM carrinho WHERE utilizador_id = ?");
        $stmt->execute([$_nav_user['id']]);
        $_nav_cartCount = (int)$stmt->fetchColumn();
    } catch (Throwable $e) { $_nav_cartCount = 0; }
}
?>
<nav>
    <a href="<?= $basePath ?>index.php" class="nav-logo">
        <div class="logo-dot"></div>
        Manga<span>Verse</span>
    </a>

    <ul class="nav-links">
        <li><a href="<?= $basePath ?>index.php" <?= navActive('loja', $currentPage) ?>>Loja</a></li>
        <li><a href="<?= $basePath ?>marketplace.php" <?= navActive('marketplace', $currentPage) ?>>Marketplace</a></li>
        <li><a href="<?= $basePath ?>contacto.php" <?= navActive('contacto', $currentPage) ?>>Contacto</a></li>
        <li><a href="<?= $basePath ?>suporte.php" <?= navActive('suporte', $currentPage) ?>>Suporte</a></li>

        <?php if ($_nav_user): ?>
        <?php if ($_nav_role === 'admin'): ?>
        <li>
            <a href="<?= $basePath ?>admin/index.php" <?= navActive('admin', $currentPage) ?> class="admin-link">
                ⚙ Painel Admin
            </a>
        </li>
        <?php endif; ?>

        <li class="user-info-nav">
            <span class="user-badge">
                <?php if ($_nav_role === 'admin'): ?>
                🛡 <?= htmlspecialchars($_nav_user['nome']) ?>
                <?php elseif ($_nav_role === 'vendedor'): ?>
                🏪 <?= htmlspecialchars($_nav_user['nome']) ?>
                <?php else: ?>
                👤 <?= htmlspecialchars($_nav_user['nome']) ?>
                <?php endif; ?>
            </span>
            <span class="role-badge role-<?= $_nav_role ?>"><?= $_nav_role ?></span>
        </li>

        <?php if ($_nav_role !== 'admin'): ?>
        <li>
            <a href="<?= $basePath ?>carrinho.php" <?= navActive('carrinho', $currentPage) ?> class="cart-btn">
                🛒 Carrinho
                <span class="cart-count" id="nav-cart-count"><?= $_nav_cartCount ?></span>
            </a>
        </li>
        <?php endif; ?>

        <!-- Dark Mode Toggle -->
        <li>
            <button class="dark-mode-toggle" id="dark-mode-toggle" title="Alternar modo escuro"
                aria-label="Alternar modo escuro">
                <span class="dm-icon" id="dm-icon">🌙</span>
            </button>
        </li>

        <li><a href="#" id="nav-logout-btn" class="nav-logout">Sair</a></li>

        <?php else: ?>
        <li><a href="<?= $basePath ?>login.php" <?= navActive('login', $currentPage) ?>>Login</a></li>
        <li><a href="<?= $basePath ?>registo.php" <?= navActive('registo', $currentPage) ?>
                class="nav-register-btn">Registar</a></li>

        <!-- Dark Mode Toggle -->
        <li>
            <button class="dark-mode-toggle" id="dark-mode-toggle" title="Alternar modo escuro"
                aria-label="Alternar modo escuro">
                <span class="dm-icon" id="dm-icon">🌙</span>
            </button>
        </li>
        <?php endif; ?>
    </ul>
</nav>

<!-- CSS da navbar (injeta uma vez) -->
<?php if (!defined('NAVBAR_STYLES_LOADED')): define('NAVBAR_STYLES_LOADED', true); ?>
<style>
nav {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(16px);
    border-bottom: 1.5px solid #ececec;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 48px;
    height: 72px;
}

.nav-logo {
    font-family: 'Orbitron', sans-serif;
    font-size: 1.35rem;
    font-weight: 900;
    letter-spacing: 0.08em;
    color: #0a0a0a;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 10px;
}

.nav-logo span {
    color: #e8002d;
}

.logo-dot {
    width: 8px;
    height: 8px;
    background: #e8002d;
    border-radius: 50%;
    animation: navDotPulse 1.8s infinite;
}

@keyframes navDotPulse {

    0%,
    100% {
        opacity: 1;
        transform: scale(1)
    }

    50% {
        opacity: 0.4;
        transform: scale(1.5)
    }
}

.nav-links {
    display: flex;
    align-items: center;
    gap: 28px;
    list-style: none;
    flex-wrap: nowrap;
}

.nav-links a {
    font-family: 'Space Mono', monospace;
    font-size: 0.72rem;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: #8a8a8a;
    text-decoration: none;
    transition: color 0.2s;
}

.nav-links a:hover {
    color: #0a0a0a;
}

.nav-links a.active {
    color: #0a0a0a;
    font-weight: 700;
}

/* Admin link */
.admin-link {
    color: #e8002d !important;
    border: 1.5px solid #e8002d;
    padding: 6px 14px;
    border-radius: 4px;
}

.admin-link:hover {
    background: #e8002d;
    color: #fff !important;
}

/* User info block */
.user-info-nav {
    display: flex;
    align-items: center;
    gap: 6px;
}

.user-badge {
    font-family: 'Space Mono', monospace;
    font-size: 0.68rem;
    color: #0a0a0a;
    letter-spacing: 0.08em;
}

.role-badge {
    font-family: 'Space Mono', monospace;
    font-size: 0.55rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    padding: 2px 7px;
    border-radius: 100px;
}

.role-admin {
    background: #ffd6d6;
    color: #e8002d;
}

.role-vendedor {
    background: #d6e8ff;
    color: #0057ff;
}

.role-cliente {
    background: #ececec;
    color: #555;
}

/* Cart button */
.cart-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #0a0a0a;
    color: #fff !important;
    padding: 9px 18px;
    border-radius: 4px;
    font-family: 'Space Mono', monospace !important;
    font-size: 0.7rem !important;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    text-decoration: none;
    transition: background 0.2s, transform 0.15s;
}

.cart-btn:hover {
    background: #e8002d !important;
    transform: translateY(-1px);
}

.cart-count {
    background: #e8002d;
    color: #fff;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    font-size: 0.62rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Space Mono', monospace;
    font-weight: 700;
}

/* Register button */
.nav-register-btn {
    background: #e8002d;
    color: #fff !important;
    padding: 9px 18px;
    border-radius: 4px;
    border: 1.5px solid #e8002d;
}

.nav-register-btn:hover {
    background: #0a0a0a !important;
    border-color: #0a0a0a;
}

/* Logout */
.nav-logout {
    color: #8a8a8a !important;
}

.nav-logout:hover {
    color: #e8002d !important;
}

/* Dark Mode Toggle */
.dark-mode-toggle {
    background: none;
    border: 1.5px solid #e0e0e0;
    border-radius: 50%;
    width: 36px;
    height: 36px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.25s;
    font-size: 1rem;
    padding: 0;
}

.dark-mode-toggle:hover {
    border-color: #0a0a0a;
    transform: scale(1.1);
}

/* ═══ DARK MODE ═══ */
body.dark-mode {
    --white: #0e0e0e;
    --off-white: #181818;
    --black: #f0f0f0;
    --grey: #999;
    --light-grey: #2a2a2a;
    --card-border: #333;
    background: #0e0e0e !important;
    color: #f0f0f0 !important;
}

/* Nav */
body.dark-mode nav {
    background: rgba(14, 14, 14, 0.95) !important;
    border-bottom-color: #2a2a2a !important;
}

body.dark-mode .nav-logo {
    color: #f0f0f0;
}

body.dark-mode .nav-links a {
    color: #999;
}

body.dark-mode .nav-links a:hover,
body.dark-mode .nav-links a.active {
    color: #f0f0f0;
}

body.dark-mode .user-badge {
    color: #f0f0f0;
}

body.dark-mode .dark-mode-toggle {
    border-color: #444;
}

body.dark-mode .dark-mode-toggle:hover {
    border-color: #f0f0f0;
}

body.dark-mode .cart-btn {
    background: #f0f0f0;
    color: #0e0e0e !important;
}

body.dark-mode .cart-btn:hover {
    background: #e8002d !important;
    color: #fff !important;
}

body.dark-mode .admin-link {
    color: #e8002d !important;
    border-color: #e8002d;
}

body.dark-mode .admin-link:hover {
    background: #e8002d;
    color: #fff !important;
}

body.dark-mode .nav-register-btn {
    background: #e8002d;
    color: #fff !important;
}

/* Page wraps & backgrounds */
body.dark-mode .page-wrap,
body.dark-mode .admin-wrap {
    background: #0e0e0e;
}

body.dark-mode footer {
    background: #0a0a0a !important;
}

/* Hero sections — keep dark background (--black is overridden to light, so force dark) */
body.dark-mode .mp-hero,
body.dark-mode .cart-hero,
body.dark-mode .admin-header,
body.dark-mode .sup-hero,
body.dark-mode .contact-hero {
    background: #111 !important;
    color: #fff !important;
}

body.dark-mode .mp-hero *,
body.dark-mode .cart-hero *,
body.dark-mode .admin-header *,
body.dark-mode .sup-hero * {
    color: inherit;
}

body.dark-mode .mp-eyebrow,
body.dark-mode .cart-eyebrow,
body.dark-mode .admin-eyebrow,
body.dark-mode .sup-eyebrow {
    color: #e8002d !important;
}

body.dark-mode .mp-desc,
body.dark-mode .admin-header p {
    color: rgba(255, 255, 255, 0.45) !important;
}

body.dark-mode .mp-stat-label {
    color: rgba(255, 255, 255, 0.35) !important;
}

body.dark-mode .cart-hero-count {
    color: rgba(255, 255, 255, 0.15) !important;
}

body.dark-mode .seller-banner {
    background: #e8002d !important;
}

/* Forms & inputs */
body.dark-mode input,
body.dark-mode textarea,
body.dark-mode select {
    background: #1a1a1a !important;
    color: #f0f0f0 !important;
    border-color: #333 !important;
}

body.dark-mode input:focus,
body.dark-mode textarea:focus,
body.dark-mode select:focus {
    border-color: #e8002d !important;
}

body.dark-mode input::placeholder,
body.dark-mode textarea::placeholder {
    color: #666 !important;
}

body.dark-mode .form-label {
    color: #999 !important;
}

/* Cards — marketplace, panel, stats, admin, checkout, etc. */
body.dark-mode .listing-card,
body.dark-mode .ticket-card,
body.dark-mode .faq-card,
body.dark-mode .seller-card,
body.dark-mode .panel-card,
body.dark-mode .stat-card,
body.dark-mode .contacto-card,
body.dark-mode .ticket-form-card,
body.dark-mode .checkout-modal,
body.dark-mode .sell-modal,
body.dark-mode .my-product-card,
body.dark-mode .chart-card,
body.dark-mode .result-card {
    background: #181818 !important;
    border-color: #333 !important;
    color: #f0f0f0 !important;
}

body.dark-mode .listing-card:hover,
body.dark-mode .ticket-card:hover,
body.dark-mode .seller-card:hover,
body.dark-mode .stat-card:hover,
body.dark-mode .chart-card:hover {
    border-color: #e8002d !important;
    box-shadow: 0 12px 32px rgba(232, 0, 45, 0.15) !important;
}

body.dark-mode .stat-card.accent {
    background: #1a0a0c !important;
    border-color: #e8002d !important;
}

/* Listing card details */
body.dark-mode .listing-name {
    color: #f0f0f0 !important;
}

body.dark-mode .listing-author {
    color: #888 !important;
}

body.dark-mode .listing-type {
    color: #e8002d !important;
}

body.dark-mode .listing-price {
    color: #f0f0f0 !important;
}

body.dark-mode .add-cart-btn {
    background: #f0f0f0 !important;
    color: #0e0e0e !important;
}

body.dark-mode .add-cart-btn:hover {
    background: #e8002d !important;
    color: #fff !important;
}

/* Sidebar & filters */
body.dark-mode .mp-sidebar {
    border-right-color: #2a2a2a !important;
    background: #0e0e0e;
}

body.dark-mode .sidebar-section-title {
    color: #777 !important;
    border-bottom-color: #2a2a2a !important;
}

body.dark-mode .cat-item:hover {
    background: #1a1a1a !important;
}

body.dark-mode .cat-item.active {
    background: #f0f0f0 !important;
    border-color: #f0f0f0 !important;
}

body.dark-mode .cat-item.active .cat-name {
    color: #0e0e0e !important;
}

body.dark-mode .cat-name {
    color: #ccc !important;
}

body.dark-mode .cat-count {
    background: #2a2a2a !important;
    color: #888 !important;
}

body.dark-mode .condition-tag.active {
    background: #f0f0f0 !important;
    color: #0e0e0e !important;
}

body.dark-mode .apply-filters-btn {
    background: #f0f0f0 !important;
    color: #0e0e0e !important;
}

body.dark-mode .apply-filters-btn:hover {
    background: #e8002d !important;
    color: #fff !important;
}

body.dark-mode .mp-results-info {
    color: #888 !important;
}

body.dark-mode .mp-results-info strong {
    color: #f0f0f0 !important;
}

body.dark-mode .sort-select {
    background: #1a1a1a !important;
    color: #f0f0f0 !important;
    border-color: #333 !important;
}

/* Cart page */
body.dark-mode .cart-main {
    border-right-color: #2a2a2a !important;
}

body.dark-mode .cart-item {
    border-bottom-color: #2a2a2a !important;
}

body.dark-mode .ci-name {
    color: #f0f0f0 !important;
}

body.dark-mode .ci-author {
    color: #888 !important;
}

body.dark-mode .ci-price {
    color: #f0f0f0 !important;
}

body.dark-mode .ci-remove {
    color: #888 !important;
}

body.dark-mode .ci-remove:hover {
    color: #e8002d !important;
}

body.dark-mode .qty-btn {
    border-color: #333 !important;
    color: #f0f0f0 !important;
}

body.dark-mode .qty-btn:hover {
    background: #f0f0f0 !important;
    color: #0e0e0e !important;
}

body.dark-mode .qty-val {
    border-color: #333 !important;
    color: #f0f0f0 !important;
}

body.dark-mode .summary-label {
    color: #999 !important;
}

body.dark-mode .summary-val {
    color: #f0f0f0 !important;
}

body.dark-mode .summary-divider {
    background: #333 !important;
}

body.dark-mode .promo-input {
    background: #1a1a1a !important;
    border-color: #333 !important;
    color: #f0f0f0 !important;
}

body.dark-mode .promo-btn {
    background: #f0f0f0 !important;
    color: #0e0e0e !important;
}

body.dark-mode .promo-btn:hover {
    background: #e8002d !important;
    color: #fff !important;
}

body.dark-mode .pm-icon {
    background: #1a1a1a !important;
    color: #888 !important;
}

body.dark-mode .secure-text {
    color: #666 !important;
}

body.dark-mode .cart-empty-text {
    color: #888 !important;
}

body.dark-mode .cart-empty-btn {
    background: #f0f0f0 !important;
    color: #0e0e0e !important;
}

body.dark-mode .cart-empty-btn:hover {
    background: #e8002d !important;
    color: #fff !important;
}

/* Product Drawer */
body.dark-mode .product-drawer {
    background: #141414 !important;
}

body.dark-mode .drawer-title {
    color: #f0f0f0 !important;
}

body.dark-mode .drawer-author,
body.dark-mode .drawer-desc {
    color: #888 !important;
}

body.dark-mode .drawer-sep {
    border-top-color: #2a2a2a !important;
}

body.dark-mode .drawer-meta-item {
    background: #1e1e1e !important;
    color: #aaa !important;
}

body.dark-mode .drawer-price {
    color: #f0f0f0 !important;
}

body.dark-mode .drawer-old-price {
    color: #666 !important;
}

body.dark-mode .drawer-add-btn {
    background: #f0f0f0 !important;
    color: #0e0e0e !important;
}

body.dark-mode .drawer-add-btn:hover:not([disabled]) {
    background: #e8002d !important;
    color: #fff !important;
}

/* Tables (admin) */
body.dark-mode .panel-table th {
    color: #999 !important;
    border-bottom-color: #333 !important;
}

body.dark-mode .panel-table td {
    border-bottom-color: #222 !important;
    color: #ccc !important;
}

body.dark-mode .panel-table tr:hover td {
    background: #1a1a1a !important;
}

body.dark-mode .panel-card-header {
    border-bottom-color: #333 !important;
}

body.dark-mode .td-mono {
    color: #888 !important;
}

/* Charts (admin) */
body.dark-mode .chart-card-header {
    border-bottom-color: #333 !important;
}

body.dark-mode .chart-card-title {
    color: #f0f0f0 !important;
}

/* Modals */
body.dark-mode .modal-overlay {
    background: rgba(0, 0, 0, 0.85);
}

body.dark-mode .modal-header {
    border-bottom-color: #333 !important;
}

body.dark-mode .modal-title {
    color: #f0f0f0 !important;
}

body.dark-mode .modal-eyebrow {
    color: #e8002d !important;
}

body.dark-mode .modal-close {
    border-color: #333 !important;
    color: #999 !important;
}

body.dark-mode .modal-close:hover {
    background: #f0f0f0 !important;
    color: #0e0e0e !important;
    border-color: #f0f0f0 !important;
}

body.dark-mode .modal-submit {
    background: #e8002d;
}

body.dark-mode .step-num {
    border-color: #444 !important;
    color: #999 !important;
}

body.dark-mode .checkout-step.active .step-num {
    background: #f0f0f0 !important;
    color: #0e0e0e !important;
    border-color: #f0f0f0 !important;
}

body.dark-mode .checkout-step.done .step-num {
    background: #22c55e !important;
    color: #fff !important;
}

body.dark-mode .checkout-step {
    color: #666 !important;
}

body.dark-mode .checkout-step.active {
    color: #f0f0f0 !important;
}

body.dark-mode .stripe-element {
    background: #1a1a1a !important;
    border-color: #333 !important;
}

/* Suporte page */
body.dark-mode .message-bubble.msg-user {
    background: #1a1a1a !important;
    border-color: #333 !important;
}

body.dark-mode .sup-tabs {
    border-bottom-color: #333 !important;
}

/* Sections with off-white bg */
body.dark-mode .off-white-section,
body.dark-mode .sellers-section,
body.dark-mode .my-products-section {
    background: #141414 !important;
    border-top-color: #2a2a2a !important;
}

body.dark-mode .my-products-title {
    color: #f0f0f0 !important;
}

body.dark-mode .btn-sell {
    background: #e8002d !important;
}

/* Result pages (sucesso/erro pagamento) */
body.dark-mode .result-page {
    background: #0e0e0e;
}

body.dark-mode .result-card {
    background: #181818 !important;
    border-color: #333 !important;
}

body.dark-mode .result-card h1 {
    color: #f0f0f0 !important;
}

body.dark-mode .result-card p {
    color: #999 !important;
}

body.dark-mode .result-card .btn-outline {
    border-color: #444 !important;
    color: #f0f0f0 !important;
}

body.dark-mode .result-card .btn-outline:hover {
    border-color: #f0f0f0 !important;
}

/* Floating cart */
body.dark-mode .floating-cart-btn {
    box-shadow: 0 8px 32px rgba(232, 0, 45, 0.3) !important;
}

body.dark-mode .floating-count {
    background: #f0f0f0 !important;
    color: #0e0e0e !important;
    border-color: #0e0e0e !important;
}

/* Login & Register — visual panels must stay dark */
body.dark-mode .login-visual,
body.dark-mode .register-visual {
    background: #111 !important;
}

body.dark-mode .login-visual *,
body.dark-mode .register-visual * {
    color: inherit;
}

body.dark-mode .login-visual-content,
body.dark-mode .register-visual-content {
    color: #fff !important;
}

body.dark-mode .login-visual-content p,
body.dark-mode .register-visual-content p {
    color: rgba(255, 255, 255, 0.5) !important;
}

body.dark-mode .benefit-text {
    color: rgba(255, 255, 255, 0.6) !important;
}

body.dark-mode .benefit-icon {
    border-color: rgba(255, 255, 255, 0.15) !important;
}

body.dark-mode .login-form-side,
body.dark-mode .register-form-side {
    background: #0e0e0e;
}

body.dark-mode .form-title {
    color: #f0f0f0 !important;
}

body.dark-mode .form-subtitle {
    color: #888 !important;
}

body.dark-mode .form-eyebrow {
    color: #e8002d !important;
}

body.dark-mode .password-hint {
    color: #777 !important;
}

body.dark-mode .form-footer {
    color: #888 !important;
}

body.dark-mode .btn-primary-full {
    background: #e8002d !important;
    color: #fff !important;
}

body.dark-mode .btn-primary-full:hover {
    background: #cc0028 !important;
}

body.dark-mode .form-divider::before,
body.dark-mode .form-divider::after {
    background: #333 !important;
}

body.dark-mode .form-divider span {
    color: #666 !important;
}

/* Admin compras section */
body.dark-mode .compras-section .panel-card {
    background: #181818 !important;
    border-color: #333 !important;
}

body.dark-mode .livro-badge {
    background: #1a1a1a !important;
    border-color: #444 !important;
    color: #ccc !important;
}

body.dark-mode .btn-toggle-livros {
    background: #f0f0f0 !important;
    color: #0e0e0e !important;
}

body.dark-mode .btn-toggle-livros:hover {
    background: #e8002d !important;
    color: #fff !important;
}

/* Suporte dark mode extras */
body.dark-mode .sup-tab {
    color: #888 !important;
}

body.dark-mode .sup-tab.active {
    color: #f0f0f0 !important;
    border-bottom-color: #e8002d !important;
}

body.dark-mode .ticket-form-card h3 {
    color: #f0f0f0 !important;
}

body.dark-mode .message-bubble.msg-admin {
    background: #1a0a0c !important;
    border-color: #e8002d !important;
}

body.dark-mode .ticket-meta {
    color: #777 !important;
}

@media (max-width: 900px) {
    nav {
        padding: 0 20px;
    }

    .nav-links {
        gap: 16px;
    }

    .nav-links a {
        font-size: 0.62rem;
    }
}
</style>
<?php endif; ?>


<script>
(function($) {
    'use strict';

    // ── Dark Mode ──
    (function initDarkMode() {
        var stored = localStorage.getItem('mv_darkmode');
        if (stored === null || stored === 'true') {
            document.body.classList.add('dark-mode');
        }
        updateDMIcon();

        $(function() {
            $('#dark-mode-toggle').on('click', function() {
                document.body.classList.toggle('dark-mode');
                var isDark = document.body.classList.contains('dark-mode');
                localStorage.setItem('mv_darkmode', isDark);
                updateDMIcon();
            });
        });

        function updateDMIcon() {
            var icon = document.getElementById('dm-icon');
            if (icon) {
                icon.textContent = document.body.classList.contains('dark-mode') ? '☀️' : '🌙';
            }
        }
    })();

    $(function() {
        // ── Logout
        $('#nav-logout-btn').on('click', function(e) {
            e.preventDefault();
            $.get('<?= $basePath ?>assets/controller/controllerAuth.php', {
                acao: 'logout'
            }, function() {
                window.location.href = '<?= $basePath ?>login.php';
            }, 'json').fail(function() {
                window.location.href = '<?= $basePath ?>login.php';
            });
        });

        <?php if ($_nav_user && $_nav_role !== 'admin'): ?>
        // ── Actualizar contador do carrinho via AJAX
        $.get('<?= $basePath ?>assets/controller/controllerCarrinho.php', {
            acao: 'contar'
        }, function(res) {
            if (res && res.success) {
                $('#nav-cart-count').text(res.total_itens);
            }
        }, 'json');
        <?php endif; ?>
    });
})(jQuery);
</script>