<?php
// ════════════════════════════════════════════════════════
//  Controller — Autenticação
//  Endpoint: assets/controller/controllerAuth.php
//  Ações: login, registar, logout, perfil
// ════════════════════════════════════════════════════════
require_once __DIR__ . '/../model/modelAuth.php';

header('Content-Type: application/json; charset=utf-8');

$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

switch ($acao) {
    case 'registar':
        $nome     = trim($_POST['nome'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($nome) || empty($email) || empty($password)) {
            jsonResponse(['success' => false, 'message' => 'Todos os campos são obrigatórios.'], 400);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['success' => false, 'message' => 'Email inválido.'], 400);
        }
        if (strlen($password) < 6) {
            jsonResponse(['success' => false, 'message' => 'A password deve ter pelo menos 6 caracteres.'], 400);
        }

        $result = ModelAuth::registar($nome, $email, $password);
        jsonResponse($result, $result['success'] ? 200 : 409);
        break;

    case 'login':
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            jsonResponse(['success' => false, 'message' => 'Email e password são obrigatórios.'], 400);
        }

        $result = ModelAuth::login($email, $password);
        jsonResponse($result, $result['success'] ? 200 : 401);
        break;

    case 'logout':
        $result = ModelAuth::logout();
        jsonResponse($result);
        break;

    case 'perfil':
        if (!isLoggedIn()) {
            jsonResponse(['success' => false, 'message' => 'Não autenticado.'], 401);
        }
        $perfil = ModelAuth::getPerfil($_SESSION['user_id']);
        jsonResponse(['success' => true, 'user' => $perfil]);
        break;

    case 'verificar':
        jsonResponse([
            'success'   => true,
            'loggedIn'  => isLoggedIn(),
            'user'      => getLoggedUser()
        ]);
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Ação inválida.'], 400);
}
