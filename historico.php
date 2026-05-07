<?php
require_once 'assets/model/modelCarrinho.php';
require_once 'assets/model/modelAvaliacoes.php';

initSession();

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getLoggedUser();
$currentPage = 'historico';
$basePath = '';
$historico = ModelCarrinho::getHistoricoDetalhado((int) $user['id']);
$siteRating = ModelAvaliacoes::getSiteResumo();

function historicoEstadoInfo(string $estado): array {
    $map = [
        'pendente' => ['label' => 'Pendente', 'class' => 'status-pending'],
        'pago' => ['label' => 'Pago', 'class' => 'status-paid'],
        'enviado' => ['label' => 'Enviado', 'class' => 'status-shipped'],
        'entregue' => ['label' => 'Entregue', 'class' => 'status-delivered'],
        'cancelado' => ['label' => 'Cancelado', 'class' => 'status-cancelled'],
    ];

    return $map[$estado] ?? ['label' => ucfirst($estado), 'class' => 'status-pending'];
}

function historicoMetodoLabel(string $metodo): string {
    $map = [
        'cartao' => 'Cartão',
        'mbway' => 'MB WAY',
        'transferencia' => 'Transferência',
    ];

    return $map[$metodo] ?? ucfirst($metodo);
}

function historicoStars(int $value): string {
    return str_repeat('★', max(0, min(5, $value))) . str_repeat('☆', max(0, 5 - min(5, $value)));
}
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Compras — MangaVerse</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Noto+Sans+JP:wght@300;400;700&family=Space+Mono:wght@400;700&display=swap"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
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

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: var(--font-body);
        background: var(--white);
        color: var(--black);
        overflow-x: hidden;
    }

    .page-wrap {
        padding-top: 72px;
        min-height: 100vh;
    }

    .history-hero {
        background: #0a0a0a;
        color: white;
        padding: 76px 80px 64px;
        position: relative;
        overflow: hidden;
    }

    .history-hero::before {
        content: '購入';
        position: absolute;
        right: 48px;
        top: 50%;
        transform: translateY(-50%);
        font-family: var(--font-display);
        font-size: 15rem;
        color: rgba(255, 255, 255, 0.04);
        line-height: 1;
        pointer-events: none;
    }

    .history-hero-grid {
        position: absolute;
        inset: 0;
        background-image: linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        background-size: 56px 56px;
        pointer-events: none;
    }

    .history-hero-inner {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 40px;
        align-items: end;
    }

    .history-kicker {
        font-family: var(--font-mono);
        font-size: 0.7rem;
        letter-spacing: 0.22em;
        text-transform: uppercase;
        color: var(--accent);
        margin-bottom: 18px;
    }

    .history-title {
        font-family: var(--font-display);
        font-size: clamp(2.2rem, 4.4vw, 4rem);
        font-weight: 900;
        line-height: 1.05;
        margin-bottom: 18px;
    }

    .history-desc {
        max-width: 600px;
        font-size: 1rem;
        line-height: 1.8;
        color: rgba(255, 255, 255, 0.62);
    }

    .history-hero-stats {
        display: flex;
        gap: 28px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .history-stat {
        min-width: 140px;
        text-align: right;
    }

    .history-stat-num {
        display: block;
        font-family: var(--font-display);
        font-size: 2rem;
        font-weight: 900;
    }

    .history-stat-label,
    .history-stat-meta {
        display: block;
        font-family: var(--font-mono);
        font-size: 0.62rem;
        letter-spacing: 0.16em;
        text-transform: uppercase;
    }

    .history-stat-label {
        color: rgba(255, 255, 255, 0.4);
        margin-top: 6px;
    }

    .history-stat-meta {
        color: rgba(255, 255, 255, 0.28);
        margin-top: 4px;
    }

    .history-main {
        padding: 36px 32px 64px;
        background: linear-gradient(180deg, var(--off-white) 0%, #ffffff 160px);
    }

    .history-shell {
        max-width: 1240px;
        margin: 0 auto;
    }

    .history-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 28px;
        flex-wrap: wrap;
    }

    .history-toolbar-copy h2 {
        font-family: var(--font-display);
        font-size: 1.45rem;
        margin-bottom: 8px;
    }

    .history-toolbar-copy p {
        color: var(--grey);
        line-height: 1.7;
        max-width: 700px;
    }

    .history-toolbar-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .history-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 46px;
        padding: 0 20px;
        border-radius: 10px;
        border: 1.5px solid #0a0a0a;
        background: #0a0a0a;
        color: white;
        text-decoration: none;
        font-family: var(--font-mono);
        font-size: 0.68rem;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        cursor: pointer;
        transition: transform 0.18s, background 0.18s, border-color 0.18s;
    }

    .history-btn:hover {
        background: var(--accent);
        border-color: var(--accent);
        transform: translateY(-1px);
    }

    .history-btn-secondary {
        background: transparent;
        color: var(--black);
    }

    .history-btn-surface {
        background: #ffffff;
        border-color: #0a0a0a;
        color: #0e0e0e;
    }

    .history-btn-secondary:hover {
        color: white;
    }

    .history-btn-surface:hover {
        color: white;
    }

    .history-empty {
        background: var(--white);
        border: 1.5px solid var(--card-border);
        border-radius: 24px;
        padding: 56px 28px;
        text-align: center;
    }

    .history-empty-kicker {
        font-family: var(--font-mono);
        font-size: 0.64rem;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: var(--accent);
        margin-bottom: 12px;
    }

    .history-empty h3 {
        font-family: var(--font-display);
        font-size: 1.7rem;
        margin-bottom: 12px;
    }

    .history-empty p {
        max-width: 520px;
        margin: 0 auto 28px;
        color: var(--grey);
        line-height: 1.8;
    }

    .history-orders {
        display: grid;
        gap: 22px;
    }

    .history-order-card {
        background: var(--white);
        border: 1.5px solid var(--card-border);
        border-radius: 22px;
        overflow: hidden;
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.05);
    }

    .history-order-head {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        padding: 26px 28px 22px;
        border-bottom: 1px solid var(--light-grey);
        flex-wrap: wrap;
    }

    .history-order-kicker {
        font-family: var(--font-mono);
        font-size: 0.6rem;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: var(--accent);
        margin-bottom: 10px;
    }

    .history-order-title {
        font-family: var(--font-display);
        font-size: 1.18rem;
        margin-bottom: 10px;
    }

    .history-order-meta {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .history-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 12px;
        border-radius: 999px;
        background: var(--off-white);
        color: var(--grey);
        font-family: var(--font-mono);
        font-size: 0.6rem;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    .status-paid,
    .status-delivered {
        background: rgba(20, 166, 104, 0.12);
        color: #12724c;
    }

    .status-shipped {
        background: rgba(0, 87, 255, 0.12);
        color: #0047d1;
    }

    .status-pending {
        background: rgba(240, 165, 0, 0.14);
        color: #9a6600;
    }

    .status-cancelled {
        background: rgba(232, 0, 45, 0.12);
        color: #a00022;
    }

    .history-order-total {
        min-width: 220px;
        text-align: right;
    }

    .history-order-total-label {
        font-family: var(--font-mono);
        font-size: 0.62rem;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: var(--grey);
        margin-bottom: 10px;
    }

    .history-order-total-value {
        font-family: var(--font-display);
        font-size: 2rem;
        font-weight: 900;
    }

    .history-order-items {
        display: grid;
        gap: 18px;
        padding: 22px 28px 28px;
    }

    .history-item {
        display: grid;
        grid-template-columns: 124px minmax(0, 1fr) minmax(280px, 340px);
        gap: 18px;
        padding: 18px;
        border: 1.5px solid var(--light-grey);
        border-radius: 18px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(247, 247, 245, 0.82) 100%);
        align-items: stretch;
    }

    .history-item-cover {
        width: 124px;
        min-height: 164px;
        border-radius: 14px;
        overflow: hidden;
        position: relative;
        background: linear-gradient(160deg, #0a0a0a, #e8002d);
        display: flex;
        align-items: flex-end;
        justify-content: center;
        padding: 14px;
    }

    .history-item-cover img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .history-item-cover span {
        position: relative;
        z-index: 1;
        font-family: var(--font-display);
        font-size: 0.74rem;
        font-weight: 700;
        color: white;
        text-align: center;
        line-height: 1.3;
        text-shadow: 0 1px 6px rgba(0, 0, 0, 0.5);
    }

    .history-item-body {
        min-width: 0;
    }

    .history-item-type {
        font-family: var(--font-mono);
        font-size: 0.58rem;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: var(--accent);
        margin-bottom: 8px;
    }

    .history-item-title {
        font-family: var(--font-display);
        font-size: 1.06rem;
        line-height: 1.3;
        margin-bottom: 4px;
    }

    .history-item-author {
        font-size: 0.9rem;
        color: var(--grey);
        margin-bottom: 14px;
    }

    .history-item-details {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 16px;
    }

    .history-item-detail {
        padding: 7px 12px;
        border-radius: 999px;
        background: var(--white);
        border: 1px solid var(--card-border);
        font-family: var(--font-mono);
        font-size: 0.6rem;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--grey);
    }

    .history-item-rating-site {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-family: var(--font-mono);
        font-size: 0.66rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--grey);
    }

    .history-item-rating-site strong {
        color: var(--black);
    }

    .history-rating-panel {
        border: 1.5px solid var(--card-border);
        border-radius: 16px;
        background: var(--white);
        padding: 18px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-width: 0;
    }

    .history-rating-title {
        font-family: var(--font-display);
        font-size: 0.95rem;
        margin-bottom: 8px;
    }

    .history-rating-copy {
        color: var(--grey);
        font-size: 0.84rem;
        line-height: 1.6;
        margin-bottom: 16px;
    }

    .rating-stars {
        display: flex;
        gap: 8px;
        margin-bottom: 14px;
    }

    .rating-star-btn {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        border: 1.5px solid var(--card-border);
        background: var(--off-white);
        color: #c4c4c4;
        font-size: 1.2rem;
        cursor: pointer;
        transition: transform 0.18s, border-color 0.18s, color 0.18s, background 0.18s;
    }

    .rating-star-btn:hover,
    .rating-star-btn.active {
        border-color: var(--accent);
        color: var(--accent);
        background: rgba(232, 0, 45, 0.08);
        transform: translateY(-1px);
    }

    .rating-hint {
        font-family: var(--font-mono);
        font-size: 0.58rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--grey);
        line-height: 1.7;
        margin-bottom: 14px;
    }

    .rating-submit {
        width: 100%;
    }

    .rating-summary {
        display: grid;
        gap: 10px;
    }

    .rating-summary-badge {
        font-family: var(--font-mono);
        font-size: 0.58rem;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: var(--accent);
    }

    .rating-summary-stars {
        font-family: var(--font-display);
        font-size: 1.2rem;
        letter-spacing: 0.08em;
    }

    .rating-summary-stars span {
        font-family: var(--font-mono);
        font-size: 0.64rem;
        color: var(--grey);
        margin-left: 8px;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .rating-summary-meta {
        color: var(--grey);
        font-size: 0.84rem;
        line-height: 1.6;
    }

    body.dark-mode .history-main {
        background: linear-gradient(180deg, #111111 0%, #0e0e0e 180px);
    }

    body.dark-mode .history-order-card,
    body.dark-mode .history-empty,
    body.dark-mode .history-rating-panel,
    body.dark-mode .history-item {
        background: #181818;
        border-color: #333;
        color: #f0f0f0;
    }

    body.dark-mode .history-order-head {
        border-bottom-color: #2b2b2b;
    }

    body.dark-mode .history-toolbar-copy p,
    body.dark-mode .history-empty p,
    body.dark-mode .history-item-author,
    body.dark-mode .history-rating-copy,
    body.dark-mode .rating-summary-meta,
    body.dark-mode .history-item-rating-site,
    body.dark-mode .history-item-detail,
    body.dark-mode .history-chip,
    body.dark-mode .history-order-total-label,
    body.dark-mode .rating-hint {
        color: #999;
    }

    body.dark-mode .history-btn-secondary {
        color: #f0f0f0;
        border-color: #444;
    }

    body.dark-mode .history-btn-surface {
        background: #f0f0f0;
        border-color: #f0f0f0;
        color: #0e0e0e;
    }

    body.dark-mode .history-btn-secondary:hover {
        border-color: var(--accent);
    }

    body.dark-mode .history-item-detail,
    body.dark-mode .history-chip,
    body.dark-mode .rating-star-btn {
        background: #121212;
        border-color: #333;
    }

    body.dark-mode .history-item-rating-site strong,
    body.dark-mode .history-order-total-value,
    body.dark-mode .history-order-title {
        color: #f0f0f0;
    }

    @media (max-width: 1024px) {
        .history-hero,
        .history-main {
            padding-left: 24px;
            padding-right: 24px;
        }

        .history-hero-inner {
            grid-template-columns: 1fr;
        }

        .history-hero-stats {
            justify-content: flex-start;
        }

        .history-item {
            grid-template-columns: 112px minmax(0, 1fr);
        }

        .history-rating-panel {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 700px) {
        .history-hero {
            padding-top: 60px;
            padding-bottom: 52px;
        }

        .history-order-head,
        .history-order-items {
            padding-left: 18px;
            padding-right: 18px;
        }

        .history-item {
            grid-template-columns: 1fr;
        }

        .history-item-cover {
            width: 100%;
            min-height: 220px;
        }

        .history-order-total {
            text-align: left;
        }
    }
    </style>
</head>

<body>
    <?php require_once 'assets/includes/navbar.php'; ?>

    <div class="page-wrap">
        <section class="history-hero">
            <div class="history-hero-grid"></div>
            <div class="history-hero-inner">
                <div>
                    <div class="history-kicker">Histórico · Compras · 2026</div>
                    <h1 class="history-title">As tuas compras,<br>prontas para rever.</h1>
                    <p class="history-desc">Consulta as encomendas concluídas, avalia cada produto uma única vez e gera um PDF com o teu histórico sempre que precisares.</p>
                </div>
                <div class="history-hero-stats">
                    <div class="history-stat">
                        <span class="history-stat-num"><?= count($historico) ?></span>
                        <span class="history-stat-label">Encomendas</span>
                        <span class="history-stat-meta">Total registado</span>
                    </div>
                    <div class="history-stat">
                        <span class="history-stat-num" id="historico-site-rating"><?= number_format((float) $siteRating['media'], 1) ?>★</span>
                        <span class="history-stat-label">Avaliação</span>
                        <span class="history-stat-meta" id="historico-site-rating-meta"><?= (int) $siteRating['total'] > 0 ? (int) $siteRating['total'] . ' avaliações' : 'Sem avaliações ainda' ?></span>
                    </div>
                </div>
            </div>
        </section>

        <main class="history-main">
            <div class="history-shell">
                <div class="history-toolbar">
                    <div class="history-toolbar-copy">
                        <h2>Resumo de compras e classificações</h2>
                        <p>As avaliações ficam bloqueadas depois do envio. O PDF inclui as encomendas, produtos, quantidades, preços e o estado das classificações no momento da exportação.</p>
                    </div>
                    <div class="history-toolbar-actions">
                        <button type="button" class="history-btn history-btn-secondary" id="download-history-pdf" <?= empty($historico) ? 'disabled' : '' ?>>Transferir PDF</button>
                        <a href="marketplace.php" class="history-btn history-btn-surface">Voltar ao marketplace</a>
                    </div>
                </div>

                <?php if (empty($historico)): ?>
                <section class="history-empty">
                    <div class="history-empty-kicker">Sem compras registadas</div>
                    <h3>Ainda não tens encomendas.</h3>
                    <p>Quando finalizares uma compra, o teu histórico fica disponível aqui com as opções de avaliação e exportação para PDF.</p>
                    <a href="marketplace.php" class="history-btn">Explorar Marketplace</a>
                </section>
                <?php else: ?>
                <section class="history-orders">
                    <?php foreach ($historico as $encomenda): ?>
                    <?php $estadoInfo = historicoEstadoInfo((string) $encomenda['estado']); ?>
                    <article class="history-order-card">
                        <div class="history-order-head">
                            <div>
                                <div class="history-order-kicker">Encomenda #<?= (int) $encomenda['id'] ?></div>
                                <div class="history-order-title">Compra feita em <?= htmlspecialchars(date('d/m/Y', strtotime((string) $encomenda['criado_em']))) ?></div>
                                <div class="history-order-meta">
                                    <span class="history-chip <?= htmlspecialchars($estadoInfo['class']) ?>"><?= htmlspecialchars($estadoInfo['label']) ?></span>
                                    <span class="history-chip">Pagamento: <?= htmlspecialchars(historicoMetodoLabel((string) $encomenda['metodo_pagamento'])) ?></span>
                                    <span class="history-chip"><?= count($encomenda['itens']) ?> item<?= count($encomenda['itens']) !== 1 ? 's' : '' ?></span>
                                </div>
                            </div>
                            <div class="history-order-total">
                                <div class="history-order-total-label">Total pago</div>
                                <div class="history-order-total-value"><?= number_format((float) $encomenda['total'], 2) ?>€</div>
                            </div>
                        </div>

                        <div class="history-order-items">
                            <?php foreach ($encomenda['itens'] as $item): ?>
                            <div class="history-item">
                                <?php $coverStyle = 'background:linear-gradient(160deg,' . htmlspecialchars((string) $item['cor1']) . ',' . htmlspecialchars((string) $item['cor2']) . ')'; ?>
                                <div class="history-item-cover" style="<?= $coverStyle ?>">
                                    <?php if (!empty($item['imagem'])): ?>
                                    <img src="<?= htmlspecialchars((string) $item['imagem']) ?>" alt="<?= htmlspecialchars((string) $item['nome']) ?>">
                                    <?php endif; ?>
                                    <span><?= htmlspecialchars((string) $item['nome']) ?></span>
                                </div>

                                <div class="history-item-body">
                                    <div class="history-item-type">// Produto comprado</div>
                                    <div class="history-item-title"><?= htmlspecialchars((string) $item['nome']) ?><?= !empty($item['volume']) ? ' — ' . htmlspecialchars((string) $item['volume']) : '' ?></div>
                                    <div class="history-item-author">por <?= htmlspecialchars((string) $item['autor']) ?></div>
                                    <div class="history-item-details">
                                        <span class="history-item-detail">Quantidade: <?= (int) $item['quantidade'] ?></span>
                                        <span class="history-item-detail">Preço unitário: <?= number_format((float) $item['preco_unitario'], 2) ?>€</span>
                                        <span class="history-item-detail">Subtotal: <?= number_format((float) $item['preco_unitario'] * (int) $item['quantidade'], 2) ?>€</span>
                                    </div>
                                    <div class="history-item-rating-site">
                                        <?php if ((int) $item['rating_total'] > 0 && $item['rating_media'] !== null): ?>
                                        <strong><?= number_format((float) $item['rating_media'], 1) ?>★</strong>
                                        <span><?= (int) $item['rating_total'] ?> avaliação<?= (int) $item['rating_total'] !== 1 ? 'ões' : '' ?> no site</span>
                                        <?php else: ?>
                                        <strong>Sem avaliação</strong>
                                        <span>Este produto ainda não recebeu classificações.</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="history-rating-panel" data-product-id="<?= (int) $item['produto_id'] ?>">
                                    <?php if ($item['minha_classificacao'] !== null): ?>
                                    <div class="rating-summary">
                                        <div class="rating-summary-badge">Avaliação guardada</div>
                                        <div class="rating-summary-stars"><?= historicoStars((int) $item['minha_classificacao']) ?><span><?= (int) $item['minha_classificacao'] ?>/5</span></div>
                                        <div class="rating-summary-meta">
                                            Não podes voltar a avaliar este produto.<?= (int) $item['rating_total'] > 0 && $item['rating_media'] !== null ? ' Média atual do site: ' . number_format((float) $item['rating_media'], 1) . '★.' : '' ?>
                                        </div>
                                    </div>
                                    <?php elseif ($item['pode_avaliar']): ?>
                                    <div class="history-rating-title">Avaliar produto</div>
                                    <div class="history-rating-copy">Escolhe a tua classificação. Depois de enviada, a tua avaliação fica bloqueada e passa a contar para a média do site.</div>
                                    <form class="rating-form" data-product-id="<?= (int) $item['produto_id'] ?>">
                                        <input type="hidden" name="produto_id" value="<?= (int) $item['produto_id'] ?>">
                                        <input type="hidden" name="classificacao" value="">
                                        <div class="rating-stars">
                                            <?php for ($estrela = 1; $estrela <= 5; $estrela++): ?>
                                            <button type="button" class="rating-star-btn" data-value="<?= $estrela ?>" aria-label="<?= $estrela ?> estrela<?= $estrela > 1 ? 's' : '' ?>">★</button>
                                            <?php endfor; ?>
                                        </div>
                                        <div class="rating-hint">Uma avaliação por produto. A média do marketplace é atualizada no fim.</div>
                                        <button type="submit" class="history-btn rating-submit">Guardar avaliação</button>
                                    </form>
                                    <?php else: ?>
                                    <div class="rating-summary">
                                        <div class="rating-summary-badge">Avaliação indisponível</div>
                                        <div class="rating-summary-meta">Este item ainda não pode ser avaliado no estado atual da encomenda.</div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </section>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
    const historicoData = <?= json_encode($historico, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    function formatEuro(value) {
        return Number(value || 0).toFixed(2) + '€';
    }

    function formatDate(value) {
        const date = new Date(String(value || '').replace(' ', 'T'));
        if (Number.isNaN(date.getTime())) {
            return value || '';
        }

        return date.toLocaleDateString('pt-PT');
    }

    function buildRatingSummaryHtml(stars, productSummary) {
        const total = Number(productSummary && productSummary.total ? productSummary.total : 0);
        const media = productSummary && productSummary.media !== null && productSummary.media !== undefined ? Number(productSummary.media).toFixed(1) + '★' : 'Sem avaliação';

        return '<div class="rating-summary">' +
            '<div class="rating-summary-badge">Avaliação guardada</div>' +
            '<div class="rating-summary-stars">' + '★'.repeat(stars) + '☆'.repeat(5 - stars) + '<span>' + stars + '/5</span></div>' +
            '<div class="rating-summary-meta">Não podes voltar a avaliar este produto.' +
            (total > 0 ? ' Média atual do site: ' + media + '.' : '') +
            '</div>' +
            '</div>';
    }

    function updateSiteRating(resumo) {
        if (!resumo) return;
        const media = Number(resumo.media || 0).toFixed(1) + '★';
        const total = Number(resumo.total || 0);
        $('#historico-site-rating').text(media);
        $('#historico-site-rating-meta').text(total > 0 ? total + ' avaliações' : 'Sem avaliações ainda');
    }

    function exportHistoryPdf() {
        if (!window.jspdf || !window.jspdf.jsPDF) {
            window.print();
            return;
        }

        const {
            jsPDF
        } = window.jspdf;
        const doc = new jsPDF({
            unit: 'pt',
            format: 'a4'
        });
        const pageHeight = doc.internal.pageSize.getHeight();
        const marginX = 42;
        const contentWidth = doc.internal.pageSize.getWidth() - marginX * 2;
        let cursorY = 44;

        function ensureSpace(extraHeight) {
            if (cursorY + extraHeight <= pageHeight - 42) {
                return;
            }

            doc.addPage();
            cursorY = 44;
        }

        function writeLine(text, options) {
            const config = Object.assign({
                size: 11,
                weight: 'normal',
                gap: 16,
            }, options || {});

            doc.setFont('helvetica', config.weight);
            doc.setFontSize(config.size);
            const lines = doc.splitTextToSize(String(text), contentWidth);
            const blockHeight = lines.length * (config.size + 2) + config.gap;
            ensureSpace(blockHeight);
            doc.text(lines, marginX, cursorY);
            cursorY += blockHeight;
        }

        writeLine('MangaVerse - Histórico de Compras', {
            size: 20,
            weight: 'bold',
            gap: 22
        });
        writeLine('Gerado em ' + new Date().toLocaleString('pt-PT'), {
            size: 10,
            gap: 20
        });

        historicoData.forEach(function(encomenda) {
            writeLine('Encomenda #' + encomenda.id + ' · ' + formatDate(encomenda.criado_em) + ' · Total ' + formatEuro(encomenda.total), {
                size: 13,
                weight: 'bold',
                gap: 14
            });

            writeLine('Estado: ' + encomenda.estado + ' · Pagamento: ' + encomenda.metodo_pagamento, {
                size: 10,
                gap: 12
            });

            (encomenda.itens || []).forEach(function(item) {
                const ratingText = item.minha_classificacao ? item.minha_classificacao + '/5' : 'por avaliar';
                writeLine('- ' + item.nome + (item.volume ? ' — ' + item.volume : '') + ' | Quantidade: ' + item.quantidade + ' | Preço: ' + formatEuro(item.preco_unitario) + ' | Tua avaliação: ' + ratingText, {
                    size: 10,
                    gap: 10
                });
            });

            cursorY += 6;
        });

        doc.save('historico-mangaverse.pdf');
    }

    $(function() {
        $('#download-history-pdf').on('click', exportHistoryPdf);

        $(document).on('click', '.rating-star-btn', function() {
            const button = $(this);
            const form = button.closest('.rating-form');
            const value = Number(button.data('value'));

            form.find('input[name="classificacao"]').val(value);
            form.find('.rating-star-btn').each(function() {
                const current = Number($(this).data('value'));
                $(this).toggleClass('active', current <= value);
            });
        });

        $(document).on('submit', '.rating-form', function(e) {
            e.preventDefault();

            const form = $(this);
            const productId = form.data('product-id');
            const stars = Number(form.find('input[name="classificacao"]').val());

            if (!stars) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Escolhe a classificação',
                    text: 'Seleciona entre 1 e 5 estrelas antes de guardar.',
                    confirmButtonColor: '#e8002d'
                });
                return;
            }

            const submitButton = form.find('button[type="submit"]');
            submitButton.prop('disabled', true).text('A guardar...');

            $.ajax({
                url: 'assets/controller/controllerAvaliacoes.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    acao: 'criar',
                    produto_id: productId,
                    classificacao: stars
                },
                success: function(res) {
                    if (!res.success) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: res.message || 'Não foi possível guardar a avaliação.',
                            confirmButtonColor: '#e8002d'
                        });
                        submitButton.prop('disabled', false).text('Guardar avaliação');
                        return;
                    }

                    $('.history-rating-panel[data-product-id="' + productId + '"]').html(buildRatingSummaryHtml(stars, res.produto_resumo || {}));

                    if (res.site_resumo) {
                        updateSiteRating(res.site_resumo);
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Avaliação guardada',
                        text: 'A tua classificação já conta para a média do site.',
                        confirmButtonColor: '#0a0a0a'
                    });
                },
                error: function(xhr) {
                    const data = xhr.responseJSON || {};
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: data.message || 'Não foi possível guardar a avaliação.',
                        confirmButtonColor: '#e8002d'
                    });
                    submitButton.prop('disabled', false).text('Guardar avaliação');
                }
            });
        });
    });
    </script>
</body>

</html>