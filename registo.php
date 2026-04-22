<?php
require_once 'assets/config/database.php';
initSession();
$currentPage = 'registo';
$basePath    = '';
// Redirect logged-in users away from registration page
if (isLoggedIn()) { header('Location: marketplace.php'); exit; }
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registar — MangaVerse</title>
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
    body {
      font-family: var(--font-body); background: var(--white); color: var(--black);
      min-height: 100vh; display: flex; flex-direction: column;
    }

    /* navbar via assets/includes/navbar.php */

    /* ─── REGISTER LAYOUT ─── */
    .register-page {
      flex: 1; display: grid; grid-template-columns: 1fr 1fr;
      min-height: 100vh; padding-top: 72px;
    }
    .register-visual {
      background: var(--black); position: relative; overflow: hidden;
      display: flex; align-items: center; justify-content: center;
    }
    .register-visual::before {
      content: '登録';
      position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
      font-family: var(--font-display); font-size: 14rem; font-weight: 900;
      color: rgba(255,255,255,0.03); pointer-events: none;
    }
    .register-visual-grid {
      position: absolute; inset: 0;
      background-image:
        linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
      background-size: 60px 60px; pointer-events: none;
    }
    .register-visual-content {
      position: relative; z-index: 2; text-align: center; color: white; padding: 40px;
    }
    .register-visual-content h2 {
      font-family: var(--font-display); font-size: clamp(1.8rem, 3vw, 2.8rem);
      font-weight: 900; line-height: 1.1; margin-bottom: 20px;
    }
    .register-visual-content h2 em { font-style: normal; color: var(--accent); }
    .register-visual-content p {
      color: rgba(255,255,255,0.5); font-size: 1rem; line-height: 1.75; max-width: 360px; margin: 0 auto;
    }

    .register-benefits {
      display: flex; flex-direction: column; gap: 20px; margin-top: 40px; text-align: left; max-width: 320px; margin-left: auto; margin-right: auto;
    }
    .benefit-item { display: flex; align-items: center; gap: 14px; }
    .benefit-icon {
      width: 40px; height: 40px; border: 1.5px solid rgba(255,255,255,0.15); border-radius: 8px;
      display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;
    }
    .benefit-text {
      font-family: var(--font-mono); font-size: 0.72rem; letter-spacing: 0.08em;
      color: rgba(255,255,255,0.6);
    }

    .register-form-side {
      display: flex; align-items: center; justify-content: center; padding: 48px;
    }
    .register-form-container { width: 100%; max-width: 420px; }

    .form-eyebrow {
      font-family: var(--font-mono); font-size: 0.68rem; letter-spacing: 0.22em;
      text-transform: uppercase; color: var(--accent); margin-bottom: 16px;
      display: flex; align-items: center; gap: 12px;
    }
    .form-eyebrow::before { content: ''; width: 24px; height: 1.5px; background: var(--accent); }
    .form-title {
      font-family: var(--font-display); font-size: clamp(1.6rem, 3vw, 2.4rem);
      font-weight: 900; margin-bottom: 8px;
    }
    .form-subtitle { color: var(--grey); font-size: 0.92rem; margin-bottom: 36px; line-height: 1.6; }
    .form-group { margin-bottom: 20px; }
    .form-label {
      display: block; font-family: var(--font-mono); font-size: 0.65rem;
      letter-spacing: 0.15em; text-transform: uppercase; color: var(--grey); margin-bottom: 8px;
    }
    .form-input {
      width: 100%; padding: 14px 18px; border: 1.5px solid var(--card-border);
      border-radius: 8px; background: var(--white); font-family: var(--font-body);
      font-size: 0.92rem; color: var(--black); transition: border-color 0.2s; outline: none;
    }
    .form-input:focus { border-color: var(--black); }
    .password-hint {
      font-family: var(--font-mono); font-size: 0.6rem; color: var(--grey);
      margin-top: 6px; letter-spacing: 0.08em;
    }
    .btn-primary-full {
      width: 100%; background: var(--black); color: var(--white); padding: 16px;
      border: none; cursor: pointer; font-family: var(--font-mono); font-size: 0.75rem;
      letter-spacing: 0.15em; text-transform: uppercase; border-radius: 6px;
      transition: all 0.22s; margin-top: 8px;
    }
    .btn-primary-full:hover { background: var(--accent); box-shadow: 0 8px 24px var(--glow); }
    .form-footer { text-align: center; margin-top: 28px; font-size: 0.88rem; color: var(--grey); }
    .form-footer a { color: var(--accent); text-decoration: none; font-weight: 600; }
    .form-footer a:hover { text-decoration: underline; }
    .form-divider { display: flex; align-items: center; gap: 16px; margin: 24px 0; }
    .form-divider::before, .form-divider::after { content: ''; flex: 1; height: 1px; background: var(--light-grey); }
    .form-divider span { font-family: var(--font-mono); font-size: 0.62rem; letter-spacing: 0.15em; text-transform: uppercase; color: var(--grey); }

    footer {
      background: var(--black); color: white; padding: 32px 80px;
      display: flex; align-items: center; justify-content: space-between;
    }
    .footer-logo { font-family: var(--font-display); font-size: 1rem; font-weight: 900; letter-spacing: 0.08em; }
    .footer-logo span { color: var(--accent); }
    .footer-copy { font-family: var(--font-mono); font-size: 0.62rem; letter-spacing: 0.1em; color: rgba(255,255,255,0.3); }

    @media (max-width: 900px) {
      nav { padding: 0 24px; }
      .register-page { grid-template-columns: 1fr; }
      .register-visual { display: none; }
      .register-form-side { padding: 32px 24px; }
      footer { padding: 24px; flex-direction: column; gap: 12px; }
    }
  </style>
