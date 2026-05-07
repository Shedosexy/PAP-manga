<?php
require_once __DIR__ . '/../model/modelAvaliacoes.php';

header('Content-Type: application/json; charset=utf-8');

$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

switch ($acao) {
    case 'site':
        jsonResponse(['success' => true, 'resumo' => ModelAvaliacoes::getSiteResumo(false)]);
        break;

    case 'criar':
        if (!isLoggedIn()) {
            jsonResponse(['success' => false, 'message' => 'Tens de fazer login primeiro.', 'redirect' => 'login.php'], 401);
        }

        $produtoId = (int) ($_POST['produto_id'] ?? 0);
        $classificacao = (int) ($_POST['classificacao'] ?? 0);
        $comentario = trim($_POST['comentario'] ?? '');

        $result = ModelAvaliacoes::criar((int) $_SESSION['user_id'], $produtoId, $classificacao, $comentario);
        jsonResponse($result, $result['success'] ? 200 : 400);
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Ação inválida.'], 400);
}