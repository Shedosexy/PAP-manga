<?php
require_once 'assets/config/database.php';
initSession();
$user        = getLoggedUser();
$currentPage = 'loja';
$basePath    = '';
?>
<!DOCTYPE html>
<html lang="pt">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MangaVerse — Loja de Mangás & Livros</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Noto+Sans+JP:wght@300;400;700&family=Space+Mono:wght@400;700&display=swap"
    rel="stylesheet">
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

    .nav-logo {
      font-family: var(--font-display);
      font-size: 1.35rem;
      font-weight: 900;
      letter-spacing: 0.08em;
      color: var(--black);
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .nav-logo span {
      color: var(--accent);
    }

    .nav-logo .logo-dot {
      width: 8px;
      height: 8px;
      background: var(--accent);
      border-radius: 50%;
      animation: pulse 1.8s infinite;
    }

    @keyframes pulse {

      0%,
      100% {
        opacity: 1;
        transform: scale(1);
      }

      50% {
        opacity: 0.4;
        transform: scale(1.5);
      }
    }

    .nav-links {
      display: flex;
      align-items: center;
      gap: 36px;
      list-style: none;
    }

    .nav-links a {
      font-family: var(--font-mono);
      font-size: 0.75rem;
      letter-spacing: 0.15em;
      text-transform: uppercase;
      color: var(--grey);
      text-decoration: none;
      transition: color 0.2s;
    }

    .nav-links a:hover {
      color: var(--black);
    }

    .cart-btn {
      display: flex;
      align-items: center;
      gap: 10px;
      background: var(--black);
      color: var(--white) !important;
      padding: 10px 20px;
      border-radius: 4px;
      font-family: var(--font-mono) !important;
      font-size: 0.72rem !important;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      text-decoration: none;
      transition: background 0.2s, transform 0.15s;
      position: relative;
    }

    .cart-btn:hover {
      background: var(--accent) !important;
      transform: translateY(-1px);
    }

    .cart-count {
      background: var(--accent);
      color: #fff;
      border-radius: 50%;
      width: 18px;
      height: 18px;
      font-size: 0.65rem;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: var(--font-mono);
      font-weight: 700;
    }

    .hero {
      min-height: 100vh;
      padding: 72px 0 0;
      display: grid;
      grid-template-columns: 1fr 1fr;
      align-items: center;
      position: relative;
      overflow: hidden;
    }

    .hero::before {
      content: '';
      position: absolute;
      top: -120px;
      right: -180px;
      width: 700px;
      height: 700px;
      background: radial-gradient(circle, rgba(232, 0, 45, 0.08) 0%, transparent 70%);
      pointer-events: none;
    }

    .hero-grid-lines {
      position: absolute;
      inset: 0;
      background-image:
        linear-gradient(rgba(0, 0, 0, 0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0, 0, 0, 0.04) 1px, transparent 1px);
      background-size: 60px 60px;
      pointer-events: none;
      opacity: 0.6;
    }

    .hero-content {
      padding: 80px 48px 80px 80px;
      position: relative;
      z-index: 2;
    }

    .hero-eyebrow {
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

    .hero-eyebrow::before {
      content: '';
      width: 32px;
      height: 1.5px;
      background: var(--accent);
    }

    .hero-title {
      font-family: var(--font-display);
      font-size: clamp(2.4rem, 5vw, 4.2rem);
      font-weight: 900;
      line-height: 1.05;
      letter-spacing: -0.02em;
      margin-bottom: 28px;
    }

    .hero-title em {
      font-style: normal;
      color: var(--accent);
      position: relative;
    }

    .hero-desc {
      font-size: 1.05rem;
      line-height: 1.75;
      color: var(--grey);
      max-width: 480px;
      margin-bottom: 44px;
    }

    .hero-actions {
      display: flex;
      gap: 16px;
      flex-wrap: wrap;
    }

    .btn-primary {
      background: var(--black);
      color: var(--white);
      padding: 14px 32px;
      border: none;
      cursor: pointer;
      font-family: var(--font-mono);
      font-size: 0.75rem;
      letter-spacing: 0.15em;
      text-transform: uppercase;
      border-radius: 4px;
      transition: all 0.22s;
      text-decoration: none;
      display: inline-block;
    }

    .btn-primary:hover {
      background: var(--accent);
      transform: translateY(-2px);
      box-shadow: 0 8px 24px var(--glow);
    }

    .btn-outline {
      background: transparent;
      color: var(--black);
      padding: 14px 32px;
      border: 1.5px solid var(--black);
      cursor: pointer;
      font-family: var(--font-mono);
      font-size: 0.75rem;
      letter-spacing: 0.15em;
      text-transform: uppercase;
      border-radius: 4px;
      transition: all 0.22s;
      text-decoration: none;
      display: inline-block;
    }

    .btn-outline:hover {
      border-color: var(--accent);
      color: var(--accent);
      transform: translateY(-2px);
    }

    .hero-visual {
      height: 100%;
      min-height: 600px;
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 80px 40px;
    }

    .hero-manga-stack {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 16px;
      max-width: 420px;
      width: 100%;
    }

    .hero-manga-card {
      aspect-ratio: 3/4;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
      transition: transform 0.3s;
      position: relative;
    }

    .hero-manga-card:nth-child(2) {
      transform: translateY(-24px);
    }

    .hero-manga-card:nth-child(5) {
      transform: translateY(-16px);
    }

    .hero-manga-card:hover {
      transform: translateY(-8px) scale(1.03);
      z-index: 2;
    }

    .hero-manga-card:nth-child(2):hover {
      transform: translateY(-32px) scale(1.03);
    }

    .manga-cover {
      width: 100%;
      height: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-end;
      padding: 12px 8px;
      font-family: var(--font-display);
      font-size: 0.5rem;
      font-weight: 700;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      color: white;
      text-shadow: 0 1px 4px rgba(0, 0, 0, 0.8);
    }

    .stats-bar {
      display: flex;
      gap: 48px;
      margin-top: 56px;
      padding-top: 40px;
      border-top: 1px solid var(--light-grey);
    }

    .stat-item {}

    .stat-num {
      font-family: var(--font-display);
      font-size: 2rem;
      font-weight: 900;
      color: var(--black);
      display: block;
    }

    .stat-label {
      font-family: var(--font-mono);
      font-size: 0.65rem;
      letter-spacing: 0.18em;
      text-transform: uppercase;
      color: var(--grey);
    }

    /* ─── SECTION HEADER ─── */
    .section-header {
      text-align: center;
      margin-bottom: 64px;
    }

    .section-eyebrow {
      font-family: var(--font-mono);
      font-size: 0.68rem;
      letter-spacing: 0.22em;
      text-transform: uppercase;
      color: var(--accent);
      margin-bottom: 16px;
      display: block;
    }

    .section-title {
      font-family: var(--font-display);
      font-size: clamp(1.8rem, 3.5vw, 2.8rem);
      font-weight: 900;
      letter-spacing: -0.01em;
      line-height: 1.1;
    }

    /* ─── MANGA GRID ─── */
    #destaques {
      padding: 100px 80px;
      background: var(--white);
    }

    .filter-tabs {
      display: flex;
      gap: 8px;
      justify-content: center;
      margin-bottom: 56px;
      flex-wrap: wrap;
    }

    .filter-tab {
      font-family: var(--font-mono);
      font-size: 0.68rem;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      padding: 8px 18px;
      border: 1.5px solid var(--light-grey);
      background: transparent;
      border-radius: 100px;
      cursor: pointer;
      transition: all 0.2s;
      color: var(--grey);
    }

    .filter-tab.active,
    .filter-tab:hover {
      border-color: var(--black);
      color: var(--black);
      background: var(--black);
      color: var(--white);
    }

    .product-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
      gap: 28px;
    }

    .product-card {
      background: var(--white);
      border: 1.5px solid var(--card-border);
      border-radius: 12px;
      overflow: hidden;
      transition: all 0.28s;
      cursor: pointer;
      position: relative;
    }

    .product-card:hover {
      border-color: var(--black);
      transform: translateY(-6px);
      box-shadow: 0 20px 48px rgba(0, 0, 0, 0.1);
    }

    .product-badge {
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
      background: var(--black);
      color: white;
    }

    .badge-sale {
      background: #f0a500;
      color: white;
    }

    .product-img-wrap {
      aspect-ratio: 3/4;
      overflow: hidden;
      position: relative;
    }

    .product-img-wrap::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(to top, rgba(0, 0, 0, 0.3) 0%, transparent 60%);
      opacity: 0;
      transition: opacity 0.3s;
    }

    .product-card:hover .product-img-wrap::after {
      opacity: 1;
    }

    .product-cover {
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: var(--font-display);
      font-weight: 900;
      font-size: 1.2rem;
      letter-spacing: 0.05em;
      color: white;
      text-align: center;
      padding: 16px;
      line-height: 1.2;
      transition: transform 0.3s;
    }

    .product-card:hover .product-cover {
      transform: scale(1.04);
    }

    .product-info {
      padding: 20px;
    }

    .product-type {
      font-family: var(--font-mono);
      font-size: 0.6rem;
      letter-spacing: 0.18em;
      text-transform: uppercase;
      color: var(--accent);
      margin-bottom: 6px;
    }

    .product-name {
      font-family: var(--font-display);
      font-size: 0.95rem;
      font-weight: 700;
      letter-spacing: 0.02em;
      margin-bottom: 4px;
      line-height: 1.3;
    }

    .product-author {
      font-size: 0.8rem;
      color: var(--grey);
      margin-bottom: 16px;
    }

    .product-bottom {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .product-price {
      font-family: var(--font-display);
      font-size: 1.1rem;
      font-weight: 700;
    }

    .product-price .old-price {
      font-size: 0.75rem;
      color: var(--grey);
      text-decoration: line-through;
      font-weight: 400;
      margin-right: 6px;
    }

    .add-cart-btn {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      background: var(--black);
      color: white;
      border: none;
      cursor: pointer;
      font-size: 1.2rem;
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

    /* ─── BANNER ─── */
    .banner-section {
      margin: 0 80px;
      border-radius: 16px;
      background: var(--black);
      color: white;
      padding: 80px;
      display: grid;
      grid-template-columns: 1fr auto;
      align-items: center;
      gap: 40px;
      position: relative;
      overflow: hidden;
    }

    .banner-section::before {
      content: '漫画';
      position: absolute;
      right: 80px;
      top: 50%;
      transform: translateY(-50%);
      font-family: var(--font-display);
      font-size: 14rem;
      font-weight: 900;
      color: rgba(255, 255, 255, 0.03);
      pointer-events: none;
      letter-spacing: -0.05em;
    }

    .banner-eyebrow {
      font-family: var(--font-mono);
      font-size: 0.68rem;
      letter-spacing: 0.22em;
      text-transform: uppercase;
      color: var(--accent);
      margin-bottom: 16px;
    }

    .banner-title {
      font-family: var(--font-display);
      font-size: clamp(1.8rem, 3vw, 3rem);
      font-weight: 900;
      line-height: 1.1;
      margin-bottom: 20px;
    }

    .banner-desc {
      color: rgba(255, 255, 255, 0.6);
      max-width: 500px;
      line-height: 1.7;
    }

    /* ─── CONTACTO ─── */
    #contacto {
      padding: 100px 80px;
      background: var(--off-white);
    }

    .contact-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 80px;
      align-items: start;
    }

    .contact-info h3 {
      font-family: var(--font-display);
      font-size: 1.4rem;
      font-weight: 700;
      margin-bottom: 16px;
    }

    .contact-info p {
      color: var(--grey);
      line-height: 1.8;
      margin-bottom: 40px;
    }

    .contact-detail {
      display: flex;
      align-items: center;
      gap: 14px;
      margin-bottom: 20px;
    }

    .contact-icon {
      width: 44px;
      height: 44px;
      background: var(--white);
      border: 1.5px solid var(--card-border);
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.1rem;
      flex-shrink: 0;
    }

    .contact-label {
      font-family: var(--font-mono);
      font-size: 0.65rem;
      letter-spacing: 0.15em;
      text-transform: uppercase;
      color: var(--grey);
    }

    .contact-value {
      font-weight: 600;
      font-size: 0.9rem;
    }

    .contact-form {}

    .form-group {
      margin-bottom: 20px;
    }

    .form-label {
      display: block;
      font-family: var(--font-mono);
      font-size: 0.65rem;
      letter-spacing: 0.15em;
      text-transform: uppercase;
      color: var(--grey);
      margin-bottom: 8px;
    }

    .form-input,
    .form-textarea {
      width: 100%;
      padding: 14px 18px;
      border: 1.5px solid var(--card-border);
      border-radius: 8px;
      background: var(--white);
      font-family: var(--font-body);
      font-size: 0.92rem;
      color: var(--black);
      transition: border-color 0.2s;
      outline: none;
    }

    .form-input:focus,
    .form-textarea:focus {
      border-color: var(--black);
    }

    .form-textarea {
      min-height: 130px;
      resize: vertical;
    }

    /* ─── FOOTER ─── */
    footer {
      background: var(--black);
      color: white;
      padding: 80px 80px 40px;
    }

    .footer-grid {
      display: grid;
      grid-template-columns: 2fr 1fr 1fr 1fr;
      gap: 60px;
      margin-bottom: 64px;
    }

    .footer-brand .logo {
      font-family: var(--font-display);
      font-size: 1.2rem;
      font-weight: 900;
      letter-spacing: 0.08em;
      margin-bottom: 16px;
      display: block;
    }

    .footer-brand .logo span {
      color: var(--accent);
    }

    .footer-brand p {
      color: rgba(255, 255, 255, 0.45);
      font-size: 0.88rem;
      line-height: 1.75;
      max-width: 280px;
    }

    .footer-col h4 {
      font-family: var(--font-mono);
      font-size: 0.65rem;
      letter-spacing: 0.22em;
      text-transform: uppercase;
      color: rgba(255, 255, 255, 0.35);
      margin-bottom: 20px;
    }

    .footer-col ul {
      list-style: none;
    }

    .footer-col ul li {
      margin-bottom: 12px;
    }

    .footer-col ul a {
      color: rgba(255, 255, 255, 0.6);
      text-decoration: none;
      font-size: 0.88rem;
      transition: color 0.2s;
    }

    .footer-col ul a:hover {
      color: white;
    }

    .footer-bottom {
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      padding-top: 32px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .footer-bottom p {
      font-family: var(--font-mono);
      font-size: 0.65rem;
      letter-spacing: 0.1em;
      color: rgba(255, 255, 255, 0.3);
    }

    .footer-social {
      display: flex;
      gap: 14px;
    }

    .social-link {
      width: 36px;
      height: 36px;
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: rgba(255, 255, 255, 0.5);
      text-decoration: none;
      font-size: 0.85rem;
      transition: all 0.2s;
    }

    .social-link:hover {
      border-color: var(--accent);
      color: var(--accent);
    }

    /* ─── ANIME FLOATING CTA ─── */
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
      font-size: 1.5rem;
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
      box-shadow: 0 12px 40px rgba(232, 0, 45, 0.55);
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

    @media (max-width: 900px) {
      nav {
        padding: 0 24px;
      }

      .hero {
        grid-template-columns: 1fr;
      }

      .hero-visual {
        display: none;
      }

      #destaques,
      #contacto,
      .banner-section {
        padding: 60px 24px;
      }

      .banner-section {
        margin: 0 24px;
      }

      .contact-grid {
        grid-template-columns: 1fr;
      }

      footer {
        padding: 60px 24px 32px;
      }

      .footer-grid {
        grid-template-columns: 1fr 1fr;
        gap: 32px;
      }
    }

    /* ─── DARK MODE TOGGLE ─── */
    .dark-mode-toggle {
      background: none;
      border: 1.5px solid var(--light-grey);
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
      border-color: var(--black);
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

    body.dark-mode .cart-btn {
      background: #f0f0f0;
      color: #0e0e0e !important;
    }

    body.dark-mode .cart-btn:hover {
      background: #e8002d !important;
      color: #fff !important;
    }

    body.dark-mode .dark-mode-toggle {
      border-color: #444;
    }

    body.dark-mode .dark-mode-toggle:hover {
      border-color: #f0f0f0;
    }

    /* Hero — keep dark */
    body.dark-mode .hero {
      background: #111 !important;
    }

    body.dark-mode .hero * {
      color: inherit;
    }

    body.dark-mode .hero-eyebrow {
      color: #e8002d !important;
    }

    body.dark-mode .hero-desc {
      color: rgba(255, 255, 255, 0.55) !important;
    }

    body.dark-mode .stat-label {
      color: rgba(255, 255, 255, 0.4) !important;
    }

    body.dark-mode .stat-num {
      color: #fff !important;
    }

    /* Products section */
    body.dark-mode #destaques {
      background: #0e0e0e;
    }

    body.dark-mode .section-eyebrow {
      color: #e8002d !important;
    }

    body.dark-mode .section-title {
      color: #f0f0f0 !important;
    }

    body.dark-mode .filter-tab {
      border-color: #333;
      color: #999;
    }

    body.dark-mode .filter-tab.active,
    body.dark-mode .filter-tab:hover {
      background: #f0f0f0;
      color: #0e0e0e;
      border-color: #f0f0f0;
    }

    body.dark-mode .product-card {
      background: #181818 !important;
      border-color: #333 !important;
    }

    body.dark-mode .product-card:hover {
      border-color: #e8002d !important;
      box-shadow: 0 20px 48px rgba(232, 0, 45, 0.15) !important;
    }

    body.dark-mode .product-name {
      color: #f0f0f0 !important;
    }

    body.dark-mode .product-author {
      color: #888 !important;
    }

    body.dark-mode .product-type {
      color: #e8002d !important;
    }

    body.dark-mode .product-price {
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

    /* Banner */
    body.dark-mode .banner-section {
      background: #141414 !important;
      border-color: #333 !important;
    }

    body.dark-mode .banner-title {
      color: #f0f0f0 !important;
    }

    body.dark-mode .banner-eyebrow {
      color: #e8002d !important;
    }

    body.dark-mode .banner-desc {
      color: #999 !important;
    }

    /* Contact */
    body.dark-mode #contacto {
      background: #0e0e0e;
    }

    body.dark-mode .contact-info h3 {
      color: #f0f0f0 !important;
    }

    body.dark-mode .contact-info p {
      color: #999 !important;
    }

    body.dark-mode .contact-value {
      color: #ccc !important;
    }

    body.dark-mode .contact-label {
      color: #888 !important;
    }

    body.dark-mode .contact-form {
      background: #181818 !important;
      border-color: #333 !important;
    }

    body.dark-mode .form-input,
    body.dark-mode .form-textarea {
      background: #1a1a1a !important;
      color: #f0f0f0 !important;
      border-color: #333 !important;
    }

    body.dark-mode .form-input:focus,
    body.dark-mode .form-textarea:focus {
      border-color: #e8002d !important;
    }

    body.dark-mode .form-label {
      color: #999 !important;
    }

    body.dark-mode input::placeholder,
    body.dark-mode textarea::placeholder {
      color: #666 !important;
    }

    /* Footer */
    body.dark-mode footer {
      background: #0a0a0a !important;
    }

    body.dark-mode .footer-brand .logo {
      color: #fff;
    }

    body.dark-mode .footer-col h4 {
      color: rgba(255, 255, 255, 0.8) !important;
    }

    body.dark-mode .footer-col ul a {
      color: rgba(255, 255, 255, 0.4) !important;
    }

    body.dark-mode .footer-col ul a:hover {
      color: #e8002d !important;
    }

    body.dark-mode .footer-bottom p {
      color: rgba(255, 255, 255, 0.3) !important;
    }

    body.dark-mode .footer-social .social-link {
      border-color: rgba(255, 255, 255, 0.15);
      color: rgba(255, 255, 255, 0.5);
    }

    body.dark-mode .footer-social .social-link:hover {
      border-color: #e8002d;
      color: #e8002d;
    }

    /* Contact section icons & details */
    body.dark-mode .contact-icon {
      background: #1a1a1a !important;
      border-color: #333 !important;
    }

    body.dark-mode .contact-detail {
      color: #e0e0e0;
    }

    /* Buttons — keep accent style in dark mode */
    body.dark-mode .btn-primary {
      background: #e8002d !important;
      color: #fff !important;
    }

    body.dark-mode .btn-primary:hover {
      background: #cc0028 !important;
    }

    body.dark-mode .btn-outline {
      border-color: rgba(255, 255, 255, 0.25) !important;
      color: #fff !important;
    }

    body.dark-mode .btn-outline:hover {
      border-color: #e8002d !important;
      color: #e8002d !important;
    }

    /* Scroll bar subtle */
    body.dark-mode .off-white-section {
      background: #141414 !important;
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

<?php require_once 'assets/includes/navbar.php'; ?>

  <section class="hero">
    <div class="hero-grid-lines"></div>
    <div class="hero-content">
      <div class="hero-eyebrow">Novo Universo · 2026</div>
      <h1 class="hero-title">
        O Futuro dos<br>
        <em>Mangás &amp; Livros</em><br>
        chegou.
      </h1>
      <p class="hero-desc">
        Descobre a maior coleção de mangás e livros do universo. Edições limitadas, lançamentos exclusivos e as tuas
        séries favoritas — tudo num só lugar.
      </p>
      <div class="hero-actions">
        <a href="#destaques" class="btn-primary">Ver Coleção</a>
        <a href="carrinho.html" class="btn-outline">🛒 Ir ao Carrinho</a>
      </div>
      <div class="stats-bar">
        <div class="stat-item">
          <span class="stat-num">2.4K+</span>
          <span class="stat-label">Títulos</span>
        </div>
        <div class="stat-item">
          <span class="stat-num">98%</span>
          <span class="stat-label">Satisfação</span>
        </div>
        <div class="stat-item">
          <span class="stat-num">24h</span>
          <span class="stat-label">Envio</span>
        </div>
      </div>
    </div>
    <div class="hero-visual">
      <div class="hero-manga-stack" id="hero-stack"></div>
    </div>
  </section>

  <section id="destaques">
    <div class="section-header reveal">
      <span class="section-eyebrow">// Coleção em destaque</span>
      <h2 class="section-title">Mangás em Destaque</h2>
    </div>
    <div class="filter-tabs reveal" id="filter-tabs">
      <button class="filter-tab active" data-filter="all">Todos</button>
      <button class="filter-tab" data-filter="manga">Mangá</button>
      <button class="filter-tab" data-filter="livro">Livro</button>
      <button class="filter-tab" data-filter="novo">Novidades</button>
      <button class="filter-tab" data-filter="sale">Em Promoção</button>
    </div>
    <div class="product-grid" id="product-grid"></div>
  </section>

  <section class="banner-section reveal" id="livros">
    <div>
      <div class="banner-eyebrow">// Edições Especiais · 2026</div>
      <h2 class="banner-title">Coleções<br>Exclusivas &amp;<br>Edições Limitadas</h2>
      <p class="banner-desc">Artbooks, box sets, edições de colecionador com foil e acabamentos premium. Quantidades
        muito limitadas.</p>
    </div>
    <div>
      <a href="#destaques" class="btn-primary" style="white-space:nowrap;">Ver Edições →</a>
    </div>
  </section>

  <section id="contacto">
    <div class="section-header reveal">
      <span class="section-eyebrow">// Fala connosco</span>
      <h2 class="section-title">Contacto</h2>
    </div>
    <div class="contact-grid">
      <div class="contact-info reveal">
        <h3>Estamos aqui para ti</h3>
        <p>Tens uma dúvida sobre uma encomenda, queres saber sobre disponibilidade ou tens uma sugestão? A nossa equipa
          responde em menos de 24 horas.</p>
        <div class="contact-detail">
          <div class="contact-icon">📍</div>
          <div>
            <div class="contact-label">Morada</div>
            <div class="contact-value">Rua das Mangás, 42 — Lisboa, Portugal</div>
          </div>
        </div>
        <div class="contact-detail">
          <div class="contact-icon">✉️</div>
          <div>
            <div class="contact-label">Email</div>
            <div class="contact-value">suporte@mangaverse.pt</div>
          </div>
        </div>
        <div class="contact-detail">
          <div class="contact-icon">📞</div>
          <div>
            <div class="contact-label">Telefone</div>
            <div class="contact-value">+351 210 000 000</div>
          </div>
        </div>
      </div>
      <div class="contact-form reveal">
        <div class="form-group">
          <label class="form-label">Nome</label>
          <input type="text" class="form-input" id="contact-name" placeholder="O teu nome">
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" class="form-input" id="contact-email" placeholder="email@exemplo.com">
        </div>
        <div class="form-group">
          <label class="form-label">Mensagem</label>
          <textarea class="form-textarea" id="contact-msg" placeholder="Como podemos ajudar?"></textarea>
        </div>
        <button class="btn-primary" style="width:100%" id="contact-submit">Enviar Mensagem</button>
      </div>
    </div>
  </section>

  <footer>
    <div class="footer-grid">
      <div class="footer-brand">
        <span class="logo">Manga<span>Verse</span></span>
        <p>A loja de mangás e livros do futuro. Curadoria premium, envio rápido e uma comunidade apaixonada por cultura
          japonesa e literatura.</p>
      </div>
      <div class="footer-col">
        <h4>Loja</h4>
        <ul>
          <li><a href="index.php">Página Inicial</a></li>
          <li><a href="marketplace.php">Marketplace</a></li>
          <li><a href="carrinho.php">Carrinho</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Suporte</h4>
        <ul>
          <li><a href="suporte.php">Centro de Suporte</a></li>
          <li><a href="contacto.php">Contacto</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Conta</h4>
        <ul>
          <li><a href="login.php">Login</a></li>
          <li><a href="registo.php">Registar</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© 2026 MangaVerse. Todos os direitos reservados.</p>
      <div class="footer-social">
        <a class="social-link" href="#">𝕏</a>
        <a class="social-link" href="#">ig</a>
        <a class="social-link" href="#">yt</a>
        <a class="social-link" href="#">dc</a>
      </div>
    </div>
  </footer>


  <script>
    const Model = {
      products: [
        { id: 1, name: 'One Piece', author: 'Eiichiro Oda', type: 'manga', price: 7.99, oldPrice: null, badge: 'hot', color: ['#e8002d', '#f7a500'], vol: 'Vol. 104' },
        { id: 2, name: 'Jujutsu Kaisen', author: 'Gege Akutami', type: 'manga', price: 6.99, oldPrice: null, badge: 'new', color: ['#0057ff', '#000'], vol: 'Vol. 24' },
        { id: 3, name: 'Chainsaw Man', author: 'Tatsuki Fujimoto', type: 'manga', price: 7.49, oldPrice: 9.99, badge: 'sale', color: ['#222', '#e8002d'], vol: 'Vol. 16' },
        { id: 4, name: 'Berserk', author: 'Kentaro Miura', type: 'manga', price: 12.99, oldPrice: null, badge: null, color: ['#1a1a2e', '#c5a028'], vol: 'Vol. 41' },
        { id: 5, name: 'Attack on Titan', author: 'Hajime Isayama', type: 'manga', price: 8.99, oldPrice: 10.99, badge: 'sale', color: ['#3a3a3a', '#8b5a2b'], vol: 'Vol. 34' },
        { id: 6, name: 'Demon Slayer', author: 'Koyoharu Gotouge', type: 'manga', price: 6.49, oldPrice: null, badge: 'new', color: ['#1a472a', '#c21807'], vol: 'Vol. 23' },
        { id: 7, name: 'Duna', author: 'Frank Herbert', type: 'livro', price: 14.99, oldPrice: 18.99, badge: 'sale', color: ['#c5a028', '#8b3a0a'], vol: 'Ed. Especial' },
        { id: 8, name: 'Neuromancer', author: 'William Gibson', type: 'livro', price: 11.99, oldPrice: null, badge: 'new', color: ['#0d1117', '#00ff88'], vol: 'Edição 2026' },
        { id: 9, name: 'Vinland Saga', author: 'Makoto Yukimura', type: 'manga', price: 9.99, oldPrice: null, badge: null, color: ['#2c4a6e', '#d4a017'], vol: 'Vol. 27' },
        { id: 10, name: 'Tokyo Ghoul', author: 'Sui Ishida', type: 'manga', price: 7.99, oldPrice: 9.49, badge: 'sale', color: ['#1a0a2e', '#8b1a4a'], vol: 'Vol. 14' },
        { id: 11, name: 'Maus', author: 'Art Spiegelman', type: 'livro', price: 16.99, oldPrice: null, badge: 'hot', color: ['#2d2d2d', '#f0f0f0'], vol: 'Completo' },
        { id: 12, name: 'Blue Period', author: 'Tsubasa Yamaguchi', type: 'manga', price: 7.49, oldPrice: null, badge: 'new', color: ['#1a3a6e', '#4a90d9'], vol: 'Vol. 14' },
      ],

      cart: JSON.parse(localStorage.getItem('mv_cart') || '[]'),

      getProducts(filter) {
        if (!filter || filter === 'all') return this.products;
        if (filter === 'novo') return this.products.filter(p => p.badge === 'new');
        if (filter === 'sale') return this.products.filter(p => p.badge === 'sale');
        return this.products.filter(p => p.type === filter);
      },

      addToCart(id) {
        const product = this.products.find(p => p.id === id);
        if (!product) return;
        const existing = this.cart.find(c => c.id === id);
        if (existing) {
          existing.qty += 1;
        } else {
          this.cart.push({ ...product, qty: 1 });
        }
        this._saveCart();
        return product;
      },

      getCartCount() {
        return this.cart.reduce((sum, item) => sum + item.qty, 0);
      },

      _saveCart() {
        localStorage.setItem('mv_cart', JSON.stringify(this.cart));
      }
    };

    // ── VIEW ───────────────────────────────────────────────
    const View = {
      heroColors: [
        ['#e8002d', '#f7a500'], ['#0057ff', '#001a66'], ['#1a472a', '#c21807'],
        ['#c5a028', '#3a200a'], ['#2c4a6e', '#d4a017'], ['#0d1117', '#00ff88'],
      ],

      renderHeroStack() {
        const stack = document.getElementById('hero-stack');
        if (!stack) return;
        this.heroColors.forEach(([c1, c2], i) => {
          const card = document.createElement('div');
          card.className = 'hero-manga-card';
          card.innerHTML = `<div class="manga-cover" style="background:linear-gradient(160deg,${c1},${c2});font-size:0.55rem;">Vol.${i + 1}</div>`;
          stack.appendChild(card);
        });
      },

      renderProducts(products) {
        const grid = document.getElementById('product-grid');
        if (!grid) return;
        grid.innerHTML = '';
        products.forEach((p, idx) => {
          const card = document.createElement('div');
          card.className = 'product-card reveal';
          card.style.transitionDelay = `${idx * 60}ms`;
          card.innerHTML = `
          ${p.badge ? `<span class="product-badge badge-${p.badge}">${p.badge === 'new' ? 'Novo' : p.badge === 'hot' ? '🔥 Hot' : 'Sale'}</span>` : ''}
          <div class="product-img-wrap">
            <div class="product-cover" style="background:linear-gradient(160deg,${p.color[0]},${p.color[1]})">
              ${p.name}<br><span style="font-size:0.5rem;opacity:0.7">${p.vol}</span>
            </div>
          </div>
          <div class="product-info">
            <div class="product-type">${p.type === 'manga' ? '// Mangá' : '// Livro'}</div>
            <div class="product-name">${p.name}</div>
            <div class="product-author">${p.author}</div>
            <div class="product-bottom">
              <div class="product-price">
                ${p.oldPrice ? `<span class="old-price">${p.oldPrice.toFixed(2)}€</span>` : ''}
                ${p.price.toFixed(2)}€
              </div>
              <button class="add-cart-btn" data-id="${p.id}" title="Adicionar ao carrinho">+</button>
            </div>
          </div>`;
          grid.appendChild(card);
          setTimeout(() => card.classList.add('visible'), 80 + idx * 60);
        });
      },

      updateCartCount(count) {
        const navEl = document.getElementById('nav-cart-count');
        const floatEl = document.getElementById('float-cart-count');
        if (navEl) navEl.textContent = count;
        if (floatEl) floatEl.textContent = count;
      },

      showAddedAlert(product) {
        Swal.fire({
          toast: true,
          position: 'bottom-end',
          icon: 'success',
          title: `<span style="font-family:'Orbitron',sans-serif;font-size:0.78rem;">${product.name}</span>`,
          text: 'Adicionado ao carrinho!',
          showConfirmButton: false,
          timer: 2200,
          timerProgressBar: true,
          background: '#0a0a0a',
          color: '#fff',
          iconColor: '#e8002d',
          customClass: { popup: 'swal-dark-toast' }
        });
      }
    };

    // ── CONTROLLER ─────────────────────────────────────────
    const Controller = {
      currentFilter: 'all',

      init() {
        View.renderHeroStack();
        this.renderProducts('all');
        View.updateCartCount(Model.getCartCount());
        this._bindFilterTabs();
        this._bindContactForm();
        this._bindScrollReveal();
      },

      renderProducts(filter) {
        this.currentFilter = filter;
        const products = Model.getProducts(filter);
        View.renderProducts(products);
        this._bindAddToCart();
      },

      _bindFilterTabs() {
        document.getElementById('filter-tabs').addEventListener('click', e => {
          const tab = e.target.closest('.filter-tab');
          if (!tab) return;
          document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
          tab.classList.add('active');
          this.renderProducts(tab.dataset.filter);
        });
      },

      _bindAddToCart() {
        document.querySelectorAll('.add-cart-btn').forEach(btn => {
          btn.addEventListener('click', e => {
            e.stopPropagation();
            const id = parseInt(btn.dataset.id);
            const product = Model.addToCart(id);
            View.updateCartCount(Model.getCartCount());
            View.showAddedAlert(product);
            btn.style.background = '#e8002d';
            btn.textContent = '✓';
            setTimeout(() => {
              btn.style.background = '';
              btn.textContent = '+';
            }, 1200);
          });
        });
      },

      _bindContactForm() {
        document.getElementById('contact-submit').addEventListener('click', () => {
          const name = document.getElementById('contact-name').value.trim();
          const email = document.getElementById('contact-email').value.trim();
          const msg = document.getElementById('contact-msg').value.trim();
          if (!name || !email || !msg) {
            Swal.fire({ icon: 'warning', title: 'Campos em falta', text: 'Por favor preenche todos os campos.', confirmButtonColor: '#e8002d' });
            return;
          }
          Swal.fire({
            icon: 'success',
            title: 'Mensagem enviada!',
            text: `Obrigado, ${name}! Responderemos brevemente.`,
            confirmButtonColor: '#0a0a0a',
            confirmButtonText: 'Fechar'
          });
          document.getElementById('contact-name').value = '';
          document.getElementById('contact-email').value = '';
          document.getElementById('contact-msg').value = '';
        });
      },

      _bindScrollReveal() {
        const obs = new IntersectionObserver(entries => {
          entries.forEach(entry => {
            if (entry.isIntersecting) entry.target.classList.add('visible');
          });
        }, { threshold: 0.1 });
        document.querySelectorAll('.reveal').forEach(el => obs.observe(el));
      }
    };

    // ── BOOT ───────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => Controller.init());

    // Re-observe new cards after filter
    const origRenderProducts = View.renderProducts.bind(View);
    View.renderProducts = function (products) {
      origRenderProducts(products);
      const obs = new IntersectionObserver(entries => {
        entries.forEach(entry => { if (entry.isIntersecting) entry.target.classList.add('visible'); });
      }, { threshold: 0.1 });
      document.querySelectorAll('.reveal').forEach(el => obs.observe(el));
    };

    // ── Dark Mode handled by navbar.php ──
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

  <script>
    // ── Product Drawer (index.php — uses local Model) ──
    (function () {
      var _drawerProduct = null;

      function openDrawer(p) {
        _drawerProduct = p;

        document.getElementById('drawer-cover').style.background =
          'linear-gradient(160deg,' + (p.color ? p.color[0] : '#0a0a0a') + ',' + (p.color ? p.color[1] : '#e8002d') + ')';

        var badgeEl = document.getElementById('drawer-badge');
        var badgeLabel = { new: 'Novo', hot: '🔥 Hot', sale: 'Sale' }[p.badge] || '';
        var badgeClass = { new: 'badge-new', hot: 'badge-hot', sale: 'badge-sale' }[p.badge] || '';
        if (p.badge) {
          badgeEl.className = 'drawer-badge listing-badge ' + badgeClass;
          badgeEl.textContent = badgeLabel;
          badgeEl.style.display = '';
        } else {
          badgeEl.style.display = 'none';
        }

        document.getElementById('drawer-type').textContent = p.type === 'manga' ? '// Mangá' : '// Livro';
        document.getElementById('drawer-title').textContent = p.name + (p.vol ? ' — ' + p.vol : '');
        document.getElementById('drawer-author').textContent = 'por ' + p.author;
        document.getElementById('drawer-desc').textContent = 'Sem descrição disponível.';

        var meta = '<span class="drawer-meta-item">✅ Novo</span>';
        document.getElementById('drawer-meta').innerHTML = meta;

        var oldEl = document.getElementById('drawer-old-price');
        if (p.oldPrice) {
          oldEl.textContent = p.oldPrice.toFixed(2) + '€';
          oldEl.style.display = '';
        } else {
          oldEl.style.display = 'none';
        }
        document.getElementById('drawer-price').textContent = p.price.toFixed(2) + '€';
        document.getElementById('drawer-stock').className = 'drawer-stock stock-ok';
        document.getElementById('drawer-stock').textContent = 'Em Stock';

        var btn = document.getElementById('drawer-add-btn');
        btn.disabled = false;
        btn.textContent = '🛒 Adicionar ao Carrinho';

        document.getElementById('drawer-overlay').classList.add('open');
      }

      function closeDrawer() {
        document.getElementById('drawer-overlay').classList.remove('open');
      }

      document.getElementById('drawer-overlay').addEventListener('click', function(e) {
        if (e.target === this) closeDrawer();
      });
      document.getElementById('drawer-close').addEventListener('click', closeDrawer);
      document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeDrawer(); });

      // Bind card clicks (re-bind after filter render)
      function bindCardClicks() {
        document.querySelectorAll('.product-card').forEach(function(card) {
          card.addEventListener('click', function(e) {
            if (e.target.closest('.add-cart-btn')) return;
            var id = parseInt(card.querySelector('.add-cart-btn').dataset.id);
            var product = Model.products.find(function(p) { return p.id === id; });
            if (product) openDrawer(product);
          });
        });
      }

      // Wrap View.renderProducts to also bind drawer
      var _origRender = View.renderProducts.bind(View);
      View.renderProducts = function(products) {
        _origRender(products);
        setTimeout(bindCardClicks, 100);
      };

      document.getElementById('drawer-add-btn').addEventListener('click', function() {
        if (!_drawerProduct) return;
        var btn = this;
        var product = Model.addToCart(_drawerProduct.id);
        View.updateCartCount(Model.getCartCount());
        btn.textContent = '✓ Adicionado!';
        setTimeout(function() { btn.textContent = '🛒 Adicionar ao Carrinho'; }, 1800);
        Swal.fire({
          toast: true, position: 'bottom-end', icon: 'success',
          title: '<span style="font-family:\'Orbitron\',sans-serif;font-size:0.78rem">' + (_drawerProduct.name || '') + '</span>',
          text: 'Adicionado ao carrinho!',
          showConfirmButton: false, timer: 2200, timerProgressBar: true,
          background: '#0a0a0a', color: '#fff', iconColor: '#e8002d'
        });
      });
    })();
  </script>
</body>

</html>