</head>
<body>

  <?php require_once 'assets/includes/navbar.php'; ?>

  <div class="register-page">
    <div class="register-visual">
      <div class="register-visual-grid"></div>
      <div class="register-visual-content">
        <h2>Junta-te ao<br><em>MangaVerse</em></h2>
        <p>Cria a tua conta e acede a milhares de mangás, livros e ao marketplace P2P.</p>
        <div class="register-benefits">
          <div class="benefit-item">
            <div class="benefit-icon">🛒</div>
            <span class="benefit-text">Carrinho e encomendas guardadas</span>
          </div>
          <div class="benefit-item">
            <div class="benefit-icon">💰</div>
            <span class="benefit-text">Vende os teus mangás no marketplace</span>
          </div>
          <div class="benefit-item">
            <div class="benefit-icon">🎯</div>
            <span class="benefit-text">Descontos exclusivos para membros</span>
          </div>
          <div class="benefit-item">
            <div class="benefit-icon">📦</div>
            <span class="benefit-text">Rastreamento de encomendas</span>
          </div>
        </div>
      </div>
    </div>

    <div class="register-form-side">
      <div class="register-form-container">
        <div class="form-eyebrow">// Criar conta</div>
        <h1 class="form-title">Registo</h1>
        <p class="form-subtitle">Preenche os dados abaixo para criar a tua conta MangaVerse.</p>

        <form id="register-form" autocomplete="on">
          <div class="form-group">
            <label class="form-label">Nome completo</label>
            <input type="text" class="form-input" id="reg-nome" placeholder="O teu nome" required autocomplete="name">
          </div>
          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" class="form-input" id="reg-email" placeholder="email@exemplo.com" required autocomplete="email">
          </div>
          <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" class="form-input" id="reg-password" placeholder="Mínimo 6 caracteres" required autocomplete="new-password">
            <div class="password-hint">Mínimo 6 caracteres</div>
          </div>
          <div class="form-group">
            <label class="form-label">Confirmar Password</label>
            <input type="password" class="form-input" id="reg-password-confirm" placeholder="Repete a password" required autocomplete="new-password">
          </div>
          <button type="submit" class="btn-primary-full" id="register-btn">Criar Conta →</button>
        </form>

        <div class="form-divider"><span>ou</span></div>

        <div class="form-footer">
          Já tens conta? <a href="login.php">Entrar</a>
        </div>
      </div>
    </div>
  </div>

  <?php require_once 'assets/includes/footer.php'; ?>

  <script>
  $(document).ready(function() {
    $('#register-form').on('submit', function(e) {
      e.preventDefault();

      const nome     = $('#reg-nome').val().trim();
      const email    = $('#reg-email').val().trim();
      const password = $('#reg-password').val();
      const confirm  = $('#reg-password-confirm').val();

      if (!nome || !email || !password || !confirm) {
        Swal.fire({ icon: 'warning', title: 'Campos em falta', text: 'Preenche todos os campos.', confirmButtonColor: '#e8002d' });
        return;
      }
      if (password.length < 6) {
        Swal.fire({ icon: 'warning', title: 'Password fraca', text: 'A password deve ter pelo menos 6 caracteres.', confirmButtonColor: '#e8002d' });
        return;
      }
      if (password !== confirm) {
        Swal.fire({ icon: 'warning', title: 'Passwords diferentes', text: 'As passwords não coincidem.', confirmButtonColor: '#e8002d' });
        return;
      }

      $('#register-btn').prop('disabled', true).text('A criar conta...');

      $.ajax({
        url: 'assets/controller/controllerAuth.php',
        method: 'POST',
        data: { acao: 'registar', nome: nome, email: email, password: password },
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            Swal.fire({
              icon: 'success',
              title: 'Conta criada!',
              text: 'Agora podes fazer login.',
              confirmButtonColor: '#0a0a0a',
              confirmButtonText: 'Ir para Login'
            }).then(function() {
              window.location.href = 'login.php';
            });
          } else {
            Swal.fire({ icon: 'error', title: 'Erro', text: res.message, confirmButtonColor: '#e8002d' });
            $('#register-btn').prop('disabled', false).text('Criar Conta →');
          }
        },
        error: function() {
          Swal.fire({ icon: 'error', title: 'Erro', text: 'Erro de conexão. Tenta novamente.', confirmButtonColor: '#e8002d' });
          $('#register-btn').prop('disabled', false).text('Criar Conta →');
        }
      });
    });
  });
  </script>
</body>
</html>
