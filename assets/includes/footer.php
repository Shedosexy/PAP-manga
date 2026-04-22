<!-- ═══ FOOTER ═══ -->
<footer>
  <div class="footer-grid">
    <div class="footer-brand">
      <span class="logo">Manga<span>Verse</span></span>
      <p>A loja de mangás e livros do futuro. Curadoria premium, envio rápido e uma comunidade apaixonada por cultura japonesa e literatura.</p>
    </div>
    <div class="footer-col">
      <h4>Loja</h4>
      <ul>
        <li><a href="<?= $basePath ?? '' ?>index.php">Página Inicial</a></li>
        <li><a href="<?= $basePath ?? '' ?>marketplace.php">Marketplace</a></li>
        <li><a href="<?= $basePath ?? '' ?>carrinho.php">Carrinho</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Suporte</h4>
      <ul>
        <li><a href="<?= $basePath ?? '' ?>suporte.php">Centro de Suporte</a></li>
        <li><a href="<?= $basePath ?? '' ?>contacto.php">Contacto</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Conta</h4>
      <ul>
        <li><a href="<?= $basePath ?? '' ?>login.php">Login</a></li>
        <li><a href="<?= $basePath ?? '' ?>registo.php">Registar</a></li>
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

<?php if (!defined('FOOTER_STYLES_LOADED')): define('FOOTER_STYLES_LOADED', true); ?>
<style>
footer { background: #0a0a0a; color: white; padding: 64px 80px 32px; }
.footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 48px; margin-bottom: 40px; }
.footer-brand .logo { font-family: 'Orbitron', sans-serif; font-size: 1.35rem; font-weight: 900; letter-spacing: 0.08em; display: block; margin-bottom: 16px; color: white; }
.footer-brand .logo span { color: #e8002d; }
.footer-brand p { font-size: 0.88rem; line-height: 1.75; color: rgba(255,255,255,0.45); max-width: 320px; }
.footer-col h4 { font-family: 'Space Mono', monospace; font-size: 0.68rem; letter-spacing: 0.18em; text-transform: uppercase; margin-bottom: 18px; color: rgba(255,255,255,0.8); }
.footer-col ul { list-style: none; padding: 0; margin: 0; }
.footer-col ul li { margin-bottom: 10px; }
.footer-col ul a { color: rgba(255,255,255,0.4); font-size: 0.85rem; text-decoration: none; transition: color 0.2s; }
.footer-col ul a:hover { color: #e8002d; }
.footer-bottom { display: flex; align-items: center; justify-content: space-between; padding-top: 28px; border-top: 1px solid rgba(255,255,255,0.08); }
.footer-bottom p { font-family: 'Space Mono', monospace; font-size: 0.62rem; letter-spacing: 0.1em; color: rgba(255,255,255,0.25); }
.footer-social { display: flex; gap: 16px; }
.footer-social a, .social-link { color: rgba(255,255,255,0.3); text-decoration: none; font-family: 'Space Mono', monospace; font-size: 0.72rem; transition: color 0.2s; }
.footer-social a:hover, .social-link:hover { color: #e8002d; }
@media (max-width: 900px) {
  footer { padding: 40px 24px 24px; }
  .footer-grid { grid-template-columns: 1fr 1fr; gap: 32px; }
}
@media (max-width: 600px) {
  .footer-grid { grid-template-columns: 1fr; }
  .footer-bottom { flex-direction: column; gap: 12px; text-align: center; }
}
</style>
<?php endif; ?>
