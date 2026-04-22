<?php
require_once 'assets/config/database.php';
initSession();
$user = getLoggedUser();
$currentPage = 'suporte';
$basePath    = '';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Suporte — MangaVerse</title>
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
    body { font-family: var(--font-body); background: var(--white); color: var(--black); overflow-x: hidden; }

    /* ─── NAVBAR ─── */
    nav {
      position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
      background: rgba(255,255,255,0.92); backdrop-filter: blur(16px);
      border-bottom: 1.5px solid var(--light-grey);
      display: flex; align-items: center; justify-content: space-between;
      padding: 0 48px; height: 72px;
    }
    /* navbar via assets/includes/navbar.php */

    .page-wrap { padding-top: 72px; }

    /* ─── HERO ─── */
    .sup-hero { background: var(--black); color: white; padding: 64px 80px 56px; position: relative; overflow: hidden; }
    .sup-hero::before { content: '支援'; position: absolute; right: 60px; top: 50%; transform: translateY(-50%); font-family: var(--font-display); font-size: 16rem; font-weight: 900; color: rgba(255,255,255,0.03); pointer-events: none; }
    .sup-hero-grid { position: absolute; inset: 0; background-image: linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px); background-size: 60px 60px; pointer-events: none; }
    .sup-hero-inner { position: relative; z-index: 2; }
    .sup-eyebrow { font-family: var(--font-mono); font-size: 0.68rem; letter-spacing: 0.25em; text-transform: uppercase; color: var(--accent); margin-bottom: 18px; display: flex; align-items: center; gap: 12px; }
    .sup-eyebrow::before { content: ''; width: 32px; height: 1.5px; background: var(--accent); }
    .sup-title { font-family: var(--font-display); font-size: clamp(2rem, 4vw, 3.2rem); font-weight: 900; line-height: 1.1; margin-bottom: 16px; }
    .sup-title em { font-style: normal; color: var(--accent); }
    .sup-desc { font-size: 1rem; line-height: 1.75; color: rgba(255,255,255,0.5); max-width: 540px; }

    /* ─── CONTENT ─── */
    .sup-content { max-width: 1100px; margin: 0 auto; padding: 60px 48px; }

    /* ─── TABS ─── */
    .sup-tabs { display: flex; gap: 4px; margin-bottom: 40px; border-bottom: 1.5px solid var(--light-grey); padding-bottom: 0; }
    .sup-tab { font-family: var(--font-mono); font-size: 0.72rem; letter-spacing: 0.12em; text-transform: uppercase; padding: 14px 24px; border: none; background: none; color: var(--grey); cursor: pointer; position: relative; transition: color 0.2s; }
    .sup-tab::after { content: ''; position: absolute; bottom: -1.5px; left: 0; right: 0; height: 2.5px; background: transparent; transition: background 0.2s; }
    .sup-tab:hover { color: var(--black); }
    .sup-tab.active { color: var(--black); font-weight: 700; }
    .sup-tab.active::after { background: var(--accent); }

    .tab-panel { display: none; }
    .tab-panel.active { display: block; }

    /* ─── CREATE TICKET FORM ─── */
    .ticket-form-card { background: var(--off-white); border: 1.5px solid var(--card-border); border-radius: 16px; padding: 40px; }
    .ticket-form-title { font-family: var(--font-display); font-size: 1.2rem; font-weight: 700; margin-bottom: 24px; }
    .form-group { margin-bottom: 20px; }
    .form-label { display: block; font-family: var(--font-mono); font-size: 0.62rem; letter-spacing: 0.15em; text-transform: uppercase; color: var(--grey); margin-bottom: 8px; }
    .form-input, .form-textarea, .form-select { width: 100%; padding: 12px 16px; border: 1.5px solid var(--card-border); border-radius: 8px; font-family: var(--font-body); font-size: 0.9rem; color: var(--black); outline: none; transition: border-color 0.2s; background: var(--white); }
    .form-input:focus, .form-textarea:focus, .form-select:focus { border-color: var(--black); }
    .form-textarea { min-height: 120px; resize: vertical; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .form-submit { background: var(--black); color: white; padding: 14px 32px; border: none; border-radius: 8px; font-family: var(--font-mono); font-size: 0.75rem; letter-spacing: 0.15em; text-transform: uppercase; cursor: pointer; transition: all 0.2s; }
    .form-submit:hover { background: var(--accent); box-shadow: 0 8px 24px var(--glow); }

    /* ─── TICKET LIST ─── */
    .ticket-list { display: flex; flex-direction: column; gap: 16px; }
    .ticket-card { background: var(--white); border: 1.5px solid var(--card-border); border-radius: 12px; padding: 24px 28px; display: grid; grid-template-columns: auto 1fr auto auto; align-items: center; gap: 20px; cursor: pointer; transition: all 0.2s; }
    .ticket-card:hover { border-color: var(--black); transform: translateX(4px); }
    .ticket-id { font-family: var(--font-mono); font-size: 0.65rem; letter-spacing: 0.12em; color: var(--grey); }
    .ticket-title { font-family: var(--font-display); font-size: 0.85rem; font-weight: 700; }
    .ticket-date { font-family: var(--font-mono); font-size: 0.62rem; color: var(--grey); letter-spacing: 0.08em; }
    .ticket-status { font-family: var(--font-mono); font-size: 0.6rem; letter-spacing: 0.12em; text-transform: uppercase; padding: 4px 12px; border-radius: 100px; }
    .status-aberto { background: #e0f5e0; color: #1a7a1a; }
    .status-em_andamento { background: #fff3d4; color: #8a6900; }
    .status-fechado { background: var(--light-grey); color: var(--grey); }

    .empty-state { text-align: center; padding: 80px 24px; color: var(--grey); }
    .empty-state-icon { font-size: 3rem; margin-bottom: 16px; }
    .empty-state-text { font-family: var(--font-mono); font-size: 0.75rem; letter-spacing: 0.1em; }

    /* ─── TICKET DETAIL ─── */
    .ticket-detail { display: none; }
    .ticket-detail.active { display: block; }
    .detail-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 32px; gap: 16px; }
    .detail-back { display: flex; align-items: center; gap: 8px; font-family: var(--font-mono); font-size: 0.68rem; letter-spacing: 0.12em; text-transform: uppercase; color: var(--grey); border: none; background: none; cursor: pointer; transition: color 0.2s; padding: 0; }
    .detail-back:hover { color: var(--black); }
    .detail-title { font-family: var(--font-display); font-size: 1.3rem; font-weight: 700; margin-bottom: 6px; }
    .detail-meta { font-family: var(--font-mono); font-size: 0.62rem; letter-spacing: 0.1em; color: var(--grey); display: flex; gap: 16px; flex-wrap: wrap; }
    .detail-actions { display: flex; gap: 8px; }
    .btn-close-ticket { background: none; border: 1.5px solid var(--card-border); padding: 8px 16px; border-radius: 6px; font-family: var(--font-mono); font-size: 0.62rem; letter-spacing: 0.12em; text-transform: uppercase; cursor: pointer; color: var(--grey); transition: all 0.2s; }
    .btn-close-ticket:hover { border-color: var(--accent); color: var(--accent); }

    .messages-list { display: flex; flex-direction: column; gap: 16px; margin-bottom: 32px; max-height: 50vh; overflow-y: auto; padding-right: 4px; }
    .message-bubble { padding: 20px 24px; border-radius: 12px; max-width: 85%; }
    .msg-user { background: var(--off-white); border: 1.5px solid var(--card-border); align-self: flex-start; }
    .msg-admin { background: var(--black); color: white; align-self: flex-end; }
    .msg-author { font-family: var(--font-mono); font-size: 0.58rem; letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 6px; }
    .msg-user .msg-author { color: var(--accent); }
    .msg-admin .msg-author { color: var(--accent); }
    .msg-text { font-size: 0.88rem; line-height: 1.7; }
    .msg-time { font-family: var(--font-mono); font-size: 0.55rem; letter-spacing: 0.08em; color: var(--grey); margin-top: 8px; }
    .msg-admin .msg-time { color: rgba(255,255,255,0.3); }

    .reply-form { display: flex; gap: 12px; }
    .reply-input { flex: 1; padding: 14px 18px; border: 1.5px solid var(--card-border); border-radius: 10px; font-family: var(--font-body); font-size: 0.88rem; outline: none; transition: border-color 0.2s; color: var(--black); }
    .reply-input:focus { border-color: var(--black); }
    .reply-btn { background: var(--black); color: white; border: none; padding: 14px 24px; border-radius: 10px; font-family: var(--font-mono); font-size: 0.7rem; letter-spacing: 0.12em; text-transform: uppercase; cursor: pointer; transition: all 0.2s; white-space: nowrap; }
    .reply-btn:hover { background: var(--accent); }

    /* ─── FAQ ─── */
    .faq-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .faq-card { background: var(--off-white); border: 1.5px solid var(--card-border); border-radius: 12px; padding: 24px; cursor: pointer; transition: all 0.2s; }
    .faq-card:hover { border-color: var(--black); }
    .faq-card.open .faq-answer { display: block; }
    .faq-card.open .faq-toggle { transform: rotate(45deg); }
    .faq-q { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
    .faq-question { font-family: var(--font-display); font-size: 0.78rem; font-weight: 700; }
    .faq-toggle { font-family: var(--font-mono); font-size: 1.1rem; color: var(--grey); transition: transform 0.2s; flex-shrink: 0; }
    .faq-answer { display: none; margin-top: 14px; font-size: 0.82rem; line-height: 1.7; color: var(--grey); padding-top: 14px; border-top: 1px solid var(--card-border); }

    /* ─── FOOTER (via footer.php) ─── */

    @media (max-width: 900px) {
      nav { padding: 0 24px; }
      .sup-hero { padding: 48px 24px; }
      .sup-content { padding: 40px 20px; }
      .faq-grid { grid-template-columns: 1fr; }
      .ticket-card { grid-template-columns: 1fr; gap: 8px; }
      .form-row { grid-template-columns: 1fr; }
      .reply-form { flex-direction: column; }
      footer { padding: 32px 24px; flex-direction: column; gap: 12px; }
    }
  </style>
</head>
<body>

  <!-- ═══ NAVBAR ═══ -->
  <?php require_once 'assets/includes/navbar.php'; ?>

  <div class="page-wrap">

    <!-- ═══ HERO ═══ -->
    <div class="sup-hero">
      <div class="sup-hero-grid"></div>
      <div class="sup-hero-inner">
        <div class="sup-eyebrow">Centro de suporte · 2026</div>
        <h1 class="sup-title">Como podemos <em>ajudar?</em></h1>
        <p class="sup-desc">Cria um ticket de suporte e a nossa equipa responde-te o mais rápido possível. Consulta também as perguntas frequentes.</p>
      </div>
    </div>

    <!-- ═══ CONTENT ═══ -->
    <div class="sup-content">

      <!-- Tabs -->
      <div class="sup-tabs">
        <button class="sup-tab active" data-tab="criar">Criar Ticket</button>
        <button class="sup-tab" data-tab="tickets">Os meus Tickets</button>
        <button class="sup-tab" data-tab="faq">Perguntas Frequentes</button>
      </div>

      <!-- TAB: Criar Ticket -->
      <div class="tab-panel active" id="tab-criar">
        <?php if (!$user): ?>
          <div class="empty-state">
            <div class="empty-state-icon">🔒</div>
            <div class="empty-state-text">Precisas de fazer <a href="login.php" style="color:var(--accent)">login</a> para criar um ticket de suporte.</div>
          </div>
        <?php else: ?>
          <div class="ticket-form-card">
            <div class="ticket-form-title">Novo ticket de suporte</div>
            <form id="ticket-form">
              <div class="form-group">
                <label class="form-label">Assunto</label>
                <input type="text" class="form-input" id="ticket-assunto" placeholder="Descreve brevemente o problema..." required>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Categoria</label>
                  <select class="form-select" id="ticket-cat">
                    <option value="geral">Geral</option>
                    <option value="encomenda">Encomenda</option>
                    <option value="pagamento">Pagamento</option>
                    <option value="conta">Conta</option>
                    <option value="marketplace">Marketplace</option>
                    <option value="outro">Outro</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">Prioridade</label>
                  <select class="form-select" id="ticket-prioridade">
                    <option value="baixa">Baixa</option>
                    <option value="media" selected>Média</option>
                    <option value="alta">Alta</option>
                    <option value="urgente">Urgente</option>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">Descrição do problema</label>
                <textarea class="form-textarea" id="ticket-mensagem" placeholder="Explica em detalhe o que aconteceu..." required></textarea>
              </div>
              <button type="submit" class="form-submit" id="ticket-submit">Enviar Ticket →</button>
            </form>
          </div>
        <?php endif; ?>
      </div>

      <!-- TAB: Os meus Tickets -->
      <div class="tab-panel" id="tab-tickets">
        <?php if (!$user): ?>
          <div class="empty-state">
            <div class="empty-state-icon">🔒</div>
            <div class="empty-state-text">Precisas de <a href="login.php" style="color:var(--accent)">fazer login</a> para ver os teus tickets.</div>
          </div>
        <?php else: ?>
          <div id="tickets-list-view">
            <div class="ticket-list" id="ticket-list">
              <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <div class="empty-state-text">A carregar tickets...</div>
              </div>
            </div>
          </div>
          <div class="ticket-detail" id="ticket-detail">
            <div class="detail-header">
              <div>
                <button class="detail-back" id="back-to-list">← Voltar</button>
                <div class="detail-title" id="detail-title"></div>
                <div class="detail-meta">
                  <span id="detail-id"></span>
                  <span id="detail-date"></span>
                  <span id="detail-status"></span>
                  <span id="detail-prioridade"></span>
                </div>
              </div>
              <div class="detail-actions">
                <button class="btn-close-ticket" id="btn-close-ticket">Fechar Ticket</button>
              </div>
            </div>
            <div class="messages-list" id="messages-list"></div>
            <div class="reply-form" id="reply-form">
              <input type="text" class="reply-input" id="reply-input" placeholder="Escreve a tua resposta...">
              <button class="reply-btn" id="reply-btn">Enviar</button>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- TAB: FAQ -->
      <div class="tab-panel" id="tab-faq">
        <div class="faq-grid">
          <div class="faq-card">
            <div class="faq-q"><span class="faq-question">Como faço uma encomenda?</span><span class="faq-toggle">+</span></div>
            <div class="faq-answer">Navega pela loja ou marketplace, adiciona os produtos ao carrinho e segue para o checkout. Podes pagar com cartão ou referência multibanco.</div>
          </div>
          <div class="faq-card">
            <div class="faq-q"><span class="faq-question">Quanto tempo demora a entrega?</span><span class="faq-toggle">+</span></div>
            <div class="faq-answer">As entregas em Portugal Continental demoram 2-5 dias úteis. Para ilhas, o prazo pode ser de 5-8 dias úteis.</div>
          </div>
          <div class="faq-card">
            <div class="faq-q"><span class="faq-question">Posso devolver um produto?</span><span class="faq-toggle">+</span></div>
            <div class="faq-answer">Sim! Tens 14 dias para devolver qualquer produto em estado original. Contacta o suporte para iniciar o processo de devolução.</div>
          </div>
          <div class="faq-card">
            <div class="faq-q"><span class="faq-question">Como vendo no Marketplace?</span><span class="faq-toggle">+</span></div>
            <div class="faq-answer">Regista-te, vai ao Marketplace e clica em "Vender Agora". Preenche o formulário com os detalhes do produto e publica o anúncio.</div>
          </div>
          <div class="faq-card">
            <div class="faq-q"><span class="faq-question">Os pagamentos são seguros?</span><span class="faq-toggle">+</span></div>
            <div class="faq-answer">Sim! Utilizamos o Stripe para processar pagamentos de forma segura. Os teus dados financeiros nunca são armazenados nos nossos servidores.</div>
          </div>
          <div class="faq-card">
            <div class="faq-q"><span class="faq-question">Esqueci a minha password</span><span class="faq-toggle">+</span></div>
            <div class="faq-answer">Na página de login, clica em "Esqueci a password" e segue as instruções enviadas por email, ou cria um ticket de suporte.</div>
          </div>
          <div class="faq-card">
            <div class="faq-q"><span class="faq-question">Posso cancelar uma encomenda?</span><span class="faq-toggle">+</span></div>
            <div class="faq-answer">Podes cancelar uma encomenda enquanto o estado for "Pendente". Após o envio, terás de fazer uma devolução. Cria um ticket para pedir cancelamento.</div>
          </div>
          <div class="faq-card">
            <div class="faq-q"><span class="faq-question">Como contacto o suporte?</span><span class="faq-toggle">+</span></div>
            <div class="faq-answer">Podes criar um ticket nesta página, enviar um email para suporte@mangaverse.pt, ou usar o formulário de contacto na página de Contacto.</div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- ═══ FOOTER ═══ -->
  <?php require_once 'assets/includes/footer.php'; ?>

  <script>
  $(document).ready(function() {
    var currentTicketId = null;

    // ── Tabs ──
    $('.sup-tab').on('click', function() {
      var tab = $(this).data('tab');
      $('.sup-tab').removeClass('active');
      $(this).addClass('active');
      $('.tab-panel').removeClass('active');
      $('#tab-' + tab).addClass('active');

      if (tab === 'tickets') loadTickets();
    });

    // ── FAQ ──
    $('.faq-card').on('click', function() {
      $(this).toggleClass('open');
    });

    // ── Create Ticket ──
    $('#ticket-form').on('submit', function(e) {
      e.preventDefault();
      var btn = $('#ticket-submit');
      btn.prop('disabled', true).text('A enviar...');

      $.ajax({
        url: 'assets/controller/controllerSuporte.php',
        method: 'POST',
        data: {
          acao: 'criar',
          assunto: $('#ticket-assunto').val().trim(),
          mensagem: $('#ticket-mensagem').val().trim(),
          prioridade: $('#ticket-prioridade').val(),
          categoria: $('#ticket-cat').val()
        },
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            Swal.fire({
              icon: 'success',
              title: '<span style="font-family:Orbitron;font-size:1rem">Ticket criado!</span>',
              html: '<span style="font-family:Space Mono;font-size:0.75rem">O teu ticket #' + res.ticket_id + ' foi criado com sucesso.</span>',
              confirmButtonColor: '#0a0a0a',
              confirmButtonText: 'Ver Tickets'
            }).then(function() {
              $('#ticket-form')[0].reset();
              $('.sup-tab[data-tab="tickets"]').click();
            });
          } else {
            Swal.fire({ icon: 'error', title: 'Erro', text: res.message, confirmButtonColor: '#e8002d' });
          }
          btn.prop('disabled', false).text('Enviar Ticket →');
        }
      });
    });

    // ── Load Tickets ──
    function loadTickets() {
      $.ajax({
        url: 'assets/controller/controllerSuporte.php',
        method: 'POST',
        data: { acao: 'listar' },
        dataType: 'json',
        success: function(res) {
          if (!res.success) return;
          var list = $('#ticket-list');
          list.empty();

          if (res.tickets.length === 0) {
            list.html('<div class="empty-state"><div class="empty-state-icon">📭</div><div class="empty-state-text">Ainda não tens nenhum ticket de suporte.</div></div>');
            return;
          }

          res.tickets.forEach(function(t) {
            var statusClass = 'status-' + t.estado.replace(/ /g, '_');
            var statusLabel = t.estado === 'aberto' ? 'Aberto' : t.estado === 'em_andamento' ? 'Em Andamento' : 'Fechado';
            var dateStr = new Date(t.criado_em).toLocaleDateString('pt-PT', { day: '2-digit', month: 'short', year: 'numeric' });

            var card = $('<div class="ticket-card" data-id="' + t.id + '">' +
              '<span class="ticket-id">#' + t.id + '</span>' +
              '<div><div class="ticket-title">' + $('<span>').text(t.assunto).html() + '</div></div>' +
              '<span class="ticket-date">' + dateStr + '</span>' +
              '<span class="ticket-status ' + statusClass + '">' + statusLabel + '</span>' +
            '</div>');
            list.append(card);
          });
        }
      });
    }

    // ── Open Ticket Detail ──
    $(document).on('click', '.ticket-card', function() {
      currentTicketId = $(this).data('id');
      loadTicketDetail(currentTicketId);
    });

    function loadTicketDetail(id) {
      $.ajax({
        url: 'assets/controller/controllerSuporte.php',
        method: 'POST',
        data: { acao: 'detalhe', ticket_id: id },
        dataType: 'json',
        success: function(res) {
          if (!res.success) return;
          var t = res.ticket;
          var statusLabel = t.estado === 'aberto' ? 'Aberto' : t.estado === 'em_andamento' ? 'Em Andamento' : 'Fechado';

          $('#detail-title').text(t.assunto);
          $('#detail-id').text('Ticket #' + t.id);
          $('#detail-date').text('Criado ' + new Date(t.criado_em).toLocaleDateString('pt-PT'));
          $('#detail-status').html('<span class="ticket-status status-' + t.estado.replace(/ /g, '_') + '">' + statusLabel + '</span>');
          $('#detail-prioridade').text('Prioridade: ' + t.prioridade);

          // Messages
          var msgList = $('#messages-list');
          msgList.empty();

          // First message (original)
          msgList.append(
            '<div class="message-bubble msg-user">' +
              '<div class="msg-author">Tu</div>' +
              '<div class="msg-text">' + $('<span>').text(t.mensagem).html().replace(/\n/g, '<br>') + '</div>' +
              '<div class="msg-time">' + new Date(t.criado_em).toLocaleString('pt-PT') + '</div>' +
            '</div>'
          );

          // Responses
          if (res.respostas) {
            res.respostas.forEach(function(r) {
              var isAdmin = r.is_admin == 1;
              msgList.append(
                '<div class="message-bubble ' + (isAdmin ? 'msg-admin' : 'msg-user') + '">' +
                  '<div class="msg-author">' + (isAdmin ? 'Suporte MangaVerse' : 'Tu') + '</div>' +
                  '<div class="msg-text">' + $('<span>').text(r.mensagem).html().replace(/\n/g, '<br>') + '</div>' +
                  '<div class="msg-time">' + new Date(r.criado_em).toLocaleString('pt-PT') + '</div>' +
                '</div>'
              );
            });
          }

          msgList.scrollTop(msgList[0].scrollHeight);

          // Show/hide close button and reply form
          if (t.estado === 'fechado') {
            $('#btn-close-ticket').hide();
            $('#reply-form').hide();
          } else {
            $('#btn-close-ticket').show();
            $('#reply-form').show();
          }

          $('#tickets-list-view').hide();
          $('#ticket-detail').addClass('active');
        }
      });
    }

    // ── Back to list ──
    $('#back-to-list').on('click', function() {
      $('#ticket-detail').removeClass('active');
      $('#tickets-list-view').show();
      loadTickets();
    });

    // ── Reply ──
    $('#reply-btn').on('click', function() {
      var msg = $('#reply-input').val().trim();
      if (!msg) return;

      $(this).prop('disabled', true).text('...');

      $.ajax({
        url: 'assets/controller/controllerSuporte.php',
        method: 'POST',
        data: { acao: 'responder', ticket_id: currentTicketId, mensagem: msg },
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            $('#reply-input').val('');
            loadTicketDetail(currentTicketId);
          } else {
            Swal.fire({ icon: 'error', title: 'Erro', text: res.message, confirmButtonColor: '#e8002d' });
          }
          $('#reply-btn').prop('disabled', false).text('Enviar');
        }
      });
    });

    $('#reply-input').on('keypress', function(e) {
      if (e.which === 13) $('#reply-btn').click();
    });

    // ── Close Ticket ──
    $('#btn-close-ticket').on('click', function() {
      Swal.fire({
        icon: 'warning',
        title: 'Fechar ticket?',
        text: 'Tens a certeza que queres fechar este ticket?',
        showCancelButton: true,
        confirmButtonColor: '#0a0a0a',
        cancelButtonColor: '#8a8a8a',
        confirmButtonText: 'Sim, fechar',
        cancelButtonText: 'Cancelar'
      }).then(function(result) {
        if (!result.isConfirmed) return;
        $.ajax({
          url: 'assets/controller/controllerSuporte.php',
          method: 'POST',
          data: { acao: 'fechar', ticket_id: currentTicketId },
          dataType: 'json',
          success: function(res) {
            if (res.success) {
              Swal.fire({ icon: 'success', title: 'Ticket fechado', confirmButtonColor: '#0a0a0a' });
              loadTicketDetail(currentTicketId);
            }
          }
        });
      });
    });

  });
  </script>
</body>
</html>
