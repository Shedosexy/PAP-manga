<?php
// ════════════════════════════════════════════════════════
//  produto.php — API JSON para detalhes de produto
// ════════════════════════════════════════════════════════
require_once 'assets/config/database.php';

header('Content-Type: application/json; charset=utf-8');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID inválido.']);
    exit;
}

try {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT p.*, c.nome AS categoria_nome, c.slug AS categoria_slug,
               u.nome AS vendedor_nome
        FROM produtos p
        JOIN categorias c ON p.categoria_id = c.id
        LEFT JOIN utilizadores u ON p.vendedor_id = u.id
        WHERE p.id = ? AND p.ativo = 1
    ");
    $stmt->execute([$id]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$produto) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Produto não encontrado.']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'produto' => [
            'id'             => (int)$produto['id'],
            'nome'           => $produto['nome'],
            'autor'          => $produto['autor'],
            'descricao'      => $produto['descricao'] ?? '',
            'categoria_nome' => $produto['categoria_nome'],
            'categoria_slug' => $produto['categoria_slug'],
            'preco'          => (float)$produto['preco'],
            'preco_antigo'   => $produto['preco_antigo'] ? (float)$produto['preco_antigo'] : null,
            'stock'          => (int)$produto['stock'],
            'volume'         => $produto['volume'] ?? '',
            'badge'          => $produto['badge'] ?? null,
            'cor1'           => $produto['cor1'] ?? '#0a0a0a',
            'cor2'           => $produto['cor2'] ?? '#e8002d',
            'condicao'       => $produto['condicao'] ?? 'novo',
            'condicao_pct'   => (int)($produto['condicao_pct'] ?? 100),
            'vendedor_nome'  => $produto['vendedor_nome'] ?? null,
            'criado_em'      => $produto['criado_em'] ?? null,
        ],
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno.']);
}