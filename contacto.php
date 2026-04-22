<?php
require_once 'assets/config/database.php';
initSession();
$user        = getLoggedUser();
$currentPage = 'contacto';
$basePath    = '';
?>
<!DOCTYPE html>
<html lang="pt">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Contacto — MangaVerse</title>
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

    .logo-dot {
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

    .nav-links a.active {
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

    .page-wrap {
      padding-top: 72px;
    }

    /* ─── HERO ─── */
    .contact-hero {
      background: var(--black);
      color: white;
      padding: 80px 80px 72px;
      position: relative;
      overflow: hidden;
    }

    .contact-hero::before {
      content: '連絡';
      position: absolute;
      right: 60px;
      top: 50%;
      transform: translateY(-50%);
      font-family: var(--font-display);
      font-size: 16rem;
      font-weight: 900;
      color: rgba(255, 255, 255, 0.03);
      pointer-events: none;
    }

    .contact-hero-grid {
      position: absolute;
      inset: 0;
      background-image:
        linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
      background-size: 60px 60px;
      pointer-events: none;
    }

    .contact-hero-inner {
      position: relative;
      z-index: 2;
      max-width: 600px;
    }

    .hero-eyebrow {
      font-family: var(--font-mono);
      font-size: 0.68rem;
      letter-spacing: 0.25em;
      text-transform: uppercase;
      color: var(--accent);
      margin-bottom: 18px;
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
      font-size: clamp(2.2rem, 4.5vw, 3.6rem);
      font-weight: 900;
      line-height: 1.05;
      margin-bottom: 18px;
    }

    .hero-title em {
      font-style: normal;
      color: var(--accent);
    }

    .hero-desc {
      font-size: 1rem;
      line-height: 1.75;
      color: rgba(255, 255, 255, 0.55);
    }

    /* ─── CONTACT SECTION ─── */
    .contact-section {
      padding: 80px 80px;
    }

    .contact-grid {
      display: grid;
      grid-template-columns: 1fr 1.2fr;
      gap: 80px;
      align-items: start;
    }

    .contact-info h3 {
      font-family: var(--font-display);
      font-size: 1.4rem;
      font-weight: 700;
      margin-bottom: 16px;
    }

    .contact-info>p {
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
      width: 50px;
      height: 50px;
      background: var(--off-white);
      border: 1.5px solid var(--card-border);
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
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
      font-size: 0.92rem;
    }

    .social-links {
      display: flex;
      gap: 10px;
      margin-top: 32px;
    }

    .social-link {
      width: 44px;
      height: 44px;
      border: 1.5px solid var(--card-border);
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--grey);
      text-decoration: none;
      font-size: 0.9rem;
      transition: all 0.2s;
    }

    .social-link:hover {
      border-color: var(--accent);
      color: var(--accent);
    }

    /* ─── FORM ─── */
    .contact-form-card {
      background: var(--white);
      border: 1.5px solid var(--card-border);
      border-radius: 16px;
      padding: 40px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.06);
    }

    .form-card-title {
      font-family: var(--font-display);
      font-size: 1.1rem;
      font-weight: 700;
      margin-bottom: 6px;
      letter-spacing: 0.04em;
    }

    .form-card-sub {
      font-family: var(--font-mono);
      font-size: 0.62rem;
      letter-spacing: 0.15em;
      text-transform: uppercase;
      color: var(--grey);
      margin-bottom: 28px;
    }

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
    .form-textarea,
    .form-select {
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
    .form-textarea:focus,
    .form-select:focus {
      border-color: var(--black);
    }

    .form-textarea {
      min-height: 130px;
      resize: vertical;
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }

    .btn-submit {
      width: 100%;
      background: var(--black);
      color: var(--white);
      padding: 16px;
      border: none;
      cursor: pointer;
      font-family: var(--font-mono);
      font-size: 0.75rem;
      letter-spacing: 0.15em;
      text-transform: uppercase;
      border-radius: 6px;
      transition: all 0.22s;
      margin-top: 8px;
    }

    .btn-submit:hover {
      background: var(--accent);
      box-shadow: 0 8px 24px var(--glow);
    }

    /* ─── MAP SECTION ─── */
    .map-section {
      background: var(--off-white);
      padding: 80px;
      border-top: 1.5px solid var(--light-grey);
    }

    .section-header {
      text-align: center;
      margin-bottom: 48px;
    }

    .section-eyebrow {
      font-family: var(--font-mono);
      font-size: 0.68rem;
      letter-spacing: 0.22em;
      text-transform: uppercase;
      color: var(--accent);
      margin-bottom: 14px;
      display: block;
    }

    .section-title {
      font-family: var(--font-display);
      font-size: clamp(1.6rem, 3vw, 2.4rem);
      font-weight: 900;
      letter-spacing: -0.01em;
    }

    .map-placeholder {
      background: var(--white);
      border: 1.5px solid var(--card-border);
      border-radius: 16px;
      height: 360px;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }

    .map-placeholder iframe {
      width: 100%;
      height: 100%;
      border: 0;
      border-radius: 16px;
    }

    /* ─── FAQ SECTION ─── */
    .faq-section {
      padding: 80px;
    }

    .faq-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      max-width: 900px;
      margin: 0 auto;
    }

    .faq-item {
      background: var(--white);
      border: 1.5px solid var(--card-border);
      border-radius: 12px;
      padding: 24px;
      transition: all 0.25s;
      cursor: pointer;
    }

    .faq-item:hover {
      border-color: var(--black);
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
    }

    .faq-question {
      font-family: var(--font-display);
      font-size: 0.82rem;
      font-weight: 700;
      letter-spacing: 0.03em;
      margin-bottom: 8px;
      display: flex;
      align-items: flex-start;
      gap: 10px;
    }

    .faq-question::before {
      content: 'Q';
      font-family: var(--font-mono);
      font-size: 0.6rem;
      background: var(--accent);
      color: white;
      width: 22px;
      height: 22px;
      border-radius: 4px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      margin-top: 1px;
    }

    .faq-answer {
      font-size: 0.85rem;
      color: var(--grey);
      line-height: 1.7;
      padding-left: 32px;
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

    .footer-social a {
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

    .footer-social a:hover {
      border-color: var(--accent);
      color: var(--accent);
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

    /* ─── DARK MODE TOGGLE ─── */
    .dark-mode-toggle {
      background: none;
      border: 1.5px solid var(--card-border);
      border-radius: 8px;
      width: 40px;
      height: 40px;
      cursor: pointer;
      font-size: 1.1rem;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: border-color .2s, background .2s;
    }

    .dark-mode-toggle:hover {
      border-color: var(--accent);
    }

    /* ─── DARK MODE ─── */
    body.dark-mode {
      background: #0a0a0a;
      color: #e0e0e0;
    }

    body.dark-mode nav {
      background: rgba(18, 18, 18, .95);
      border-bottom-color: #222;
    }

    body.dark-mode .nav-logo {
      color: #fff;
    }

    body.dark-mode .nav-links a {
      color: #999;
    }

    body.dark-mode .nav-links a:hover,
    body.dark-mode .nav-links a.active {
      color: #fff;
    }

    body.dark-mode .cart-btn {
      background: #fff;
      color: #0a0a0a !important;
    }

    body.dark-mode .cart-btn:hover {
      background: var(--accent);
      color: #fff !important;
    }

    body.dark-mode .dark-mode-toggle {
      border-color: #444;
      color: #fff;
    }

    body.dark-mode .contact-section {
      background: #0a0a0a;
    }

    body.dark-mode .contact-info h3 {
      color: #fff;
    }

    body.dark-mode .contact-info>p {
      color: #888;
    }

    body.dark-mode .contact-icon {
      background: #1a1a1a;
      border-color: #333;
      color: #ccc;
    }

    body.dark-mode .contact-label {
      color: #777;
    }

    body.dark-mode .contact-value {
      color: #e0e0e0;
    }

    body.dark-mode .social-link {
      border-color: #333;
      color: #888;
    }

    body.dark-mode .social-link:hover {
      border-color: var(--accent);
      color: var(--accent);
    }

    body.dark-mode .contact-form-card {
      background: #141414;
      border-color: #2a2a2a;
      box-shadow: 0 8px 32px rgba(0, 0, 0, .3);
    }

    body.dark-mode .form-card-title {
      color: #fff;
    }

    body.dark-mode .form-card-sub {
      color: #777;
    }

    body.dark-mode .form-input,
    body.dark-mode .form-textarea,
    body.dark-mode .form-select {
      background: #1a1a1a;
      border-color: #333;
      color: #e0e0e0;
    }

    body.dark-mode .form-input:focus,
    body.dark-mode .form-textarea:focus,
    body.dark-mode .form-select:focus {
      border-color: #666;
    }

    body.dark-mode .form-label {
      color: #777;
    }

    body.dark-mode .btn-submit {
      background: #fff;
      color: #0a0a0a;
    }

    body.dark-mode .btn-submit:hover {
      background: var(--accent);
      color: #fff;
    }

    body.dark-mode .map-section {
      background: #111;
      border-top-color: #222;
    }

    body.dark-mode .section-title {
      color: #fff;
    }

    body.dark-mode .map-placeholder {
      background: #1a1a1a;
      border-color: #333;
    }

    body.dark-mode .faq-section {
      background: #0a0a0a;
    }

    body.dark-mode .faq-item {
      background: #141414;
      border-color: #2a2a2a;
    }

    body.dark-mode .faq-item:hover {
      border-color: #555;
      box-shadow: 0 8px 24px rgba(0, 0, 0, .3);
    }

    body.dark-mode .faq-question {
      color: #fff;
    }

    body.dark-mode .faq-answer {
      color: #888;
    }

    body.dark-mode footer {
      background: #050505;
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

    body.dark-mode .footer-social a {
      border-color: rgba(255, 255, 255, 0.15);
      color: rgba(255, 255, 255, 0.5);
    }

    body.dark-mode .footer-social a:hover {
      border-color: #e8002d;
      color: #e8002d;
    }

    body.dark-mode .section-eyebrow {
      color: #e8002d;
    }

    body.dark-mode .contact-hero {
      background: #111;
    }

    body.dark-mode .hero-eyebrow {
      color: #e8002d;
    }

    body.dark-mode .contact-icon {
      background: #1a1a1a !important;
      border-color: #333 !important;
    }

    body.dark-mode .page-wrap {
      background: #0a0a0a;
    }

    @media (max-width: 900px) {
      nav {
        padding: 0 24px;
      }

      .contact-hero {
        padding: 60px 24px;
      }

      .contact-section {
        padding: 48px 24px;
      }

      .contact-grid {
        grid-template-columns: 1fr;
        gap: 40px;
      }

      .map-section,
      .faq-section {
        padding: 48px 24px;
      }

      .faq-grid {
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
  </style>
</head>

<body>

<?php require_once 'assets/includes/navbar.php'; ?>

  <div class="page-wrap">

    <!-- ═══ HERO ═══ -->
    <div class="contact-hero">
      <div class="contact-hero-grid"></div>
      <div class="contact-hero-inner">
        <div class="hero-eyebrow">Contacto · 2026</div>
        <h1 class="hero-title">Fala<br><em>connosco.</em></h1>
        <p class="hero-desc">Tens uma dúvida, sugestão ou precisas de ajuda? A nossa equipa responde em menos de 24
          horas. Estamos aqui para ti.</p>
      </div>
    </div>

    <!-- ═══ CONTACT FORM + INFO ═══ -->
    <section class="contact-section">
      <div class="contact-grid">
        <div class="contact-info reveal">
          <h3>Estamos aqui para ti</h3>
          <p>Podes contactar-nos por email, telefone ou através do formulário. Para questões técnicas ou sobre
            encomendas, recomendamos o nosso <a href="suporte.php" style="color:var(--accent)">centro de suporte</a>.
          </p>

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
          <div class="contact-detail">
            <div class="contact-icon">⏰</div>
            <div>
              <div class="contact-label">Horário</div>
              <div class="contact-value">Seg-Sex: 9h — 18h</div>
            </div>
          </div>

          <div class="social-links">
            <a class="social-link" href="#" title="Twitter">𝕏</a>
            <a class="social-link" href="#" title="Instagram">ig</a>
            <a class="social-link" href="#" title="YouTube">yt</a>
            <a class="social-link" href="#" title="Discord">dc</a>
          </div>
        </div>

        <div class="contact-form-card reveal" style="transition-delay: 100ms">
          <div class="form-card-title">Envia-nos uma mensagem</div>
          <div class="form-card-sub">// Respondemos em menos de 24h</div>

          <form id="contact-form">
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Nome</label>
                <input type="text" class="form-input" id="contact-name" placeholder="O teu nome" required>
              </div>
              <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" class="form-input" id="contact-email" placeholder="email@exemplo.com" required>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Assunto</label>
              <select class="form-select" id="contact-subject">
                <option value="geral">Questão Geral</option>
                <option value="encomenda">Sobre Encomenda</option>
                <option value="parceria">Parceria / Negócios</option>
                <option value="sugestao">Sugestão</option>
                <option value="outro">Outro</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Mensagem</label>
              <textarea class="form-textarea" id="contact-msg" placeholder="Como podemos ajudar?" required></textarea>
            </div>
            <button type="submit" class="btn-submit" id="contact-submit">Enviar Mensagem →</button>
          </form>
        </div>
      </div>
    </section>

    <!-- ═══ MAP ═══ -->
    <section class="map-section">
      <div class="section-header reveal">
        <span class="section-eyebrow">// Onde estamos</span>
        <h2 class="section-title">Visita-nos</h2>
      </div>
      <div class="map-placeholder reveal">
        <iframe
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d24884.39!2d-9.15!3d38.72!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd19331a61e4f33b%3A0x400ebbde49036d0!2sLisboa!5e0!3m2!1spt-PT!2spt!4v1"
          allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
      </div>
    </section>

    <!-- ═══ FAQ ═══ -->
    <section class="faq-section">
      <div class="section-header reveal">
        <span class="section-eyebrow">// Perguntas frequentes</span>
        <h2 class="section-title">FAQ</h2>
      </div>
      <div class="faq-grid" id="faq-grid">
        <div class="faq-item reveal">
          <div class="faq-question">Quanto tempo demora o envio?</div>
          <div class="faq-answer">Os envios são processados em 24-48h úteis. Entregas em Portugal continental demoram
            2-3 dias úteis.</div>
        </div>
        <div class="faq-item reveal" style="transition-delay:60ms">
          <div class="faq-question">Posso devolver um produto?</div>
          <div class="faq-answer">Sim, tens 14 dias para devolver qualquer produto em estado original. Os portes de
            devolução são gratuitos.</div>
        </div>
        <div class="faq-item reveal" style="transition-delay:120ms">
          <div class="faq-question">Como faço o pagamento?</div>
          <div class="faq-answer">Aceitamos Visa, Mastercard, MB Way e referência Multibanco através do Stripe.</div>
        </div>
        <div class="faq-item reveal" style="transition-delay:180ms">
          <div class="faq-question">Os mangás são em português?</div>
          <div class="faq-answer">Temos edições em português, inglês e japonês. A língua está indicada em cada produto.
          </div>
        </div>
        <div class="faq-item reveal" style="transition-delay:240ms">
          <div class="faq-question">Como vendo no Marketplace?</div>
          <div class="faq-answer">Regista-te, vai ao Marketplace e clica em "Vender agora". Preenche os dados do produto
            e publica.</div>
        </div>
        <div class="faq-item reveal" style="transition-delay:300ms">
          <div class="faq-question">Qual é a comissão do Marketplace?</div>
          <div class="faq-answer">A comissão é de 5% do valor da venda. O pagamento é processado em 48h após confirmação
            de entrega.</div>
        </div>
      </div>
    </section>

  </div>

  <!-- ═══ FOOTER ═══ -->
  <footer>
    <div class="footer-grid">
      <div class="footer-brand">
        <span class="logo">Manga<span>Verse</span></span>
        <p>A loja de mangás e livros do futuro. Curadoria premium, envio rápido e uma comunidade apaixonada.</p>
      </div>
      <div class="footer-col">
        <h4>Loja</h4>
        <ul>
          <li><a href="index.php">Mangás</a></li>
          <li><a href="marketplace.php">Marketplace</a></li>
          <li><a href="#">Novidades</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Suporte</h4>
        <ul>
          <li><a href="suporte.php">Centro de Suporte</a></li>
          <li><a href="#">Envios</a></li>
          <li><a href="#">Devoluções</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Conta</h4>
        <ul>
          <li><a href="login.php">Login</a></li>
          <li><a href="registo.php">Registar</a></li>
          <li><a href="carrinho.php">Carrinho</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© 2026 MangaVerse. Todos os direitos reservados.</p>
      <div class="footer-social">
        <a href="#">𝕏</a><a href="#">ig</a><a href="#">yt</a><a href="#">dc</a>
      </div>
    </div>
  </footer>

  <script>
    $(document).ready(function () {
      // ── Scroll Reveal ──
      const observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) entry.target.classList.add('visible');
        });
      }, { threshold: 0.1 });
      document.querySelectorAll('.reveal').forEach(function (el) { observer.observe(el); });

      // ── Contact Form ──
      $('#contact-form').on('submit', function (e) {
        e.preventDefault();

        const nome = $('#contact-name').val().trim();
        const email = $('#contact-email').val().trim();
        const assunto = $('#contact-subject').val();
        const mensagem = $('#contact-msg').val().trim();

        if (!nome || !email || !mensagem) {
          Swal.fire({ icon: 'warning', title: 'Campos em falta', text: 'Por favor preenche todos os campos obrigatórios.', confirmButtonColor: '#e8002d' });
          return;
        }

        $('#contact-submit').prop('disabled', true).text('A enviar...');

        $.ajax({
          url: 'assets/controller/controllerContacto.php',
          method: 'POST',
          data: { acao: 'enviar', nome: nome, email: email, assunto: assunto, mensagem: mensagem },
          dataType: 'json',
          success: function (res) {
            if (res.success) {
              Swal.fire({
                icon: 'success',
                title: 'Mensagem enviada!',
                text: 'Obrigado, ' + nome + '! Responderemos brevemente.',
                confirmButtonColor: '#0a0a0a',
                confirmButtonText: 'Fechar'
              });
              $('#contact-form')[0].reset();
            } else {
              Swal.fire({ icon: 'error', title: 'Erro', text: res.message, confirmButtonColor: '#e8002d' });
            }
            $('#contact-submit').prop('disabled', false).text('Enviar Mensagem →');
          },
          error: function () {
            Swal.fire({ icon: 'error', title: 'Erro', text: 'Erro de conexão. Tenta novamente.', confirmButtonColor: '#e8002d' });
            $('#contact-submit').prop('disabled', false).text('Enviar Mensagem →');
          }
        });
      });

      // ── Cart count from localStorage (fallback) ──
      var cart = JSON.parse(localStorage.getItem('mv_cart') || '[]');
      var count = cart.reduce(function (s, i) { return s + (i.qty || 1); }, 0);
      if (!$('#nav-cart-count').text() || $('#nav-cart-count').text() === '0') {
        $('#nav-cart-count').text(count);
      }
    });
  </script>
</body>

</html>