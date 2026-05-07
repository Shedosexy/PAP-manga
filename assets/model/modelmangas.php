<?php
// ════════════════════════════════════════════════════════
//  Model — Mangás / Produtos
// ════════════════════════════════════════════════════════
require_once __DIR__ . '/../config/database.php';

class ModelMangas {

    /**
     * Obter todos os produtos (com filtros opcionais)
     */
    public static function getAll($filtros = []) {
        $db = getDB();
        $where = ["p.ativo = 1"];
        $params = [];

        if (!empty($filtros['categoria'])) {
            $where[] = "c.slug = ?";
            $params[] = $filtros['categoria'];
        }

        if (!empty($filtros['badge'])) {
            $where[] = "p.badge = ?";
            $params[] = $filtros['badge'];
        }

        if (!empty($filtros['pesquisa'])) {
            $where[] = "(p.nome LIKE ? OR p.autor LIKE ?)";
            $params[] = '%' . $filtros['pesquisa'] . '%';
            $params[] = '%' . $filtros['pesquisa'] . '%';
        }

        if (!empty($filtros['preco_min'])) {
            $where[] = "p.preco >= ?";
            $params[] = $filtros['preco_min'];
        }

        if (!empty($filtros['preco_max'])) {
            $where[] = "p.preco <= ?";
            $params[] = $filtros['preco_max'];
        }

        if (!empty($filtros['condicao'])) {
            $where[] = "p.condicao = ?";
            $params[] = $filtros['condicao'];
        }

        $whereSQL = implode(' AND ', $where);

        $orderBy = "p.criado_em DESC";
        if (!empty($filtros['ordenar'])) {
            switch ($filtros['ordenar']) {
                case 'preco_asc':  $orderBy = "p.preco ASC"; break;
                case 'preco_desc': $orderBy = "p.preco DESC"; break;
                case 'nome':       $orderBy = "p.nome ASC"; break;
                case 'recente':    $orderBy = "p.criado_em DESC"; break;
            }
        }

         $sql = "SELECT p.*, c.nome AS categoria_nome, c.slug AS categoria_slug,
                  u.nome AS vendedor_nome,
                  ratings.rating_media,
                  COALESCE(ratings.rating_total, 0) AS rating_total
                FROM produtos p
                JOIN categorias c ON p.categoria_id = c.id
                LEFT JOIN utilizadores u ON p.vendedor_id = u.id
              LEFT JOIN (
                  SELECT produto_id,
                      ROUND(AVG(classificacao), 1) AS rating_media,
                      COUNT(*) AS rating_total
                  FROM produto_avaliacoes
                  GROUP BY produto_id
              ) ratings ON ratings.produto_id = p.id
                WHERE {$whereSQL}
                ORDER BY {$orderBy}";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return array_map('applyProductCatalogFallbacks', $stmt->fetchAll());
    }

    /**
     * Obter produto por ID
     */
    public static function getById($id) {
        $db = getDB();
        $stmt = $db->prepare("
                 SELECT p.*, c.nome AS categoria_nome, c.slug AS categoria_slug,
                     u.nome AS vendedor_nome,
                     ratings.rating_media,
                     COALESCE(ratings.rating_total, 0) AS rating_total
            FROM produtos p
            JOIN categorias c ON p.categoria_id = c.id
            LEFT JOIN utilizadores u ON p.vendedor_id = u.id
                 LEFT JOIN (
                  SELECT produto_id,
                      ROUND(AVG(classificacao), 1) AS rating_media,
                      COUNT(*) AS rating_total
                  FROM produto_avaliacoes
                  GROUP BY produto_id
                 ) ratings ON ratings.produto_id = p.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $produto = $stmt->fetch();

        return $produto ? applyProductCatalogFallbacks($produto) : $produto;
    }

    /**
     * Criar novo produto (para marketplace)
     */
    public static function criar($dados) {
        $db = getDB();
        $stmt = $db->prepare("
            INSERT INTO produtos (nome, autor, descricao, categoria_id, preco, preco_antigo, stock, volume, badge, cor1, cor2, condicao, condicao_pct, vendedor_id, imagem)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $dados['nome'],
            $dados['autor'] ?? 'Desconhecido',
            $dados['descricao'] ?? '',
            $dados['categoria_id'],
            $dados['preco'],
            $dados['preco_antigo'] ?? null,
            $dados['stock'] ?? 1,
            $dados['volume'] ?? null,
            $dados['badge'] ?? null,
            $dados['cor1'] ?? '#0a0a0a',
            $dados['cor2'] ?? '#e8002d',
            $dados['condicao'] ?? 'novo',
            $dados['condicao_pct'] ?? 100,
            $dados['vendedor_id'] ?? null,
            $dados['imagem'] ?? null
        ]);
        return $db->lastInsertId();
    }

    public static function atualizar($id, $dados) {
        $db = getDB();
        $stmt = $db->prepare("\n            UPDATE produtos\n            SET nome = ?, autor = ?, descricao = ?, categoria_id = ?, preco = ?, preco_antigo = ?, stock = ?, volume = ?, condicao = ?, condicao_pct = ?, imagem = ?\n            WHERE id = ? AND ativo = 1\n        ");

        return $stmt->execute([
            $dados['nome'],
            $dados['autor'] ?? 'Desconhecido',
            $dados['descricao'] ?? '',
            $dados['categoria_id'],
            $dados['preco'],
            $dados['preco_antigo'] ?? null,
            $dados['stock'] ?? 1,
            $dados['volume'] ?? null,
            $dados['condicao'] ?? 'novo',
            $dados['condicao_pct'] ?? 100,
            $dados['imagem'] ?? null,
            $id,
        ]);
    }

    public static function eliminar($id) {
        $db = getDB();
        $stmt = $db->prepare("UPDATE produtos SET ativo = 0 WHERE id = ? AND ativo = 1");
        return $stmt->execute([$id]);
    }

    public static function getCategoriaIdBySlug($slug) {
        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM categorias WHERE slug = ? LIMIT 1");
        $stmt->execute([$slug]);
        $id = $stmt->fetchColumn();
        return $id ? (int) $id : 0;
    }

    /**
     * Obter categorias
     */
    public static function getCategorias() {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM categorias ORDER BY nome");
        return $stmt->fetchAll();
    }

    /**
     * Contar produtos por categoria
     */
    public static function contarPorCategoria() {
        $db = getDB();
        $stmt = $db->query("
            SELECT c.slug, c.nome, COUNT(p.id) AS total
            FROM categorias c
            LEFT JOIN produtos p ON p.categoria_id = c.id AND p.ativo = 1
            GROUP BY c.id
        ");
        return $stmt->fetchAll();
    }
}
