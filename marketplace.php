<?php
require_once 'assets/config/database.php';
require_once 'assets/model/modelAvaliacoes.php';
initSession();
$user = getLoggedUser();
$canCreateMangas = $user && in_array($user['role'], ['vendedor', 'admin'], true);
$siteRating = ModelAvaliacoes::getSiteResumo();
$currentPage = 'marketplace';
$basePath    = '';
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Marketplace — MangaVerse</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Noto+Sans+JP:wght@300;400;700&family=Space+Mono:wght@400;700&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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

    /* navbar via assets/includes/navbar.php */

    .page-wrap {
        padding-top: 72px;
    }

    /* ─── HERO ─── */
    .mp-hero {
        background: var(--black);
        color: white;
        min-height: 46vh;
        padding: 72px 80px 64px;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
    }

    .mp-hero::before {
        content: '市場';
        position: absolute;
        right: 60px;
        top: 50%;
        transform: translateY(-50%);
        font-family: var(--font-display);
        font-size: 18rem;
        font-weight: 900;
        color: rgba(255, 255, 255, 0.03);
        pointer-events: none;
    }

    .mp-hero-grid {
        position: absolute;
        inset: 0;
        background-image: linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        background-size: 60px 60px;
        pointer-events: none;
    }

    .mp-hero-inner {
        position: relative;
        z-index: 2;
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(220px, auto);
        align-items: center;
        gap: 48px;
        width: 100%;
    }

    .mp-hero-copy {
        max-width: 620px;
    }

    .mp-eyebrow {
        font-family: var(--font-mono);
        font-size: 0.7rem;
        letter-spacing: 0.25em;
        text-transform: uppercase;
        color: var(--accent);
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .mp-eyebrow::before {
        content: '';
        width: 32px;
        height: 1.5px;
        background: var(--accent);
    }

    .mp-title {
        font-family: var(--font-display);
        font-size: clamp(2.4rem, 5vw, 4.2rem);
        font-weight: 900;
        line-height: 1.05;
        letter-spacing: -0.02em;
        margin-bottom: 28px;
    }

    .mp-title em {
        font-style: normal;
        color: var(--accent);
    }

    .mp-desc {
        font-size: 1.05rem;
        line-height: 1.75;
        color: rgba(255, 255, 255, 0.55);
        max-width: 540px;
    }

    .mp-hero-stats {
        display: flex;
        gap: 40px;
        flex-shrink: 0;
    }

    .mp-stat {
        text-align: right;
    }

    .mp-stat-num {
        font-family: var(--font-display);
        font-size: 2.2rem;
        font-weight: 900;
        color: white;
        display: block;
    }

    .mp-stat-label {
        font-family: var(--font-mono);
        font-size: 0.62rem;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.35);
    }

    .mp-stat-sub {
        display: block;
        margin-top: 6px;
        font-family: var(--font-mono);
        font-size: 0.56rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.25);
    }

    /* ─── SELL BANNER ─── */
    .seller-banner {
        background: var(--accent);
        color: white;
        padding: 20px 80px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
    }

    .seller-banner-text {
        font-family: var(--font-mono);
        font-size: 0.75rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .seller-banner-text::before {
        content: 'POST';
        font-size: 0.62rem;
        font-weight: 700;
        padding: 3px 6px;
        border: 1px solid rgba(255, 255, 255, 0.55);
        border-radius: 999px;
    }

    .seller-banner-cta {
        background: white;
        color: var(--accent);
        padding: 8px 20px;
        border-radius: 4px;
        font-family: var(--font-mono);
        font-size: 0.68rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        text-decoration: none;
        font-weight: 700;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }

    .seller-banner-cta:hover {
        background: var(--black);
        color: white;
    }

    /* ─── LAYOUT ─── */
    .mp-layout {
        display: grid;
        grid-template-columns: minmax(300px, 328px) minmax(0, 1fr);
        gap: 24px;
        align-items: start;
        min-height: 80vh;
        padding: 32px 32px 0;
    }

    /* ─── SIDEBAR ─── */
    .mp-sidebar {
        padding: 24px;
        border: 1.5px solid var(--card-border);
        border-radius: 20px;
        background: var(--off-white);
        position: sticky;
        top: 96px;
        max-height: calc(100vh - 128px);
        overflow-y: auto;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.04);
    }

    .mp-sidebar-head {
        margin-bottom: 20px;
    }

    .sidebar-kicker {
        font-family: var(--font-mono);
        font-size: 0.58rem;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: var(--accent);
        margin-bottom: 10px;
    }

    .sidebar-title {
        font-family: var(--font-display);
        font-size: 1.18rem;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .sidebar-desc {
        font-size: 0.86rem;
        line-height: 1.65;
        color: var(--grey);
    }

    .sidebar-section {
        margin-bottom: 14px;
        padding: 16px;
        border: 1.5px solid var(--card-border);
        border-radius: 14px;
        background: var(--white);
    }

    .sidebar-section-title {
        font-family: var(--font-mono);
        font-size: 0.58rem;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: var(--accent);
        margin-bottom: 12px;
    }

    .sidebar-search {
        position: relative;
    }

    .sidebar-search input {
        width: 100%;
        padding: 12px 14px 12px 38px;
        border: 1.5px solid var(--card-border);
        border-radius: 10px;
        font-family: var(--font-mono);
        font-size: 0.74rem;
        letter-spacing: 0.04em;
        outline: none;
        color: var(--black);
        transition: border-color 0.2s;
        background: var(--white);
    }

    .sidebar-search input:focus {
        border-color: var(--black);
    }

    .sidebar-search::before {
        content: '⌕';
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--grey);
        font-size: 1rem;
        pointer-events: none;
    }

    .cat-list {
        display: grid;
        gap: 8px;
    }

    .cat-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 14px;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.18s;
        border: 1.5px solid var(--light-grey);
        background: var(--off-white);
    }

    .cat-item:hover {
        border-color: var(--black);
    }

    .cat-item.active {
        border-color: var(--black);
        background: var(--black);
    }

    .cat-item.active .cat-name {
        color: white;
    }

    .cat-item.active .cat-count {
        background: var(--accent);
        color: white;
    }

    .cat-name {
        font-family: var(--font-mono);
        font-size: 0.64rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--black);
    }

    .cat-count {
        font-family: var(--font-mono);
        font-size: 0.58rem;
        background: var(--light-grey);
        padding: 3px 8px;
        border-radius: 100px;
        color: var(--grey);
    }

    .price-inputs {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
        gap: 10px;
        align-items: center;
    }

    .price-input {
        min-width: 0;
        padding: 11px 12px;
        border: 1.5px solid var(--card-border);
        border-radius: 10px;
        font-family: var(--font-mono);
        font-size: 0.72rem;
        outline: none;
        color: var(--black);
        transition: border-color 0.2s;
        background: var(--white);
    }

    .price-input:focus {
        border-color: var(--black);
    }

    .price-sep {
        font-family: var(--font-mono);
        font-size: 0.65rem;
        color: var(--grey);
    }

    .apply-filters-btn {
        width: 100%;
        background: var(--black);
        color: white;
        border: none;
        padding: 14px 16px;
        border-radius: 12px;
        font-family: var(--font-mono);
        font-size: 0.68rem;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        cursor: pointer;
        transition: background 0.2s, transform 0.18s, box-shadow 0.18s;
        margin-top: 6px;
        box-shadow: 0 10px 24px rgba(10, 10, 10, 0.12);
    }

    .apply-filters-btn:hover {
        background: var(--accent);
        transform: translateY(-1px);
    }

    /* ─── MAIN ─── */
    .mp-main {
        min-width: 0;
        padding: 4px 0 24px;
    }

    .mp-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 28px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .mp-results-info {
        font-family: var(--font-mono);
        font-size: 0.68rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--grey);
    }

    .mp-results-info strong {
        color: var(--black);
    }

    .sort-select {
        padding: 10px 14px;
        border: 1.5px solid var(--card-border);
        border-radius: 10px;
        font-family: var(--font-mono);
        font-size: 0.68rem;
        outline: none;
        color: var(--black);
        cursor: pointer;
        background: white;
        min-width: 180px;
    }

    /* ─── PRODUCT GRID ─── */
    .listings-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 24px;
    }

    .listing-card {
        background: var(--white);
        border: 1.5px solid var(--card-border);
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.28s;
        cursor: pointer;
        position: relative;
    }

    .listing-card:hover {
        border-color: var(--black);
        transform: translateY(-6px);
        box-shadow: 0 20px 48px rgba(0, 0, 0, 0.1);
    }

    .listing-badge {
        position: absolute;
        top: 14px;
        left: 14px;
        font-family: var(--font-mono);
        font-size: 0.58rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        padding: 4px 10px;
        border-radius: 4px;
        z-index: 2;
    }

    .badge-new {
        background: var(--accent);
        color: white;
    }

    .badge-hot {
        background: #333333;
        color: white;
    }

    .badge-sale {
        background: #f0a500;
        color: white;
    }

    .listing-img-wrap {
        aspect-ratio: 3/4;
        overflow: hidden;
        position: relative;
    }

    .listing-cover {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-end;
        padding: 16px 12px;
        font-family: var(--font-display);
        font-size: 0.75rem;
        font-weight: 700;
        color: white;
        text-align: center;
        line-height: 1.25;
        transition: transform 0.3s;
    }

    .listing-card:hover .listing-cover {
        transform: scale(1.04);
    }

    .listing-info {
        padding: 18px 18px 16px;
    }

    .listing-type {
        font-family: var(--font-mono);
        font-size: 0.58rem;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: var(--accent);
        margin-bottom: 5px;
    }

    .listing-name {
        font-family: var(--font-display);
        font-size: 0.88rem;
        font-weight: 700;
        margin-bottom: 3px;
        line-height: 1.3;
    }

    .listing-author {
        font-size: 0.78rem;
        color: var(--grey);
        margin-bottom: 10px;
    }

    .listing-rating {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 14px;
        font-family: var(--font-mono);
        font-size: 0.6rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--grey);
    }

    .listing-rating-value {
        color: var(--black);
        font-weight: 700;
    }

    .listing-rating-empty {
        color: var(--grey);
    }

    .listing-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .listing-price-wrap {}

    .listing-old-price {
        font-family: var(--font-mono);
        font-size: 0.65rem;
        color: var(--grey);
        text-decoration: line-through;
    }

    .listing-price {
        font-family: var(--font-display);
        font-size: 1.1rem;
        font-weight: 700;
    }

    .add-cart-btn {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--black);
        color: white;
        border: none;
        cursor: pointer;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        flex-shrink: 0;
    }

    .add-cart-btn:hover {
        background: var(--accent);
        transform: scale(1.12);
    }

    /* Sell your products section */
    .my-products-section {
        padding: 60px 80px;
        background: var(--off-white);
        border-top: 1.5px solid var(--light-grey);
    }

    .my-products-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 36px;
    }

    .my-products-title {
        font-family: var(--font-display);
        font-size: 1.4rem;
        font-weight: 700;
    }

    .btn-sell {
        background: var(--accent);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 6px;
        font-family: var(--font-mono);
        font-size: 0.72rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-sell:hover {
        background: var(--black);
        transform: translateY(-2px);
    }

    .my-products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 20px;
    }

    .my-product-card {
        background: white;
        border: 1.5px solid var(--card-border);
        border-radius: 12px;
        padding: 20px;
        text-align: center;
    }

    .my-product-card .mp-card-price {
        font-family: var(--font-display);
        font-size: 1rem;
        font-weight: 700;
    }

    /* ─── SELL MODAL ─── */
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

    .sell-modal {
        background: var(--white);
        border-radius: 16px;
        width: 100%;
        max-width: 560px;
        max-height: 90vh;
        overflow-y: auto;
        transform: translateY(24px) scale(0.97);
        transition: transform 0.3s;
    }

    .modal-overlay.open .sell-modal {
        transform: none;
    }

    .modal-header {
        padding: 32px 36px 24px;
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
        font-size: 1.4rem;
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

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-family: var(--font-mono);
        font-size: 0.62rem;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        color: var(--grey);
        margin-bottom: 8px;
    }

    .form-input,
    .form-textarea,
    .form-select {
        width: 100%;
        padding: 12px 16px;
        border: 1.5px solid var(--card-border);
        border-radius: 8px;
        font-family: var(--font-body);
        font-size: 0.9rem;
        color: var(--black);
        outline: none;
        transition: border-color 0.2s;
        background: var(--white);
    }

    .form-input:focus,
    .form-textarea:focus,
    .form-select:focus {
        border-color: var(--black);
    }

    .form-input[readonly] {
        background: var(--off-white);
        color: var(--grey);
    }

    .form-textarea {
        min-height: 100px;
        resize: vertical;
    }

    .form-helper {
        margin-top: 8px;
        font-family: var(--font-mono);
        font-size: 0.64rem;
        letter-spacing: 0.04em;
        color: var(--grey);
        line-height: 1.6;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .modal-submit {
        width: 100%;
        background: var(--black);
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
        background: var(--accent);
        box-shadow: 0 8px 24px var(--glow);
    }

    /* ─── FLOATING CART ─── */
    .floating-cart {
        position: fixed;
        bottom: 32px;
        right: 32px;
        z-index: 500;
    }

    .floating-cart-btn {
        background: var(--accent);
        color: white;
        border: none;
        border-radius: 50%;
        width: 58px;
        height: 58px;
        font-size: 0.62rem;
        font-family: var(--font-mono);
        letter-spacing: 0.12em;
        text-transform: uppercase;
        cursor: pointer;
        box-shadow: 0 8px 32px rgba(232, 0, 45, 0.4);
        transition: all 0.25s;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        position: relative;
    }

    .floating-cart-btn:hover {
        transform: scale(1.1);
    }

    .floating-count {
        position: absolute;
        top: -4px;
        right: -4px;
        background: var(--black);
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 0.6rem;
        font-family: var(--font-mono);
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid white;
    }

    .reveal {
        opacity: 0;
        transform: translateY(24px);
        transition: opacity 0.65s, transform 0.65s;
    }

    .reveal.visible {
        opacity: 1;
        transform: none;
    }

    /* ─── FOOTER (via footer.php) ─── */

    @media (max-width: 1100px) {
        .mp-layout {
            grid-template-columns: 1fr;
        }

        .mp-sidebar {
            position: static;
            max-height: none;
            border-right: none;
            border-bottom: 1.5px solid var(--light-grey);
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(0, 0.8fr);
            gap: 18px 20px;
            padding: 24px;
        }

        .mp-sidebar .sidebar-section {
            margin-bottom: 0;
        }

        .mp-sidebar .sidebar-section:nth-of-type(2) {
            grid-column: 1 / -1;
        }

        .apply-filters-btn {
            margin-top: 0;
            grid-column: 1 / -1;
            max-width: 240px;
        }
    }

    @media (max-width: 900px) {
        .mp-hero {
            min-height: auto;
            padding: 60px 24px;
        }

        .mp-hero-inner {
            grid-template-columns: 1fr;
        }

        .seller-banner {
            padding: 16px 24px;
            flex-wrap: wrap;
        }

        .mp-sidebar {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .mp-sidebar .sidebar-section:nth-of-type(2) {
            grid-column: auto;
        }

        .apply-filters-btn {
            max-width: none;
        }

        .mp-main {
            padding: 24px;
        }

        .my-products-section {
            padding: 40px 24px;
        }

        footer {
            padding: 32px 24px;
            flex-direction: column;
            gap: 12px;
        }
    }

    /* ─── PRODUCT DRAWER ─── */
    .drawer-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.55);
        backdrop-filter: blur(4px);
        z-index: 3000;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s;
    }

    .drawer-overlay.open {
        opacity: 1;
        pointer-events: all;
    }

    .product-drawer {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        width: 460px;
        max-width: 100vw;
        background: var(--white);
        z-index: 3001;
        transform: translateX(100%);
        transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        box-shadow: -8px 0 40px rgba(0, 0, 0, 0.18);
    }

    .drawer-overlay.open .product-drawer {
        transform: translateX(0);
    }

    .drawer-cover {
        width: 100%;
        aspect-ratio: 5/3;
        display: flex;
        align-items: flex-end;
        padding: 28px;
        position: relative;
        flex-shrink: 0;
        overflow: hidden;
    }

    .drawer-cover-media {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .drawer-cover-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center center;
        display: block;
    }

    .drawer-cover-media::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0, 0, 0, 0.38) 0%, rgba(0, 0, 0, 0.08) 55%, rgba(0, 0, 0, 0.02) 100%);
    }

    .drawer-close {
        position: absolute;
        top: 16px;
        right: 16px;
        background: rgba(0, 0, 0, 0.45);
        border: none;
        border-radius: 50%;
        width: 38px;
        height: 38px;
        color: white;
        font-size: 1rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.18s;
    }

    .drawer-close:hover {
        background: var(--accent);
    }

    .drawer-badge {
        display: none !important;
    }

    .drawer-body {
        padding: 28px 32px 40px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .drawer-type {
        font-family: var(--font-mono);
        font-size: 0.6rem;
        letter-spacing: 0.2em;
        text-transform: uppercase;
        color: var(--accent);
        margin-bottom: 6px;
    }

    .drawer-title {
        font-family: var(--font-display);
        font-size: 1.4rem;
        font-weight: 900;
        line-height: 1.18;
        margin-bottom: 4px;
    }

    .drawer-author {
        font-size: 0.88rem;
        color: var(--grey);
        margin-bottom: 12px;
    }

    .drawer-rating {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 18px;
        font-family: var(--font-mono);
        font-size: 0.62rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--grey);
    }

    .drawer-rating-value {
        color: var(--black);
        font-weight: 700;
    }

    .drawer-sep {
        border: none;
        border-top: 1.5px solid var(--light-grey);
        margin: 2px 0 16px;
    }

    .drawer-desc {
        font-size: 0.88rem;
        line-height: 1.75;
        color: var(--grey);
        margin-bottom: 20px;
    }

    .drawer-meta {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .drawer-meta-item {
        font-family: var(--font-mono);
        font-size: 0.6rem;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--grey);
        background: var(--off-white);
        padding: 5px 11px;
        border-radius: 4px;
    }

    .drawer-price-row {
        display: flex;
        align-items: baseline;
        gap: 12px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    .drawer-old-price {
        font-family: var(--font-mono);
        font-size: 0.95rem;
        color: var(--grey);
        text-decoration: line-through;
    }

    .drawer-price {
        font-family: var(--font-display);
        font-size: 2.1rem;
        font-weight: 900;
    }

    .drawer-stock {
        font-family: var(--font-mono);
        font-size: 0.6rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        padding: 4px 10px;
        border-radius: 20px;
        margin-left: auto;
        align-self: center;
    }

    .stock-ok {
        background: #e8faf0;
        color: #1a7a45;
    }

    .stock-low {
        background: #fff4e0;
        color: #c07a00;
    }

    .stock-out {
        background: #fdecea;
        color: #c0392b;
    }

    .drawer-actions {
        display: flex;
        gap: 12px;
        margin-bottom: 16px;
    }

    .drawer-manage-btn {
        flex: 1;
        border: 1.5px solid var(--card-border);
        background: var(--white);
        color: var(--black);
        padding: 13px 14px;
        border-radius: 10px;
        font-family: var(--font-mono);
        font-size: 0.68rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        cursor: pointer;
        transition: all 0.2s;
    }

    .drawer-manage-btn:hover {
        border-color: var(--black);
        background: var(--black);
        color: white;
    }

    .drawer-delete-btn {
        border-color: rgba(232, 0, 45, 0.24);
        color: var(--accent);
    }

    .drawer-delete-btn:hover {
        border-color: var(--accent);
        background: var(--accent);
        color: white;
    }

    .drawer-add-btn {
        background: var(--black);
        color: white;
        border: none;
        padding: 16px;
        border-radius: 10px;
        font-family: var(--font-mono);
        font-size: 0.78rem;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        cursor: pointer;
        transition: all 0.22s;
        width: 100%;
        margin-top: auto;
    }

    .drawer-add-btn:hover:not([disabled]) {
        background: var(--accent);
        box-shadow: 0 8px 24px var(--glow);
        transform: translateY(-2px);
    }

    .drawer-add-btn[disabled] {
        opacity: 0.4;
        cursor: not-allowed;
    }

    @media (max-width: 520px) {
        .product-drawer {
            width: 100vw;
        }
    }
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
                <div class="mp-hero-copy">
                    <div class="mp-eyebrow">Marketplace · P2P · 2026</div>
                    <h1 class="mp-title">Compra e vende<br><em>entre fãs.</em></h1>
                    <p class="mp-desc">O mercado peer-to-peer do MangaVerse. Encontra raridades, edições esgotadas e
                        coleções únicas.</p>
                </div>
                <div class="mp-hero-stats">
                    <div class="mp-stat"><span class="mp-stat-num" id="stat-produtos">0</span><span
                            class="mp-stat-label">Produtos</span></div>
                    <div class="mp-stat"><span class="mp-stat-num" id="site-rating-value"><?= number_format((float) $siteRating['media'], 1) ?>★</span><span
                        class="mp-stat-label">Avaliação</span><span class="mp-stat-sub" id="site-rating-total"><?= (int) $siteRating['total'] > 0 ? (int) $siteRating['total'] . ' avaliações' : 'Sem avaliações ainda' ?></span></div>
                </div>
            </div>
        </div>

        <!-- Sell banner -->
        <?php if ($canCreateMangas): ?>
        <div class="seller-banner">
            <span class="seller-banner-text">Tens mangás para vender? Publica o teu anúncio no marketplace.</span>
            <button class="seller-banner-cta" id="open-sell-modal">Vender agora →</button>
        </div>
        <?php endif; ?>

        <!-- ═══ MAIN LAYOUT ═══ -->
        <div class="mp-layout">

            <!-- Sidebar -->
            <aside class="mp-sidebar">
                <div class="mp-sidebar-head">
                    <div class="sidebar-kicker">// Filtros rápidos</div>
                    <h2 class="sidebar-title">Explora o marketplace</h2>
                    <p class="sidebar-desc">Pesquisa por título, escolhe a categoria e limita o preço para encontrares mais depressa o que queres.</p>
                </div>
                <div class="sidebar-section search-section">
                    <div class="sidebar-section-title">Pesquisa</div>
                    <div class="sidebar-search">
                        <input type="text" placeholder="Título, autor..." id="search-input">
                    </div>
                </div>
                <div class="sidebar-section categories-section">
                    <div class="sidebar-section-title">Categoria</div>
                    <div class="cat-list" id="cat-list"></div>
                </div>
                <div class="sidebar-section price-section">
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

        <!-- ═══ MY PRODUCTS (apenas para vendedores) ═══ -->
        <?php if ($canCreateMangas): ?>
        <section class="my-products-section">
            <div class="my-products-header">
                <div class="my-products-title">Os teus produtos no Marketplace</div>
                <button class="btn-sell" id="open-sell-modal-2">+ Novo Produto</button>
            </div>
            <div class="my-products-grid" id="my-products-grid">
                <p style="color:var(--grey); font-family:var(--font-mono); font-size:0.72rem;">Ainda não publicaste
                    nenhum produto.</p>
            </div>
        </section>
        <?php endif; ?>

    </div>


    <!-- ═══ SELL MODAL ═══ -->
    <?php if ($canCreateMangas): ?>
    <div class="modal-overlay" id="sell-modal-overlay">
        <div class="sell-modal">
            <div class="modal-header">
                <div>
                    <div class="modal-eyebrow">// Criar mangá</div>
                    <div class="modal-title">Novo mangá no Marketplace</div>
                </div>
                <button class="modal-close" id="close-modal">✕</button>
            </div>
            <div class="modal-body">
                <form id="sell-form" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="form-label">Título / Nome do produto</label>
                        <input type="text" class="form-input" id="sell-title" placeholder="Ex: Berserk Vol. 1-10"
                            required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tipo de publicação</label>
                        <input type="text" class="form-input" value="Mangá" readonly>
                        <div class="form-helper">A publicação está limitada a mangás para vendedores e administradores.
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Condição</label>
                            <select class="form-select" id="sell-condition" required>
                                <option value="novo">Novo</option>
                                <option value="usado">Usado</option>
                                <option value="raro">Raro</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Volume</label>
                            <input type="text" class="form-input" id="sell-volume" placeholder="Ex: Vol. 14">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Autor</label>
                            <input type="text" class="form-input" id="sell-author" placeholder="Autor do mangá">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Imagem da capa</label>
                            <input type="file" class="form-input" id="sell-image-file"
                                accept=".jpg,.jpeg,.png,.webp,.gif,image/*">
                            <div class="form-helper">Ao escolher uma imagem, o sistema faz o upload e guarda o caminho
                                na base de dados.</div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Descrição</label>
                        <textarea class="form-textarea" id="sell-desc" placeholder="Descreve o produto..."></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Preço (€)</label>
                            <input type="number" class="form-input" id="sell-price" placeholder="0.00" step="0.01"
                                min="0.01" required>
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
    <?php endif; ?>

    <?php require_once 'assets/includes/footer.php'; ?>

    <script>
    $(document).ready(function() {
        var currentFilters = {
            categoria: '',
            pesquisa: '',
            preco_min: '',
            preco_max: '',
            ordenar: 'recente'
        };
        var currentUser =
            <?= json_encode($user ? ['id' => (int) $user['id'], 'role' => $user['role']] : null, JSON_UNESCAPED_UNICODE) ?>;
        var canCreateMangas = <?= $canCreateMangas ? 'true' : 'false' ?>;
        var editingProductId = null;
        var editingProductData = null;

        function getListingRatingHtml(media, total) {
            var ratingTotal = parseInt(total || 0, 10);

            if (ratingTotal > 0 && media !== null && media !== undefined && media !== '') {
                return '<div class="listing-rating"><span class="listing-rating-value">' +
                    parseFloat(media).toFixed(1) + '★</span><span class="listing-rating-count">' +
                    ratingTotal + ' avaliação' + (ratingTotal !== 1 ? 'ões' : '') + '</span></div>';
            }

            return '<div class="listing-rating listing-rating-empty">Sem avaliações</div>';
        }

        function getDrawerRatingHtml(media, total) {
            var ratingTotal = parseInt(total || 0, 10);

            if (ratingTotal > 0 && media !== null && media !== undefined && media !== '') {
                return '<span class="drawer-rating-value">' + parseFloat(media).toFixed(1) +
                    '★</span><span>' + ratingTotal + ' avaliação' + (ratingTotal !== 1 ? 'ões' : '') +
                    ' no site</span>';
            }

            return '<span>Sem avaliações no site</span>';
        }

        // ── Load categories ──
        $.get('assets/controller/controllerMangas.php', {
            acao: 'categorias'
        }, function(res) {
            if (!res.success) return;
            var html =
                '<div class="cat-item active" data-cat=""><span class="cat-name">Todos</span><span class="cat-count">' +
                (res.contagem.reduce(function(s, c) {
                    return s + parseInt(c.total)
                }, 0)) + '</span></div>';
            res.contagem.forEach(function(c) {
                if (c.slug === 'livro') return;
                html += '<div class="cat-item" data-cat="' + c.slug +
                    '"><span class="cat-name">' + c.nome + '</span><span class="cat-count">' + c
                    .total + '</span></div>';
            });
            $('#cat-list').html(html);

        }, 'json');

        // ── Load products ──
        function loadProducts() {
            $.get('assets/controller/controllerMangas.php', $.extend({
                acao: 'listar'
            }, currentFilters), function(res) {
                if (!res.success) return;
                $('#results-num').text(res.total);
                $('#stat-produtos').text(res.total);
                var grid = $('#listings-grid');
                grid.empty();
                renderMyProducts(res.produtos || []);

                if (res.produtos.length === 0) {
                    grid.html(
                        '<p style="color:var(--grey);font-family:var(--font-mono);font-size:0.8rem;grid-column:1/-1;text-align:center;padding:60px 0;">Nenhum produto encontrado.</p>'
                        );
                    return;
                }

                res.produtos.forEach(function(p, idx) {
                    var badgeHtml = '';
                    if (p.badge) {
                        var badgeClass = p.badge === 'new' ? 'badge-new' : p.badge === 'hot' ?
                            'badge-hot' : 'badge-sale';
                        var badgeLabel = p.badge === 'new' ? 'Novo' : p.badge === 'hot' ?
                            'Popular' : 'Promoção';
                        badgeHtml = '<span class="listing-badge ' + badgeClass + '">' +
                            badgeLabel + '</span>';
                    }
                    var typeLabel = '// Mangá';
                    var oldPriceHtml = p.preco_antigo ? '<div class="listing-old-price">' +
                        parseFloat(p.preco_antigo).toFixed(2) + '€</div>' : '';
                    var ratingHtml = getListingRatingHtml(p.rating_media, p.rating_total);

                    var coverHtml = '';
                    if (p.imagem) {
                        coverHtml = '<img src="' + $('<span>').text(p.imagem).html() +
                            '" alt="' + $('<span>').text(p.nome).html() +
                            '" style="width:100%;height:100%;object-fit:cover;">';
                    } else {
                        coverHtml =
                            '<div class="listing-cover" style="background:linear-gradient(160deg,' +
                            (p.cor1 || '#0a0a0a') + ',' + (p.cor2 || '#e8002d') + ')">' + $(
                                '<span>').text(p.nome).html() +
                            '<br><span style="font-size:0.5rem;opacity:0.7">' + $('<span>')
                            .text(p.volume || '').html() + '</span></div>';
                    }

                    var card = $('<div class="listing-card reveal" data-id="' + p.id +
                        '" style="transition-delay:' + (idx * 50) + 'ms">' +
                        badgeHtml +
                        '<div class="listing-img-wrap">' + coverHtml + '</div>' +
                        '<div class="listing-info">' +
                        '<div class="listing-type">' + typeLabel + '</div>' +
                        '<div class="listing-name">' + $('<span>').text(p.nome).html() +
                        '</div>' +
                        '<div class="listing-author">' + $('<span>').text(p.autor).html() +
                        '</div>' +
                        ratingHtml +
                        '<div class="listing-bottom">' +
                        '<div class="listing-price-wrap">' + oldPriceHtml +
                        '<div class="listing-price">' + parseFloat(p.preco).toFixed(2) +
                        '€</div></div>' +
                        '<button class="add-cart-btn" data-id="' + p.id +
                        '" title="Adicionar ao carrinho">+</button>' +
                        '</div>' +
                        '</div>' +
                        '</div>');

                    grid.append(card);
                    setTimeout(function() {
                        card.addClass('visible');
                    }, 60 + idx * 50);
                });
            }, 'json');
        }

        function renderMyProducts(products) {
            if (!canCreateMangas || !$('#my-products-grid').length) return;

            var ownProducts = [];
            if (currentUser && currentUser.id) {
                ownProducts = (products || []).filter(function(p) {
                    return parseInt(p.vendedor_id || 0, 10) === parseInt(currentUser.id, 10);
                });
            }

            if (!ownProducts.length) {
                $('#my-products-grid').html(
                    '<p style="color:var(--grey); font-family:var(--font-mono); font-size:0.72rem;">Ainda não publicaste nenhum produto.</p>'
                    );
                return;
            }

            var html = ownProducts.map(function(p) {
                var coverHtml = p.imagem ?
                    '<img src="' + $('<span>').text(p.imagem).html() + '" alt="' + $('<span>').text(p
                        .nome).html() +
                    '" style="width:100%;height:180px;object-fit:cover;border-radius:10px;margin-bottom:14px;display:block;">' :
                    '<div style="height:180px;border-radius:10px;margin-bottom:14px;background:linear-gradient(160deg,' +
                    (p.cor1 || '#0a0a0a') + ',' + (p.cor2 || '#e8002d') + ');"></div>';

                return '<div class="my-product-card" data-id="' + p.id + '" style="cursor:pointer;">' +
                    coverHtml +
                    '<div style="font-family:var(--font-mono);font-size:0.6rem;letter-spacing:0.14em;text-transform:uppercase;color:var(--accent);margin-bottom:6px;">// Mangá</div>' +
                    '<div style="font-family:var(--font-display);font-size:0.88rem;font-weight:700;line-height:1.3;margin-bottom:6px;">' +
                    $('<span>').text(p.nome).html() + '</div>' +
                    '<div style="font-size:0.78rem;color:var(--grey);margin-bottom:12px;">' + $(
                        '<span>').text(p.autor || 'Desconhecido').html() + '</div>' +
                    '<div class="mp-card-price">' + parseFloat(p.preco).toFixed(2) + '€</div>' +
                    '</div>';
            }).join('');

            $('#my-products-grid').html(html);
        }

        function resetSellForm() {
            editingProductId = null;
            editingProductData = null;

            if (!$('#sell-form').length) return;

            $('#sell-form')[0].reset();
            $('#sell-condition').val('novo');
            $('#sell-stock').val('1');
            $('#sell-image-file').val('');
            $('.modal-eyebrow').text('// Criar mangá');
            $('.modal-title').text('Novo mangá no Marketplace');
            $('#sell-submit').prop('disabled', false).text('Publicar Anúncio →');
        }

        function openEditModal(product) {
            if (!product || !product.pode_editar || !$('#sell-form').length) return;

            editingProductId = parseInt(product.id, 10);
            editingProductData = product;

            $('#sell-title').val(product.nome || '');
            $('#sell-author').val(product.autor || '');
            $('#sell-volume').val(product.volume || '');
            $('#sell-desc').val(product.descricao || '');
            $('#sell-price').val(product.preco || '');
            $('#sell-stock').val(product.stock || 0);
            $('#sell-condition').val(product.condicao || 'novo');
            $('#sell-image-file').val('');

            $('.modal-eyebrow').text('// Editar mangá');
            $('.modal-title').text('Editar mangá do Marketplace');
            $('#sell-submit').text('Guardar Alterações →');
            $('#sell-modal-overlay').addClass('open');
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
                data: {
                    acao: 'adicionar',
                    produto_id: produtoId
                },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        updateCartCount(res.total_itens);
                        Swal.fire({
                            toast: true,
                            position: 'bottom-end',
                            icon: 'success',
                            title: '<span style="font-family:Orbitron;font-size:0.78rem">Adicionado!</span>',
                            text: 'Produto adicionado ao carrinho.',
                            showConfirmButton: false,
                            timer: 2200,
                            timerProgressBar: true,
                            background: '#0a0a0a',
                            color: '#fff',
                            iconColor: '#e8002d'
                        });
                        btn.css('background', '#e8002d').text('✓');
                        setTimeout(function() {
                            btn.css('background', '').text('+');
                        }, 1200);
                    } else if (res.redirect) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Login necessário',
                            text: 'Precisas de fazer login para adicionar ao carrinho.',
                            confirmButtonColor: '#0a0a0a',
                            confirmButtonText: 'Ir para Login'
                        }).then(function() {
                            window.location.href = res.redirect;
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: res.message,
                            confirmButtonColor: '#e8002d'
                        });
                    }
                },
                error: function(xhr) {
                    var data = xhr.responseJSON || {};
                    if (data.redirect) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Login necessário',
                            text: 'Precisas de fazer login para adicionar ao carrinho.',
                            confirmButtonColor: '#0a0a0a',
                            confirmButtonText: 'Ir para Login'
                        }).then(function() {
                            window.location.href = data.redirect;
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: data.message ||
                                'Não foi possível adicionar ao carrinho.',
                            confirmButtonColor: '#e8002d'
                        });
                    }
                }
            });
        });

        // ── Sell Modal ──
        function openModal() {
            <?php if (!$user): ?>
            Swal.fire({
                icon: 'info',
                title: 'Login necessário',
                text: 'Precisas de fazer login para vender produtos.',
                confirmButtonColor: '#0a0a0a',
                confirmButtonText: 'Ir para Login'
            }).then(function() {
                window.location.href = 'login.php';
            });
            return;
            <?php elseif (!$canCreateMangas): ?>
            Swal.fire({
                icon: 'info',
                title: 'Acesso restrito',
                text: 'Apenas vendedores e administradores podem criar mangás.',
                confirmButtonColor: '#0a0a0a'
            });
            return;
            <?php endif; ?>
            resetSellForm();
            $('#sell-modal-overlay').addClass('open');
        }

        $('#open-sell-modal, #open-sell-modal-2').on('click', openModal);
        $('#close-modal').on('click', function() {
            $('#sell-modal-overlay').removeClass('open');
        });
        $('#sell-modal-overlay').on('click', function(e) {
            if (e.target === this) $(this).removeClass('open');
        });

        $('#sell-form').on('submit', function(e) {
            e.preventDefault();
            var isEditing = editingProductId !== null;
            $('#sell-submit').prop('disabled', true).text(isEditing ? 'A guardar...' : 'A publicar...');

            var formData = new window.FormData();
            formData.append('acao', isEditing ? 'atualizar' : 'criar');
            if (isEditing) {
                formData.append('id', editingProductId);
            }
            formData.append('nome', $('#sell-title').val().trim());
            formData.append('autor', $('#sell-author').val().trim());
            formData.append('descricao', $('#sell-desc').val().trim());
            formData.append('preco', $('#sell-price').val());
            formData.append('stock', $('#sell-stock').val());
            formData.append('volume', $('#sell-volume').val().trim());
            formData.append('condicao', $('#sell-condition').val());

            var imageFile = $('#sell-image-file')[0].files[0];
            if (imageFile) {
                formData.append('imagem_file', imageFile);
            } else if (editingProductData && editingProductData.imagem) {
                formData.append('imagem', editingProductData.imagem);
            }

            $.ajax({
                url: 'assets/controller/controllerMangas.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: isEditing ? 'Atualizado!' : 'Publicado!',
                            text: isEditing ?
                                'As alterações ao produto foram guardadas.' :
                                'O teu produto já está no marketplace.',
                            confirmButtonColor: '#0a0a0a'
                        });
                        $('#sell-modal-overlay').removeClass('open');
                        resetSellForm();
                        loadProducts();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: res.message,
                            confirmButtonColor: '#e8002d'
                        });
                    }
                    $('#sell-submit').prop('disabled', false).text(isEditing ?
                        'Guardar Alterações →' : 'Publicar Anúncio →');
                }
            }).always(function() {
                $('#sell-submit').prop('disabled', false).text(editingProductId !== null ?
                    'Guardar Alterações →' : 'Publicar Anúncio →');
            });
        });

        // ── Cart count (barra flutuante) ──
        function updateCartCount(count) {
            $('#nav-cart-count, #float-cart-count').text(count);
        }

        // ── Scroll reveal ──
        var obs = new window.IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) entry.target.classList.add('visible');
            });
        }, {
            threshold: 0.1
        });
        document.querySelectorAll('.reveal').forEach(function(el) {
            obs.observe(el);
        });

        // ── Product Drawer ──
        var _drawerId = null;
        var _drawerProduct = null;

        function openDrawer(id) {
            _drawerId = id;
            $.getJSON('produto.php', {
                id: id
            }, function(res) {
                if (!res.success) return;
                var p = res.produto;
                _drawerProduct = p;

                $('#drawer-cover').css('background', 'linear-gradient(160deg,' + p.cor1 + ',' + p.cor2 +
                    ')');
                $('#drawer-cover-media').html(
                    p.imagem ?
                    '<img src="' + $('<span>').text(p.imagem).html() + '" alt="' + $('<span>').text(
                        p.nome).html() + '">' :
                    ''
                );

                $('#drawer-badge').hide();

                var typeLabel = '// Mangá';
                $('#drawer-type').text(typeLabel);
                $('#drawer-title').text(p.nome + (p.volume ? ' — ' + p.volume : ''));
                $('#drawer-author').text('por ' + p.autor);
                $('#drawer-rating').html(getDrawerRatingHtml(p.rating_media, p.rating_total));
                $('#drawer-desc').text(p.descricao || 'Sem descrição disponível.');

                var meta = '';
                var condLabel = p.condicao === 'novo' ? 'Novo' : 'Usado (' + p.condicao_pct + '%)';
                meta += '<span class="drawer-meta-item">Estado: ' + condLabel + '</span>';
                if (p.vendedor_nome) meta += '<span class="drawer-meta-item">Vendedor: ' + $('<span>')
                    .text(p.vendedor_nome).html() + '</span>';
                $('#drawer-meta').html(meta);

                if (p.preco_antigo) {
                    $('#drawer-old-price').text(parseFloat(p.preco_antigo).toFixed(2) + '€').show();
                } else {
                    $('#drawer-old-price').hide();
                }
                $('#drawer-price').text(parseFloat(p.preco).toFixed(2) + '€');

                var stockClass = p.stock > 5 ? 'stock-ok' : p.stock > 0 ? 'stock-low' : 'stock-out';
                var stockLabel = p.stock > 5 ? 'Em Stock' : p.stock > 0 ? p.stock + ' restantes' :
                    'Esgotado';
                $('#drawer-stock').attr('class', 'drawer-stock ' + stockClass).text(stockLabel);
                $('#drawer-actions').toggle(!!(p.pode_editar || p.pode_eliminar));
                $('#drawer-edit-btn').toggle(!!p.pode_editar);
                $('#drawer-delete-btn').toggle(!!p.pode_eliminar);
                $('#drawer-add-btn').prop('disabled', p.stock <= 0).text(p.stock > 0 ?
                    'Adicionar ao Carrinho' : 'Esgotado');

                $('#drawer-overlay').addClass('open');
            });
        }

        $('#drawer-overlay').on('click', function(e) {
            if (e.target === this) $(this).removeClass('open');
        });
        $('#drawer-close').on('click', function() {
            $('#drawer-overlay').removeClass('open');
        });
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') $('#drawer-overlay').removeClass('open');
        });

        $(document).on('click', '.listing-card', function(e) {
            if ($(e.target).closest('.add-cart-btn').length) return;
            var id = $(this).data('id') || $(this).find('.add-cart-btn').data('id');
            if (id) openDrawer(id);
        });

        $(document).on('click', '.my-product-card', function() {
            var id = $(this).data('id');
            if (id) openDrawer(id);
        });

        $('#drawer-edit-btn').on('click', function() {
            if (!_drawerProduct || !_drawerProduct.pode_editar) return;
            $('#drawer-overlay').removeClass('open');
            openEditModal(_drawerProduct);
        });

        $('#drawer-delete-btn').on('click', function() {
            if (!_drawerProduct || !_drawerProduct.pode_eliminar) return;

            Swal.fire({
                icon: 'warning',
                title: 'Eliminar produto?',
                text: 'Esta ação remove o produto do marketplace.',
                showCancelButton: true,
                confirmButtonText: 'Eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#e8002d',
                cancelButtonColor: '#0a0a0a'
            }).then(function(result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: 'assets/controller/controllerMangas.php',
                    method: 'POST',
                    data: {
                        acao: 'eliminar',
                        id: _drawerProduct.id
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            $('#drawer-overlay').removeClass('open');
                            Swal.fire({
                                icon: 'success',
                                title: 'Eliminado!',
                                text: 'O produto foi removido do marketplace.',
                                confirmButtonColor: '#0a0a0a'
                            });
                            loadProducts();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: res.message,
                                confirmButtonColor: '#e8002d'
                            });
                        }
                    },
                    error: function(xhr) {
                        var data = xhr.responseJSON || {};
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: data.message ||
                                'Não foi possível eliminar o produto.',
                            confirmButtonColor: '#e8002d'
                        });
                    }
                });
            });
        });

        $('#drawer-add-btn').on('click', function() {
            if (!_drawerId) return;
            var btn = $(this);
            $.ajax({
                url: 'assets/controller/controllerCarrinho.php',
                method: 'POST',
                data: {
                    acao: 'adicionar',
                    produto_id: _drawerId
                },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        updateCartCount(res.total_itens);
                        btn.text('✓ Adicionado!');
                        setTimeout(function() {
                            btn.text('Adicionar ao Carrinho');
                        }, 1800);
                        Swal.fire({
                            toast: true,
                            position: 'bottom-end',
                            icon: 'success',
                            title: '<span style="font-family:Orbitron;font-size:0.78rem">Adicionado!</span>',
                            showConfirmButton: false,
                            timer: 2200,
                            timerProgressBar: true,
                            background: '#0a0a0a',
                            color: '#fff',
                            iconColor: '#e8002d'
                        });
                    } else if (res.redirect) {
                        $('#drawer-overlay').removeClass('open');
                        Swal.fire({
                            icon: 'info',
                            title: 'Login necessário',
                            text: 'Precisas de fazer login para adicionar ao carrinho.',
                            confirmButtonColor: '#0a0a0a',
                            confirmButtonText: 'Ir para Login'
                        }).then(function() {
                            window.location.href = res.redirect;
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: res.message,
                            confirmButtonColor: '#e8002d'
                        });
                    }
                },
                error: function(xhr) {
                    var data = xhr.responseJSON || {};
                    if (data.redirect) {
                        $('#drawer-overlay').removeClass('open');
                        Swal.fire({
                            icon: 'info',
                            title: 'Login necessário',
                            confirmButtonColor: '#0a0a0a',
                            confirmButtonText: 'Ir para Login'
                        }).then(function() {
                            window.location.href = data.redirect;
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: data.message || 'Erro ao adicionar ao carrinho.',
                            confirmButtonColor: '#e8002d'
                        });
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
                <div class="drawer-cover-media" id="drawer-cover-media"></div>
                <button class="drawer-close" id="drawer-close" title="Fechar">✕</button>
                <span class="drawer-badge" id="drawer-badge" style="display:none"></span>
            </div>
            <div class="drawer-body">
                <div class="drawer-type" id="drawer-type"></div>
                <div class="drawer-title" id="drawer-title"></div>
                <div class="drawer-author" id="drawer-author"></div>
                <div class="drawer-rating" id="drawer-rating"></div>
                <hr class="drawer-sep">
                <p class="drawer-desc" id="drawer-desc"></p>
                <div class="drawer-meta" id="drawer-meta"></div>
                <div class="drawer-price-row">
                    <span class="drawer-old-price" id="drawer-old-price" style="display:none"></span>
                    <span class="drawer-price" id="drawer-price"></span>
                    <span class="drawer-stock" id="drawer-stock"></span>
                </div>
                <div class="drawer-actions" id="drawer-actions" style="display:none;">
                    <button type="button" class="drawer-manage-btn" id="drawer-edit-btn"
                        style="display:none;">Editar</button>
                    <button type="button" class="drawer-manage-btn drawer-delete-btn" id="drawer-delete-btn"
                        style="display:none;">Eliminar</button>
                </div>
                <button class="drawer-add-btn" id="drawer-add-btn">Adicionar ao Carrinho</button>
            </div>
        </div>
    </div>
</body>

</html>