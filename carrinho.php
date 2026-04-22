<?php
require_once 'assets/config/database.php';
initSession();
$user = getLoggedUser();
$currentPage = 'carrinho';
$basePath    = '';
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Carrinho — MangaVerse</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Noto+Sans+JP:wght@300;400;700&family=Space+Mono:wght@400;700&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
    :root {
        --white: #ffffff;
        --off-white: #f7f7f5;
        --black: #0a0a0a;
        --accent: #e8002d;
        --accent2: #0057ff;
        --grey: #8a8a8a;
        --light-grey: #ececec;
        --card-border: #e0e0e0;
        --glow: rgba(232, 0, 45, 0.18);
        --font-display: 'Orbitron', sans-serif;
        --font-body: 'Noto Sans JP', sans-serif;
        --font-mono: 'Space Mono', monospace;
    }

    *,
    *::before,
    *::after {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html {
        scroll-behavior: smooth;
    }

    body {
        font-family: var(--font-body);
        background: var(--white);
        color: var(--black);
        overflow-x: hidden;
    }

    /* ─── NAVBAR ─── */
    nav {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(16px);
        border-bottom: 1.5px solid var(--light-grey);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 48px;
        height: 72px;
    }

    /* navbar via assets/includes/navbar.php */

    .page-wrap {
        padding-top: 72px;
    }

    /* ─── HERO ─── */
    .cart-hero {
        background: var(--black);
        color: white;
        padding: 56px 80px 48px;
        position: relative;
        overflow: hidden;
    }

    .cart-hero::before {
        content: 'カート';
        position: absolute;
        right: 60px;
        top: 50%;
        transform: translateY(-50%);
        font-family: var(--font-display);
        font-size: 14rem;
        font-weight: 900;
        color: rgba(255, 255, 255, 0.03);
        pointer-events: none;
    }

    .cart-hero-grid {
        position: absolute;
        inset: 0;
        background-image: linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        background-size: 60px 60px;
        pointer-events: none;
    }

    .cart-hero-inner {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .cart-eyebrow {
        font-family: var(--font-mono);
        font-size: 0.68rem;
        letter-spacing: 0.25em;
        text-transform: uppercase;
        color: var(--accent);
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .cart-eyebrow::before {
        content: '';
        width: 32px;
        height: 1.5px;
        background: var(--accent);
    }

    .cart-title {
        font-family: var(--font-display);
        font-size: clamp(1.8rem, 3.5vw, 2.8rem);
        font-weight: 900;
        line-height: 1.1;
    }

    .cart-title em {
        font-style: normal;
        color: var(--accent);
    }

    .cart-hero-count {
        font-family: var(--font-display);
        font-size: 3rem;
        font-weight: 900;
        color: rgba(255, 255, 255, 0.15);
    }

    /* ─── LAYOUT ─── */
    .cart-layout {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 0;
        min-height: 60vh;
    }

    .cart-main {
        padding: 48px;
        border-right: 1.5px solid var(--light-grey);
    }

    .cart-sidebar {
        padding: 48px 36px;
        position: sticky;
        top: 72px;
        max-height: calc(100vh - 72px);
        overflow-y: auto;
    }

    /* ─── CART ITEMS ─── */
    .cart-items-list {
        display: flex;
        flex-direction: column;
        gap: 0;
    }

    .cart-item {
        display: grid;
        grid-template-columns: 80px 1fr auto auto;
        gap: 20px;
        align-items: center;
        padding: 24px 0;
        border-bottom: 1px solid var(--light-grey);
    }

    .cart-item:first-child {
        padding-top: 0;
    }

    .ci-cover {
        width: 80px;
        height: 108px;
        border-radius: 8px;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        padding: 8px 4px;
        font-family: var(--font-display);
        font-size: 0.5rem;
        font-weight: 700;
        color: white;
        text-align: center;
        line-height: 1.2;
    }

    .ci-info {}

    .ci-type {
        font-family: var(--font-mono);
        font-size: 0.55rem;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: var(--accent);
        margin-bottom: 4px;
    }

    .ci-name {
        font-family: var(--font-display);
        font-size: 0.85rem;
        font-weight: 700;
        margin-bottom: 2px;
    }

    .ci-author {
        font-size: 0.75rem;
        color: var(--grey);
    }

    .ci-qty {
        display: flex;
        align-items: center;
        gap: 0;
    }

    .qty-btn {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1.5px solid var(--card-border);
        background: none;
        cursor: pointer;
        font-family: var(--font-mono);
        font-size: 0.8rem;
        color: var(--black);
        transition: all 0.15s;
    }

    .qty-btn:first-child {
        border-radius: 6px 0 0 6px;
    }

    .qty-btn:last-child {
        border-radius: 0 6px 6px 0;
    }

    .qty-btn:hover {
        background: var(--black);
        color: white;
        border-color: var(--black);
    }

    .qty-val {
        width: 36px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-top: 1.5px solid var(--card-border);
        border-bottom: 1.5px solid var(--card-border);
        font-family: var(--font-mono);
        font-size: 0.75rem;
    }

    .ci-price-col {
        text-align: right;
        min-width: 80px;
    }

    .ci-price {
        font-family: var(--font-display);
        font-size: 1rem;
        font-weight: 700;
    }

    .ci-remove {
        font-family: var(--font-mono);
        font-size: 0.58rem;
        color: var(--grey);
        cursor: pointer;
        margin-top: 4px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        transition: color 0.15s;
    }

    .ci-remove:hover {
        color: var(--accent);
    }

    .cart-empty {
        text-align: center;
        padding: 80px 24px;
    }

    .cart-empty-icon {
        font-size: 3.5rem;
        margin-bottom: 16px;
    }

    .cart-empty-text {
        font-family: var(--font-mono);
        font-size: 0.8rem;
        letter-spacing: 0.1em;
        color: var(--grey);
        margin-bottom: 24px;
    }

    .cart-empty-btn {
        display: inline-block;
        background: var(--black);
        color: white;
        padding: 12px 28px;
        border-radius: 6px;
        font-family: var(--font-mono);
        font-size: 0.72rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        text-decoration: none;
        transition: all 0.2s;
    }

    .cart-empty-btn:hover {
        background: var(--accent);
        color: white;
    }

    /* ─── SIDEBAR ─── */
    .sidebar-title {
        font-family: var(--font-display);
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 28px;
    }

    .summary-rows {
        display: flex;
        flex-direction: column;
        gap: 14px;
        margin-bottom: 24px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .summary-label {
        font-family: var(--font-mono);
        font-size: 0.68rem;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--grey);
    }

    .summary-val {
        font-family: var(--font-mono);
        font-size: 0.8rem;
        font-weight: 700;
    }

    .summary-divider {
        height: 1.5px;
        background: var(--light-grey);
        margin: 8px 0;
    }

    .summary-total .summary-label {
        color: var(--black);
        font-size: 0.72rem;
    }

    .summary-total .summary-val {
        font-family: var(--font-display);
        font-size: 1.3rem;
        color: var(--accent);
    }

    .promo-row {
        display: flex;
        gap: 8px;
        margin-bottom: 28px;
    }

    .promo-input {
        flex: 1;
        padding: 10px 14px;
        border: 1.5px solid var(--card-border);
        border-radius: 6px;
        font-family: var(--font-mono);
        font-size: 0.7rem;
        outline: none;
        color: var(--black);
        transition: border-color 0.2s;
    }

    .promo-input:focus {
        border-color: var(--black);
    }

    .promo-btn {
        background: var(--black);
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 6px;
        font-family: var(--font-mono);
        font-size: 0.65rem;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        cursor: pointer;
        transition: background 0.2s;
        white-space: nowrap;
    }

    .promo-btn:hover {
        background: var(--accent);
    }

    .checkout-btn {
        width: 100%;
        background: var(--accent);
        color: white;
        border: none;
        padding: 16px;
        border-radius: 8px;
        font-family: var(--font-mono);
        font-size: 0.78rem;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        cursor: pointer;
        transition: all 0.25s;
        position: relative;
        overflow: hidden;
    }

    .checkout-btn:hover {
        background: #cc0028;
        box-shadow: 0 12px 32px var(--glow);
        transform: translateY(-2px);
    }

    .checkout-btn:disabled {
        background: var(--grey);
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .payment-methods {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-top: 16px;
    }

    .pm-icon {
        font-family: var(--font-mono);
        font-size: 0.55rem;
        color: var(--grey);
        letter-spacing: 0.08em;
        background: var(--off-white);
        padding: 4px 10px;
        border-radius: 4px;
    }

    .secure-text {
        text-align: center;
        font-family: var(--font-mono);
        font-size: 0.58rem;
        color: var(--grey);
        letter-spacing: 0.1em;
        margin-top: 12px;
    }

    /* ─── CHECKOUT MODAL ─── */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(6px);
        z-index: 2000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s;
    }

    .modal-overlay.open {
        opacity: 1;
        pointer-events: all;
    }

    .checkout-modal {
        background: var(--white);
        border-radius: 16px;
        width: 100%;
        max-width: 520px;
        max-height: 90vh;
        overflow-y: auto;
        transform: translateY(24px) scale(0.97);
        transition: transform 0.3s;
    }

    .modal-overlay.open .checkout-modal {
        transform: none;
    }

    .modal-header {
        padding: 32px 36px 20px;
        border-bottom: 1.5px solid var(--light-grey);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .modal-eyebrow {
        font-family: var(--font-mono);
        font-size: 0.62rem;
        letter-spacing: 0.2em;
        text-transform: uppercase;
        color: var(--accent);
        margin-bottom: 6px;
    }

    .modal-title {
        font-family: var(--font-display);
        font-size: 1.3rem;
        font-weight: 900;
    }

    .modal-close {
        background: none;
        border: 1.5px solid var(--card-border);
        border-radius: 6px;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 1rem;
        color: var(--grey);
        transition: all 0.18s;
    }

    .modal-close:hover {
        border-color: var(--black);
        background: var(--black);
        color: white;
    }

    .modal-body {
        padding: 28px 36px 36px;
    }

    .checkout-steps {
        display: flex;
        gap: 24px;
        margin-bottom: 28px;
    }

    .checkout-step {
        display: flex;
        align-items: center;
        gap: 8px;
        font-family: var(--font-mono);
        font-size: 0.62rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--grey);
    }

    .checkout-step.active {
        color: var(--black);
    }

    .step-num {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        border: 1.5px solid var(--card-border);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.6rem;
        font-weight: 700;
    }

    .checkout-step.active .step-num {
        background: var(--black);
        color: white;
        border-color: var(--black);
    }

    .checkout-step.done .step-num {
        background: #22c55e;
        color: white;
        border-color: #22c55e;
    }

    .form-group {
        margin-bottom: 18px;
    }

    .form-label {
        display: block;
        font-family: var(--font-mono);
        font-size: 0.6rem;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        color: var(--grey);
        margin-bottom: 6px;
    }

    .form-input,
    .form-select {
        width: 100%;
        padding: 12px 14px;
        border: 1.5px solid var(--card-border);
        border-radius: 8px;
        font-family: var(--font-body);
        font-size: 0.88rem;
        color: var(--black);
        outline: none;
        transition: border-color 0.2s;
        background: var(--white);
    }

    .form-input:focus,
    .form-select:focus {
        border-color: var(--black);
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    .stripe-element {
        padding: 14px;
        border: 1.5px solid var(--card-border);
        border-radius: 8px;
        background: var(--white);
        transition: border-color 0.2s;
    }

    .stripe-element.StripeElement--focus {
        border-color: var(--black);
    }

    .modal-submit {
        width: 100%;
        background: var(--accent);
        color: white;
        padding: 14px;
        border: none;
        border-radius: 8px;
        font-family: var(--font-mono);
        font-size: 0.75rem;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        cursor: pointer;
        transition: all 0.2s;
        margin-top: 8px;
    }

    .modal-submit:hover {
        background: #cc0028;
        box-shadow: 0 8px 24px var(--glow);
    }

    .modal-submit:disabled {
        background: var(--grey);
        cursor: not-allowed;
    }

    /* ─── FOOTER (via footer.php) ─── */

    @media (max-width: 1000px) {
        .cart-layout {
            grid-template-columns: 1fr;
        }

        .cart-main {
            border-right: none;
            border-bottom: 1.5px solid var(--light-grey);
        }

        .cart-sidebar {
            position: static;
            max-height: none;
        }
    }

    @media (max-width: 768px) {
        nav {
            padding: 0 24px;
        }

        .cart-hero {
            padding: 40px 24px;
        }

        .cart-hero-inner {
            flex-direction: column;
            gap: 12px;
            align-items: flex-start;
        }

        .cart-main {
            padding: 24px;
        }

        .cart-sidebar {
            padding: 24px;
        }

        .cart-item {
            grid-template-columns: 64px 1fr;
            gap: 12px;
        }

        .ci-qty {
            grid-column: 2;
        }

        .ci-price-col {
            grid-column: 2;
            text-align: left;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        footer {
            padding: 32px 24px;
            flex-direction: column;
            gap: 12px;
        }
    }
    </style>
</head>

<body>

    <!-- ═══ NAVBAR ═══ -->
    <?php require_once 'assets/includes/navbar.php'; ?>

    <div class="page-wrap">

        <!-- ═══ HERO ═══ -->
        <div class="cart-hero">
            <div class="cart-hero-grid"></div>
            <div class="cart-hero-inner">
                <div>
                    <div class="cart-eyebrow">O teu carrinho · 2026</div>
                    <h1 class="cart-title">O teu <em>carrinho.</em></h1>
                </div>
                <div class="cart-hero-count" id="hero-count">0</div>
            </div>
        </div>

        <!-- ═══ CART LAYOUT ═══ -->
        <div class="cart-layout">

            <!-- Cart items -->
            <div class="cart-main">
                <div id="cart-items-container">
                    <div class="cart-empty">
                        <div class="cart-empty-icon">🛒</div>
                        <div class="cart-empty-text">A carregar o teu carrinho...</div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="cart-sidebar">
                <div class="sidebar-title">Resumo da encomenda</div>
                <div class="summary-rows">
                    <div class="summary-row">
                        <span class="summary-label">Subtotal</span>
                        <span class="summary-val" id="summary-subtotal">0.00€</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Envio</span>
                        <span class="summary-val" id="summary-envio">Grátis</span>
                    </div>
                    <div class="summary-row" id="promo-row" style="display:none;">
                        <span class="summary-label">Desconto</span>
                        <span class="summary-val" id="summary-desconto" style="color:#22c55e;">-0.00€</span>
                    </div>
                    <div class="summary-divider"></div>
                    <div class="summary-row summary-total">
                        <span class="summary-label">Total</span>
                        <span class="summary-val" id="summary-total">0.00€</span>
                    </div>
                </div>

                <div class="promo-row">
                    <input type="text" class="promo-input" id="promo-input" placeholder="Código promocional">
                    <button class="promo-btn" id="promo-btn">Aplicar</button>
                </div>

                <button class="checkout-btn" id="checkout-btn" disabled>Finalizar Compra →</button>

                <div class="payment-methods">
                    <span class="pm-icon">Visa</span>
                    <span class="pm-icon">Mastercard</span>
                    <span class="pm-icon">Stripe</span>
                    <span class="pm-icon">MB Way</span>
                </div>
                <div class="secure-text">🔒 Pagamento seguro via Stripe</div>
            </div>

        </div>
    </div>

    <!-- ═══ CHECKOUT MODAL ═══ -->
    <div class="modal-overlay" id="checkout-modal-overlay">
        <div class="checkout-modal">
            <div class="modal-header">
                <div>
                    <div class="modal-eyebrow">// Checkout</div>
                    <div class="modal-title">Finalizar Compra</div>
                </div>
                <button class="modal-close" id="close-checkout">✕</button>
            </div>
            <div class="modal-body">
                <div class="checkout-steps">
                    <div class="checkout-step active" id="step-1"><span class="step-num">1</span>Dados</div>
                    <div class="checkout-step" id="step-2"><span class="step-num">2</span>Pagamento</div>
                    <div class="checkout-step" id="step-3"><span class="step-num">3</span>Confirmação</div>
                </div>

                <!-- Step 1: Delivery Data -->
                <div id="checkout-step-1">
                    <div class="form-group">
                        <label class="form-label">Nome completo</label>
                        <input type="text" class="form-input" id="ck-nome"
                            value="<?= $user ? htmlspecialchars($user['nome']) : '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-input" id="ck-email"
                            value="<?= $user ? htmlspecialchars($user['email']) : '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Morada</label>
                        <input type="text" class="form-input" id="ck-morada" placeholder="Rua, Nº...">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Cidade</label>
                            <input type="text" class="form-input" id="ck-cidade" placeholder="Lisboa">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Código Postal</label>
                            <input type="text" class="form-input" id="ck-cp" placeholder="1000-001">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Telefone</label>
                        <input type="tel" class="form-input" id="ck-telefone" placeholder="912 345 678">
                    </div>
                    <button class="modal-submit" id="ck-next-1">Continuar para pagamento →</button>
                </div>

                <!-- Step 2: Payment -->
                <div id="checkout-step-2" style="display:none;">
                    <div class="form-group">
                        <label class="form-label">Método de pagamento</label>
                        <select class="form-select" id="ck-metodo">
                            <option value="cartao">Cartão de crédito/débito</option>
                            <option value="mbway">MB WAY</option>
                            <option value="transferencia">Transferência bancária</option>
                        </select>
                    </div>

                    <div id="stripe-card-section">
                        <div class="form-group">
                            <label class="form-label">Dados do cartão</label>
                            <div class="stripe-element" id="card-element"></div>
                            <div id="card-errors"
                                style="color:var(--accent);font-family:var(--font-mono);font-size:0.65rem;margin-top:6px;">
                            </div>
                        </div>
                    </div>

                    <div id="mbway-section" style="display:none;">
                        <div class="form-group">
                            <label class="form-label">Número MB WAY</label>
                            <input type="tel" class="form-input" id="ck-mbway-phone" placeholder="912 345 678">
                        </div>
                    </div>

                    <div id="transfer-section" style="display:none;">
                        <div
                            style="background:var(--off-white);padding:20px;border-radius:8px;font-family:var(--font-mono);font-size:0.72rem;line-height:2;">
                            <strong>IBAN:</strong> PT50 0000 0000 0000 0000 0000 0<br>
                            <strong>Banco:</strong> MangaVerse Finance<br>
                            <strong>Ref:</strong> Será gerada após confirmação
                        </div>
                    </div>

                    <div style="display:flex;gap:12px;margin-top:8px;">
                        <button class="modal-submit" id="ck-back-2"
                            style="background:var(--grey);flex:0 0 auto;width:auto;padding:14px 20px;">← Voltar</button>
                        <button class="modal-submit" id="ck-pay" style="flex:1;">Pagar <span id="ck-pay-total"></span>
                            →</button>
                    </div>
                </div>

                <!-- Step 3: Confirmation -->
                <div id="checkout-step-3" style="display:none;text-align:center;padding:40px 0;">
                    <div style="font-size:3.5rem;margin-bottom:16px;">✅</div>
                    <div style="font-family:var(--font-display);font-size:1.3rem;font-weight:900;margin-bottom:10px;">
                        Encomenda confirmada!</div>
                    <div
                        style="font-family:var(--font-mono);font-size:0.75rem;color:var(--grey);letter-spacing:0.08em;margin-bottom:8px;">
                        Encomenda <strong id="ck-order-id">#---</strong></div>
                    <div style="font-size:0.85rem;color:var(--grey);margin-bottom:28px;line-height:1.7;">Receberás um
                        email de confirmação em breve.<br>Obrigado pela tua compra!</div>
                    <a href="index.php" class="modal-submit"
                        style="display:inline-block;text-decoration:none;width:auto;padding:14px 32px;">Continuar a
                        comprar</a>
                </div>
            </div>
        </div>
    </div>

    <?php require_once 'assets/includes/footer.php'; ?>

    <script>
    $(document).ready(function() {
        var cartItems = [];
        var subtotal = 0;
        var desconto = 0;
        var promoCode = null;

        // ── Load Cart ──
        function loadCart() {
            $.ajax({
                url: 'assets/controller/controllerCarrinho.php',
                method: 'POST',
                data: {
                    acao: 'listar'
                },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        cartItems = res.carrinho || [];
                        renderCart();
                    } else if (res.redirect) {
                        // Not logged in – use localStorage fallback
                        loadLocalCart();
                    }
                },
                error: function() {
                    loadLocalCart();
                }
            });
        }

        function loadLocalCart() {
            var local = JSON.parse(localStorage.getItem('mv_cart') || '[]');
            if (local.length === 0) {
                cartItems = [];
                renderCart();
                return;
            }
            // Fetch product details
            $.ajax({
                url: 'assets/controller/controllerMangas.php',
                data: {
                    acao: 'listar'
                },
                dataType: 'json',
                success: function(res) {
                    if (!res.success) return;
                    cartItems = [];
                    local.forEach(function(li) {
                        var p = res.produtos.find(function(pr) {
                            return pr.id == li.id;
                        });
                        if (p) {
                            cartItems.push({
                                produto_id: p.id,
                                nome: p.nome,
                                autor: p.autor,
                                preco: p.preco,
                                quantidade: li.qty || 1,
                                categoria_slug: p.categoria_slug,
                                cor1: p.cor1 || '#0a0a0a',
                                cor2: p.cor2 || '#e8002d'
                            });
                        }
                    });
                    renderCart();
                }
            });
        }

        function renderCart() {
            var container = $('#cart-items-container');
            container.empty();
            updateCartCount(cartItems.reduce(function(s, i) {
                return s + parseInt(i.quantidade)
            }, 0));

            if (cartItems.length === 0) {
                container.html(
                    '<div class="cart-empty">' +
                    '<div class="cart-empty-icon">🛒</div>' +
                    '<div class="cart-empty-text">O teu carrinho está vazio.</div>' +
                    '<a href="index.php" class="cart-empty-btn">Explorar Loja →</a>' +
                    '</div>'
                );
                updateSummary();
                return;
            }

            var list = $('<div class="cart-items-list"></div>');
            cartItems.forEach(function(item) {
                var typeLabel = item.categoria_slug === 'manga' ? '// Mangá' : '// Produto';
                var itemTotal = (parseFloat(item.preco) * parseInt(item.quantidade)).toFixed(2);

                var card = $(
                    '<div class="cart-item" data-id="' + item.produto_id + '">' +
                    '<div class="ci-cover" style="background:linear-gradient(160deg,' + (item
                        .cor1 || '#0a0a0a') + ',' + (item.cor2 || '#e8002d') + ')">' + $('<span>')
                    .text(item.nome).html() + '</div>' +
                    '<div class="ci-info">' +
                    '<div class="ci-type">' + typeLabel + '</div>' +
                    '<div class="ci-name">' + $('<span>').text(item.nome).html() + '</div>' +
                    '<div class="ci-author">' + $('<span>').text(item.autor || '').html() +
                    '</div>' +
                    '</div>' +
                    '<div class="ci-qty">' +
                    '<button class="qty-btn qty-minus" data-id="' + item.produto_id +
                    '">−</button>' +
                    '<div class="qty-val">' + item.quantidade + '</div>' +
                    '<button class="qty-btn qty-plus" data-id="' + item.produto_id +
                    '">+</button>' +
                    '</div>' +
                    '<div class="ci-price-col">' +
                    '<div class="ci-price">' + itemTotal + '€</div>' +
                    '<div class="ci-remove" data-id="' + item.produto_id + '">Remover</div>' +
                    '</div>' +
                    '</div>'
                );
                list.append(card);
            });
            container.append(list);
            updateSummary();
        }

        function updateSummary() {
            subtotal = cartItems.reduce(function(sum, i) {
                return sum + parseFloat(i.preco) * parseInt(i.quantidade);
            }, 0);
            var envio = subtotal >= 30 ? 0 : 4.99;
            var total = subtotal - desconto + envio;
            if (total < 0) total = 0;

            $('#summary-subtotal').text(subtotal.toFixed(2) + '€');
            $('#summary-envio').text(envio === 0 ? 'Grátis' : envio.toFixed(2) + '€');
            $('#summary-total').text(total.toFixed(2) + '€');
            $('#hero-count').text(cartItems.length);
            $('#ck-pay-total').text(total.toFixed(2) + '€');

            $('#checkout-btn').prop('disabled', cartItems.length === 0);

            if (desconto > 0) {
                $('#promo-row').show();
                $('#summary-desconto').text('-' + desconto.toFixed(2) + '€');
            } else {
                $('#promo-row').hide();
            }
        }

        function updateCartCount(count) {
            $('#nav-cart-count').text(count);
        }

        // ── Quantity ──
        $(document).on('click', '.qty-plus', function() {
            var id = $(this).data('id');
            changeQty(id, 1);
        });

        $(document).on('click', '.qty-minus', function() {
            var id = $(this).data('id');
            changeQty(id, -1);
        });

        function changeQty(id, delta) {
            var item = cartItems.find(function(i) {
                return i.produto_id == id;
            });
            if (!item) return;
            var newQty = parseInt(item.quantidade) + delta;
            if (newQty < 1) {
                removeItem(id);
                return;
            }

            $.ajax({
                url: 'assets/controller/controllerCarrinho.php',
                method: 'POST',
                data: {
                    acao: 'atualizar',
                    produto_id: id,
                    quantidade: newQty
                },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        item.quantidade = newQty;
                        renderCart();
                    }
                },
                error: function() {
                    // localStorage fallback
                    item.quantidade = newQty;
                    saveLSCart();
                    renderCart();
                }
            });
        }

        // ── Remove ──
        $(document).on('click', '.ci-remove', function() {
            var id = $(this).data('id');
            removeItem(id);
        });

        function removeItem(id) {
            $.ajax({
                url: 'assets/controller/controllerCarrinho.php',
                method: 'POST',
                data: {
                    acao: 'remover',
                    produto_id: id
                },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        cartItems = cartItems.filter(function(i) {
                            return i.produto_id != id;
                        });
                        renderCart();
                        Swal.fire({
                            toast: true,
                            position: 'bottom-end',
                            icon: 'info',
                            title: 'Removido',
                            showConfirmButton: false,
                            timer: 1500,
                            background: '#0a0a0a',
                            color: '#fff'
                        });
                    }
                },
                error: function() {
                    cartItems = cartItems.filter(function(i) {
                        return i.produto_id != id;
                    });
                    saveLSCart();
                    renderCart();
                }
            });
        }

        function saveLSCart() {
            var ls = cartItems.map(function(i) {
                return {
                    id: i.produto_id,
                    qty: i.quantidade
                };
            });
            localStorage.setItem('mv_cart', JSON.stringify(ls));
        }

        // ── Promo Code ──
        $('#promo-btn').on('click', function() {
            var code = $('#promo-input').val().trim().toUpperCase();
            if (!code) return;

            // Demo promo codes
            var promos = {
                'MANGA10': 10,
                'OTAKU20': 20,
                'WELCOME': 5
            };
            if (promos[code]) {
                desconto = subtotal * (promos[code] / 100);
                promoCode = code;
                Swal.fire({
                    toast: true,
                    position: 'bottom-end',
                    icon: 'success',
                    title: 'Código aplicado!',
                    text: promos[code] + '% de desconto',
                    showConfirmButton: false,
                    timer: 2200,
                    background: '#0a0a0a',
                    color: '#fff',
                    iconColor: '#22c55e'
                });
                updateSummary();
            } else {
                Swal.fire({
                    toast: true,
                    position: 'bottom-end',
                    icon: 'error',
                    title: 'Código inválido',
                    showConfirmButton: false,
                    timer: 1800,
                    background: '#0a0a0a',
                    color: '#fff'
                });
            }
        });

        // ── Checkout Modal ──
        $('#checkout-btn').on('click', function() {
            <?php if (!$user): ?>
            Swal.fire({
                icon: 'info',
                title: 'Login necessário',
                text: 'Precisas de fazer login para finalizar a compra.',
                confirmButtonColor: '#0a0a0a',
                confirmButtonText: 'Ir para Login'
            }).then(function() {
                window.location.href = 'login.php';
            });
            return;
            <?php endif; ?>
            $('#checkout-modal-overlay').addClass('open');
            showStep(1);
        });

        $('#close-checkout').on('click', function() {
            $('#checkout-modal-overlay').removeClass('open');
        });
        $('#checkout-modal-overlay').on('click', function(e) {
            if (e.target === this) $(this).removeClass('open');
        });

        function showStep(n) {
            $('#checkout-step-1, #checkout-step-2, #checkout-step-3').hide();
            $('#checkout-step-' + n).show();
            $('#step-1, #step-2, #step-3').removeClass('active done');
            for (var i = 1; i < n; i++) $('#step-' + i).addClass('done');
            $('#step-' + n).addClass('active');
        }

        // Step navigation
        $('#ck-next-1').on('click', function() {
            if (!$('#ck-nome').val().trim() || !$('#ck-email').val().trim()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos obrigatórios',
                    text: 'Preenche o nome e email.',
                    confirmButtonColor: '#0a0a0a'
                });
                return;
            }
            showStep(2);
            initStripe();
        });

        $('#ck-back-2').on('click', function() {
            showStep(1);
        });

        // Payment method toggle
        $('#ck-metodo').on('change', function() {
            var m = $(this).val();
            $('#stripe-card-section, #mbway-section, #transfer-section').hide();
            if (m === 'cartao') $('#stripe-card-section').show();
            else if (m === 'mbway') $('#mbway-section').show();
            else if (m === 'transferencia') $('#transfer-section').show();
        });

        // ── Stripe ──
        var stripe, elements, cardElement, stripeInitialized = false;

        function initStripe() {
            if (stripeInitialized) return;
            stripeInitialized = true;
            // Note: Replace with your real Stripe publishable key
            stripe = Stripe(
                'pk_test_51SAniYBgsjq4eGslSQL7yh2GZw8cmWjzm2ECCRUDNsOoINeLwjXHIBHeDre0PQMu1qSoDZOb2g5EGPuRP4n6R2co00YQOyOxT9'
                );
            elements = stripe.elements();
            cardElement = elements.create('card', {
                style: {
                    base: {
                        fontFamily: '"Space Mono", monospace',
                        fontSize: '14px',
                        color: '#0a0a0a',
                        '::placeholder': {
                            color: '#8a8a8a'
                        }
                    }
                }
            });
            cardElement.mount('#card-element');
            cardElement.on('change', function(e) {
                var d = document.getElementById('card-errors');
                d.textContent = e.error ? e.error.message : '';
            });
        }

        // ── Process Payment ──
        $('#ck-pay').on('click', function() {
            var btn = $(this);
            btn.prop('disabled', true).text('A processar...');

            var metodo = $('#ck-metodo').val();
            var envio = subtotal >= 30 ? 0 : 4.99;
            var total = (subtotal - desconto + envio).toFixed(2);

            // Build order items
            var itens = cartItems.map(function(i) {
                return {
                    produto_id: i.produto_id,
                    quantidade: i.quantidade,
                    preco: i.preco
                };
            });

            if (metodo === 'cartao' && stripe) {
                // Stripe token (demo - in production use server-side PaymentIntent)
                stripe.createToken(cardElement).then(function(result) {
                    if (result.error) {
                        $('#card-errors').text(result.error.message);
                        btn.prop('disabled', false).text('Pagar ' + total + '€ →');
                        return;
                    }
                    finalizarCompra(metodo, total, itens, result.token.id, btn);
                });
            } else {
                finalizarCompra(metodo, total, itens, null, btn);
            }
        });

        function finalizarCompra(metodo, total, itens, stripeToken, btn) {
            $.ajax({
                url: 'assets/controller/controllerCarrinho.php',
                method: 'POST',
                data: {
                    acao: 'finalizar',
                    metodo_pagamento: metodo,
                    total: total,
                    itens: JSON.stringify(itens),
                    stripe_token: stripeToken || '',
                    morada: $('#ck-morada').val(),
                    cidade: $('#ck-cidade').val(),
                    codigo_postal: $('#ck-cp').val(),
                    telefone: $('#ck-telefone').val(),
                    codigo_promo: promoCode || ''
                },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        // Clear cart and redirect to marketplace
                        cartItems = [];
                        localStorage.removeItem('mv_cart');
                        Swal.fire({
                            icon: 'success',
                            title: 'Encomenda confirmada!',
                            text: 'Encomenda #' + res.encomenda_id + ' registada com sucesso.',
                            confirmButtonColor: '#e8002d',
                            confirmButtonText: 'Continuar a comprar'
                        }).then(function() {
                            window.location.href = 'marketplace.php';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro no pagamento',
                            text: res.message || 'Erro ao processar pagamento.',
                            confirmButtonColor: '#e8002d'
                        });
                        btn.prop('disabled', false).text('Pagar ' + total + '€ →');
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro de ligação',
                        text: 'Verifica a tua ligação e tenta novamente.',
                        confirmButtonColor: '#e8002d'
                    });
                    btn.prop('disabled', false).text('Pagar ' + total + '€ →');
                }
            });
        }

        // ── Init ──
        loadCart();
    });
    </script>
</body>

</html>