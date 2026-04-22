<?php
// ════════════════════════════════════════════════════════
//  Controller — Mangás / Produtos
//  Endpoint: assets/controller/controllerMangas.php
//  Ações: listar, detalhe, criar, categorias
// ════════════════════════════════════════════════════════
require_once __DIR__ . '/../model/modelMangas.php';

header('Content-Type: application/json; charset=utf-8');

$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

switch ($acao) {
    case 'listar':
        $filtros = [
            'categoria' => $_GET['categoria'] ?? '',
            'badge'     => $_GET['badge'] ?? '',
            'pesquisa'  => $_GET['pesquisa'] ?? '',
            'preco_min' => $_GET['preco_min'] ?? '',
            'preco_max' => $_GET['preco_max'] ?? '',
            'condicao'  => $_GET['condicao'] ?? '',
            'ordenar'   => $_GET['ordenar'] ?? 'recente',
        ];
        $produtos = ModelMangas::getAll($filtros);
        jsonResponse(['success' => true, 'produtos' => $produtos, 'total' => count($produtos)]);
        break;

    case 'detalhe':
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            jsonResponse(['success' => false, 'message' => 'ID inválido.'], 400);
        }
        $produto = ModelMangas::getById($id);
        if (!$produto) {
            jsonResponse(['success' => false, 'message' => 'Produto não encontrado.'], 404);
        }
        jsonResponse(['success' => true, 'produto' => $produto]);
        break;

    case 'criar':
        requireRole(['vendedor', 'admin']);

        $nome = trim($_POST['nome'] ?? '');
        $preco = floatval($_POST['preco'] ?? 0);
        $categoriaId = intval($_POST['categoria_id'] ?? 0);

        if (empty($nome) || $preco <= 0 || $categoriaId <= 0) {
            jsonResponse(['success' => false, 'message' => 'Dados obrigatórios em falta.'], 400);
        }

        $dados = [
            'nome'         => $nome,
            'autor'        => trim($_POST['autor'] ?? 'Desconhecido'),
            'descricao'    => trim($_POST['descricao'] ?? ''),
            'categoria_id' => $categoriaId,
            'preco'        => $preco,
            'preco_antigo' => !empty($_POST['preco_antigo']) ? floatval($_POST['preco_antigo']) : null,
            'stock'        => intval($_POST['stock'] ?? 1),
            'volume'       => trim($_POST['volume'] ?? ''),
            'badge'        => $_POST['badge'] ?? null,
            'cor1'         => $_POST['cor1'] ?? '#0a0a0a',
            'cor2'         => $_POST['cor2'] ?? '#e8002d',
            'condicao'     => $_POST['condicao'] ?? 'novo',
            'condicao_pct' => intval($_POST['condicao_pct'] ?? 100),
            'vendedor_id'  => $_SESSION['user_id'],
        ];

        $id = ModelMangas::criar($dados);
        jsonResponse(['success' => true, 'message' => 'Produto criado com sucesso!', 'id' => $id]);
        break;

    case 'categorias':
        $categorias = ModelMangas::getCategorias();
        $contagem = ModelMangas::contarPorCategoria();
        jsonResponse(['success' => true, 'categorias' => $categorias, 'contagem' => $contagem]);
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Ação inválida.'], 400);
}
