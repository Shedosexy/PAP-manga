<?php
// ════════════════════════════════════════════════════════
//  Controller — Suporte
//  Endpoint: assets/controller/controllerSuporte.php
//  Ações: criar, listar, listar_todos, detalhe, responder, fechar, faq
// ════════════════════════════════════════════════════════
require_once __DIR__ . '/../model/modelSuporte.php';

header('Content-Type: application/json; charset=utf-8');

$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

switch ($acao) {
    case 'criar':
        if (!isLoggedIn()) {
            jsonResponse(['success' => false, 'message' => 'Tens de fazer login para criar um ticket.'], 401);
        }

        $user = getLoggedUser();
        $nome       = $user['nome'];
        $email      = $user['email'];
        $categoria  = $_POST['categoria'] ?? 'outro';
        $prioridade = $_POST['prioridade'] ?? 'media';
        $assunto    = trim($_POST['assunto'] ?? '');
        $mensagem   = trim($_POST['mensagem'] ?? '');

        if (empty($assunto) || empty($mensagem)) {
            jsonResponse(['success' => false, 'message' => 'O assunto e a mensagem são obrigatórios.'], 400);
        }

        $dados = [
            'utilizador_id' => $_SESSION['user_id'],
            'nome'          => $nome,
            'email'         => $email,
            'categoria'     => $categoria,
            'prioridade'    => $prioridade,
            'assunto'       => $assunto,
            'mensagem'      => $mensagem,
        ];

        $result = ModelSuporte::criarTicket($dados);
        jsonResponse($result);
        break;

    case 'listar':
        if (!isLoggedIn()) {
            jsonResponse(['success' => false, 'message' => 'Não autenticado.'], 401);
        }

        if (($_SESSION['user_role'] ?? '') === 'admin') {
            $tickets = ModelSuporte::getAllTickets();
        } else {
            $tickets = ModelSuporte::getTickets($_SESSION['user_id']);
        }
        jsonResponse(['success' => true, 'tickets' => $tickets]);
        break;

    case 'listar_todos':
        requireRole(['admin']);
        $tickets = ModelSuporte::getAllTickets();
        jsonResponse(['success' => true, 'tickets' => $tickets]);
        break;

    case 'detalhe':
        if (!isLoggedIn()) {
            jsonResponse(['success' => false, 'message' => 'Não autenticado.'], 401);
        }

        $ticketId = intval($_POST['ticket_id'] ?? $_GET['ticket_id'] ?? $_GET['id'] ?? $_POST['id'] ?? 0);
        if ($ticketId <= 0) {
            jsonResponse(['success' => false, 'message' => 'ID inválido.'], 400);
        }

        $ticket = ModelSuporte::getTicket($ticketId);
        if (!$ticket) {
            jsonResponse(['success' => false, 'message' => 'Ticket não encontrado.'], 404);
        }

        if (($_SESSION['user_role'] ?? '') !== 'admin' && $ticket['utilizador_id'] != $_SESSION['user_id']) {
            jsonResponse(['success' => false, 'message' => 'Acesso negado.'], 403);
        }

        $respostas = ModelSuporte::getRespostas($ticketId);
        jsonResponse(['success' => true, 'ticket' => $ticket, 'respostas' => $respostas]);
        break;

    case 'responder':
        if (!isLoggedIn()) {
            jsonResponse(['success' => false, 'message' => 'Não autenticado.'], 401);
        }
        $ticketId = intval($_POST['ticket_id'] ?? 0);
        $mensagem = trim($_POST['mensagem'] ?? '');

        if ($ticketId <= 0 || empty($mensagem)) {
            jsonResponse(['success' => false, 'message' => 'Dados inválidos.'], 400);
        }

        $ticket = ModelSuporte::getTicket($ticketId);
        if (!$ticket) {
            jsonResponse(['success' => false, 'message' => 'Ticket não encontrado.'], 404);
        }

        $isAdmin = (($_SESSION['user_role'] ?? '') === 'admin');
        if (!$isAdmin && $ticket['utilizador_id'] != $_SESSION['user_id']) {
            jsonResponse(['success' => false, 'message' => 'Acesso negado.'], 403);
        }

        $result = ModelSuporte::responder($ticketId, $_SESSION['user_id'], $mensagem, $isAdmin);
        jsonResponse($result);
        break;

    case 'fechar':
        if (!isLoggedIn()) {
            jsonResponse(['success' => false, 'message' => 'Não autenticado.'], 401);
        }
        $ticketId = intval($_POST['ticket_id'] ?? 0);
        if ($ticketId <= 0) {
            jsonResponse(['success' => false, 'message' => 'ID inválido.'], 400);
        }

        $ticket = ModelSuporte::getTicket($ticketId);
        if (!$ticket) {
            jsonResponse(['success' => false, 'message' => 'Ticket não encontrado.'], 404);
        }

        $isAdmin = (($_SESSION['user_role'] ?? '') === 'admin');
        if (!$isAdmin && $ticket['utilizador_id'] != $_SESSION['user_id']) {
            jsonResponse(['success' => false, 'message' => 'Acesso negado.'], 403);
        }

        $result = ModelSuporte::fecharTicket($ticketId);
        jsonResponse($result);
        break;

    case 'faq':
        $faq = ModelSuporte::getFAQ();
        jsonResponse(['success' => true, 'faq' => $faq]);
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Ação inválida.'], 400);
}
