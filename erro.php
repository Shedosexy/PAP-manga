<?php
// ════════════════════════════════════════════════════════
//  MangaVerse — Página de Erro
// ════════════════════════════════════════════════════════
$errorCode    = $_GET['code'] ?? '500';
$errorMessage = $_GET['msg']  ?? 'Ocorreu um erro inesperado.';

$titles = [
    '404' => 'Página não encontrada',
    '403' => 'Acesso negado',
    '500' => 'Erro interno',
];

$descs = [
    '404' => 'A página que procuras não existe ou foi movida.',
    '403' => 'Não tens permissão para aceder a esta página.',
    '500' => 'Algo correu mal do nosso lado. Tenta novamente mais tarde.',
];

$kanji = [
    '404' => '迷子',
    '403' => '禁止',
    '500' => '故障',
];

$title = $titles[$errorCode] ?? 'Erro';
$desc  = $descs[$errorCode]  ?? htmlspecialchars($errorMessage);
$jp    = $kanji[$errorCode]  ?? 'エラー';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $errorCode ?> — MangaVerse</title>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Noto+Sans+JP:wght@300;400;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --white: #ffffff; --off-white: #f7f7f5; --black: #0a0a0a;
      --accent: #e8002d; --grey: #8a8a8a; --light-grey: #ececec;
      --font-display: 'Orbitron', sans-serif;
      --font-body: 'Noto Sans JP', sans-serif;
      --font-mono: 'Space Mono', monospace;
    }
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
    html, body { height: 100%; }
    body {
      font-family: var(--font-body); background: var(--black); color: var(--white);
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      overflow: hidden; position: relative;
    }

    /* Grid overlay */
    .grid-bg {
      position: absolute; inset: 0; pointer-events: none;
      background-image:
        linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
      background-size: 60px 60px;
    }

    /* Giant background kanji */
    .kanji-bg {
      position: absolute; top: 50%; left: 50%;
      transform: translate(-50%, -50%);
      font-family: var(--font-body); font-size: clamp(15rem, 30vw, 28rem);
      font-weight: 900; color: rgba(255,255,255,0.03);
      pointer-events: none; white-space: nowrap; user-select: none;
    }

    .error-container {
      position: relative; z-index: 2; text-align: center; padding: 40px;
      max-width: 600px;
    }

    .error-eyebrow {
      font-family: var(--font-mono); font-size: 0.68rem; letter-spacing: 0.25em;
      text-transform: uppercase; color: var(--accent); margin-bottom: 20px;
      display: inline-flex; align-items: center; gap: 12px;
    }
    .error-eyebrow::before {
      content: ''; width: 32px; height: 1.5px; background: var(--accent);
    }

    .error-code {
      font-family: var(--font-display); font-size: clamp(6rem, 15vw, 12rem);
      font-weight: 900; line-height: 1; margin-bottom: 12px;
      color: var(--white); position: relative;
    }
    .error-code span { color: var(--accent); }

    .error-title {
      font-family: var(--font-display); font-size: clamp(1.2rem, 2.5vw, 1.8rem);
      font-weight: 700; margin-bottom: 16px; letter-spacing: 0.02em;
    }

    .error-desc {
      font-size: 1rem; line-height: 1.75; color: rgba(255,255,255,0.5);
      margin-bottom: 40px; max-width: 460px; margin-left: auto; margin-right: auto;
    }

    .error-actions { display: flex; justify-content: center; gap: 16px; flex-wrap: wrap; }

    .btn-primary {
      display: inline-block; background: var(--accent); color: white; padding: 14px 32px;
      border-radius: 6px; text-decoration: none; font-family: var(--font-mono);
      font-size: 0.75rem; letter-spacing: 0.15em; text-transform: uppercase;
      transition: all 0.25s; border: 1.5px solid var(--accent);
    }
    .btn-primary:hover {
      background: transparent; color: var(--accent);
      box-shadow: 0 0 24px rgba(232,0,45,0.3);
    }

    .btn-outline {
      display: inline-block; background: transparent; color: rgba(255,255,255,0.6);
      padding: 14px 32px; border-radius: 6px; text-decoration: none;
      font-family: var(--font-mono); font-size: 0.75rem; letter-spacing: 0.15em;
      text-transform: uppercase; transition: all 0.25s;
      border: 1.5px solid rgba(255,255,255,0.15);
    }
    .btn-outline:hover { border-color: var(--white); color: var(--white); }

    .error-footer {
      position: absolute; bottom: 32px; left: 0; right: 0; text-align: center;
    }
    .error-footer a {
      font-family: var(--font-display); font-size: 1rem; font-weight: 900;
      letter-spacing: 0.08em; color: rgba(255,255,255,0.15); text-decoration: none;
    }
    .error-footer a span { color: rgba(232,0,45,0.3); }

    /* Glitch animation on code */
    .error-code { animation: glitch 3s infinite; }
    @keyframes glitch {
      0%, 92%, 100% { text-shadow: none; }
      93% { text-shadow: -4px 0 var(--accent), 4px 0 #0057ff; }
      94% { text-shadow: 4px 0 var(--accent), -4px 0 #0057ff; }
      95% { text-shadow: none; }
      96% { text-shadow: -2px 0 var(--accent), 2px 0 #0057ff; }
    }

    @media (max-width: 600px) {
      .error-actions { flex-direction: column; align-items: center; }
      .btn-primary, .btn-outline { width: 100%; text-align: center; }
    }
  </style>
</head>
<body>
  <div class="grid-bg"></div>
  <div class="kanji-bg"><?= $jp ?></div>

  <div class="error-container">
    <div class="error-eyebrow">// Erro <?= htmlspecialchars($errorCode) ?></div>
    <div class="error-code"><?= substr($errorCode, 0, 1) ?><span><?= substr($errorCode, 1) ?></span></div>
    <h1 class="error-title"><?= htmlspecialchars($title) ?></h1>
    <p class="error-desc"><?= htmlspecialchars($desc) ?></p>
    <div class="error-actions">
      <a href="index.php" class="btn-primary">Ir para a Loja</a>
      <a href="javascript:history.back()" class="btn-outline">← Voltar</a>
    </div>
  </div>

  <div class="error-footer">
    <a href="index.php">Manga<span>Verse</span></a>
  </div>
</body>
</html>
