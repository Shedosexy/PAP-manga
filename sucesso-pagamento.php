<?php
require_once 'assets/config/database.php';
initSession();
$currentPage = '';
$basePath    = '';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pagamento Concluído | MangaVerse</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .result-page{min-height:80vh;display:flex;align-items:center;justify-content:center;text-align:center;padding:40px 20px}
    .result-card{background:var(--dark,#111);border:1px solid rgba(255,255,255,.08);border-radius:16px;padding:60px 40px;max-width:520px;width:100%}
    .result-icon{font-size:72px;margin-bottom:20px}
    .result-card h1{font-family:'Orbitron',sans-serif;font-size:1.8rem;color:#fff;margin-bottom:12px}
    .result-card p{color:#aaa;font-size:1rem;margin-bottom:24px}
    .result-card .encomenda-id{color:var(--accent,#e8002d);font-family:'Space Mono',monospace;font-size:1.1rem}
    .result-card .btn-accent{background:var(--accent,#e8002d);color:#fff;border:none;padding:12px 32px;border-radius:8px;font-weight:600;text-decoration:none;display:inline-block;margin:6px}
    .result-card .btn-accent:hover{opacity:.85}
    .result-card .btn-outline{border:1px solid rgba(255,255,255,.2);color:#fff;padding:12px 32px;border-radius:8px;text-decoration:none;display:inline-block;margin:6px}
    .result-card .btn-outline:hover{border-color:#fff}
  </style>
</head>
<body>
<?php require_once 'assets/includes/navbar.php'; ?>

<div class="result-page">
  <div class="result-card">
    <div class="result-icon">✅</div>
    <h1>Pagamento Concluído!</h1>
    <?php if(isset($_GET['encomenda'])): ?>
      <p>A tua encomenda foi registada com sucesso.</p>
      <p class="encomenda-id">Encomenda #<?= htmlspecialchars($_GET['encomenda']) ?></p>
    <?php else: ?>
      <p>O teu pagamento foi processado com sucesso.</p>
    <?php endif; ?>
    <div style="margin-top:28px">
      <a href="marketplace.html" class="btn-accent">Continuar a Comprar</a>
      <a href="index.php" class="btn-outline">Página Inicial</a>
    </div>
  </div>
</div>

<?php require_once 'assets/includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
