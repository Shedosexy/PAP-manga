<?php
// ════════════════════════════════════════════════════════
//  Controller — Carrinho
//  Endpoint: assets/controller/controllerCarrinho.php
//  Ações: listar, adicionar, atualizar, remover, limpar, checkout, encomendas
// ════════════════════════════════════════════════════════
require_once __DIR__ . '/../model/modelCarrinho.php';

header('Content-Type: application/json; charset=utf-8');

$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

// Verificar autenticação para todas as ações
if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Tens de fazer login primeiro.', 'redirect' => 'login.php'], 401);
}

$userId = $_SESSION['user_id'];

switch ($acao) {
    case 'listar':
        $carrinho = ModelCarrinho::getCarrinho($userId);
        $total = ModelCarrinho::contar($userId);
        jsonResponse(['success' => true, 'carrinho' => $carrinho, 'total_itens' => $total]);
        break;

    case 'adicionar':
        $produtoId  = intval($_POST['produto_id'] ?? 0);
        $quantidade = intval($_POST['quantidade'] ?? 1);

        if ($produtoId <= 0) {
            jsonResponse(['success' => false, 'message' => 'Produto inválido.'], 400);
        }

        $result = ModelCarrinho::adicionar($userId, $produtoId, $quantidade);
        $result['total_itens'] = ModelCarrinho::contar($userId);
        jsonResponse($result);
        break;

    case 'atualizar':
        $produtoId  = intval($_POST['produto_id'] ?? 0);
        $quantidade = intval($_POST['quantidade'] ?? 0);

        if ($produtoId <= 0) {
            jsonResponse(['success' => false, 'message' => 'Produto inválido.'], 400);
        }

        $result = ModelCarrinho::atualizarQtd($userId, $produtoId, $quantidade);
        $result['total_itens'] = ModelCarrinho::contar($userId);
        jsonResponse($result);
        break;

    case 'remover':
        $produtoId = intval($_POST['produto_id'] ?? 0);
        if ($produtoId <= 0) {
            jsonResponse(['success' => false, 'message' => 'Produto inválido.'], 400);
        }

        $result = ModelCarrinho::remover($userId, $produtoId);
        $result['total_itens'] = ModelCarrinho::contar($userId);
        jsonResponse($result);
        break;

    case 'limpar':
        $result = ModelCarrinho::limpar($userId);
        jsonResponse($result);
        break;

    case 'contar':
        $total = ModelCarrinho::contar($userId);
        jsonResponse(['success' => true, 'total_itens' => $total]);
        break;

    case 'checkout':
    case 'finalizar':
        $metodo       = $_POST['metodo_pagamento'] ?? 'cartao';
        $stripeToken  = $_POST['stripe_token'] ?? null;
        $morada       = trim($_POST['morada'] ?? '');
        $cidade       = trim($_POST['cidade'] ?? '');
        $codigoPostal = trim($_POST['codigo_postal'] ?? '');
        $telefone     = trim($_POST['telefone'] ?? '');
        $codigoPromo  = $_POST['codigo_promo'] ?? '';

        $result = ModelCarrinho::criarEncomenda($userId, $stripeToken, $metodo, $morada, $cidade, $codigoPostal, $telefone);

        if ($result['success']) {
            jsonResponse($result);
        } else {
            jsonResponse($result, 400);
        }
        break;

    case 'encomendas':
        $encomendas = ModelCarrinho::getEncomendas($userId);
        jsonResponse(['success' => true, 'encomendas' => $encomendas]);
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Ação inválida.'], 400);
}
