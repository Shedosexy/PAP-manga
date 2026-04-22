<?php
// ════════════════════════════════════════════════════════
//  Controller — Contacto
//  Endpoint: assets/controller/controllerContacto.php
//  Ações: enviar
// ════════════════════════════════════════════════════════
require_once __DIR__ . '/../model/modelContacto.php';

header('Content-Type: application/json; charset=utf-8');

$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

switch ($acao) {
    case 'enviar':
        $nome     = trim($_POST['nome'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $assunto  = trim($_POST['assunto'] ?? '');
        $mensagem = trim($_POST['mensagem'] ?? '');

        if (empty($nome) || empty($email) || empty($mensagem)) {
            jsonResponse(['success' => false, 'message' => 'Nome, email e mensagem são obrigatórios.'], 400);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['success' => false, 'message' => 'Email inválido.'], 400);
        }

        $result = ModelContacto::enviar($nome, $email, $assunto, $mensagem);
        jsonResponse($result);
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Ação inválida.'], 400);
}
