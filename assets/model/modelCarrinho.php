<?php
// ════════════════════════════════════════════════════════
//  Model — Carrinho & Encomendas
// ════════════════════════════════════════════════════════
require_once __DIR__ . '/../config/database.php';

class ModelCarrinho {

    /**
     * Obter carrinho do utilizador
     */
    public static function getCarrinho($userId) {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT c.*, p.nome, p.autor, p.preco, p.volume, p.cor1, p.cor2,
                   p.imagem, cat.slug AS tipo
            FROM carrinho c
            JOIN produtos p ON c.produto_id = p.id
            JOIN categorias cat ON p.categoria_id = cat.id
            WHERE c.utilizador_id = ?
            ORDER BY c.criado_em DESC
        ");
        $stmt->execute([$userId]);
        return array_map('applyProductCatalogFallbacks', $stmt->fetchAll());
    }

    /**
     * Adicionar ao carrinho
     */
    public static function adicionar($userId, $produtoId, $quantidade = 1) {
        $db = getDB();

        // Verificar se o produto existe
        $stmt = $db->prepare("SELECT id, stock FROM produtos WHERE id = ? AND ativo = 1");
        $stmt->execute([$produtoId]);
        $produto = $stmt->fetch();
        if (!$produto) {
            return ['success' => false, 'message' => 'Produto não encontrado.'];
        }

        // Validar se há stock suficiente
        if ($quantidade > $produto['stock']) {
            return ['success' => false, 'message' => 'Stock insuficiente. Disponível: ' . $produto['stock'] . ' unidades.'];
        }

        // UPSERT: inserir ou atualizar quantidade
        $stmt = $db->prepare("
            INSERT INTO carrinho (utilizador_id, produto_id, quantidade)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE quantidade = quantidade + VALUES(quantidade)
        ");
        $stmt->execute([$userId, $produtoId, $quantidade]);

        return ['success' => true, 'message' => 'Produto adicionado ao carrinho!'];
    }

    /**
     * Atualizar quantidade
     */
    public static function atualizarQtd($userId, $produtoId, $quantidade) {
        $db = getDB();
        if ($quantidade <= 0) {
            return self::remover($userId, $produtoId);
        }
        $stmt = $db->prepare("UPDATE carrinho SET quantidade = ? WHERE utilizador_id = ? AND produto_id = ?");
        $stmt->execute([$quantidade, $userId, $produtoId]);
        return ['success' => true, 'message' => 'Quantidade atualizada.'];
    }

    /**
     * Remover do carrinho
     */
    public static function remover($userId, $produtoId) {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM carrinho WHERE utilizador_id = ? AND produto_id = ?");
        $stmt->execute([$userId, $produtoId]);
        return ['success' => true, 'message' => 'Produto removido do carrinho.'];
    }

    /**
     * Limpar carrinho
     */
    public static function limpar($userId) {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM carrinho WHERE utilizador_id = ?");
        $stmt->execute([$userId]);
        return ['success' => true];
    }

    /**
     * Contar itens no carrinho
     */
    public static function contar($userId) {
        $db = getDB();
        $stmt = $db->prepare("SELECT COALESCE(SUM(quantidade), 0) AS total FROM carrinho WHERE utilizador_id = ?");
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Criar encomenda
     */
    public static function criarEncomenda($userId, $stripePaymentId = null, $metodo = 'cartao', $morada = '', $cidade = '', $codigoPostal = '', $telefone = '') {
        $db = getDB();
        $carrinho = self::getCarrinho($userId);

        if (empty($carrinho)) {
            return ['success' => false, 'message' => 'Carrinho vazio.'];
        }

        $subtotal = 0;
        foreach ($carrinho as $item) {
            $subtotal += $item['preco'] * $item['quantidade'];
        }

        $envio = $subtotal > 30 ? 0 : 3.99;
        $iva = $subtotal * 0.23;
        $total = $subtotal + $envio;

        $db->beginTransaction();
        try {
            // Criar encomenda
            $stmt = $db->prepare("
                INSERT INTO encomendas (utilizador_id, subtotal, envio, iva, total, metodo_pagamento, stripe_payment_id, morada, cidade, codigo_postal, telefone, estado)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pago')
            ");
            $stmt->execute([$userId, $subtotal, $envio, $iva, $total, $metodo, $stripePaymentId, $morada, $cidade, $codigoPostal, $telefone]);
            $encomendaId = $db->lastInsertId();

            // Inserir itens
            $stmtItem = $db->prepare("
                INSERT INTO encomenda_itens (encomenda_id, produto_id, quantidade, preco_unitario)
                VALUES (?, ?, ?, ?)
            ");
            foreach ($carrinho as $item) {
                $stmtItem->execute([$encomendaId, $item['produto_id'], $item['quantidade'], $item['preco']]);
            }

            // Limpar carrinho
            self::limpar($userId);

            $db->commit();
            return [
                'success'      => true,
                'message'      => 'Encomenda criada com sucesso!',
                'encomenda_id' => $encomendaId,
                'total'        => $total
            ];
        } catch (Exception $e) {
            $db->rollBack();
            return ['success' => false, 'message' => 'Erro ao criar encomenda.'];
        }
    }

    /**
     * Obter encomendas do utilizador
     */
    public static function getEncomendas($userId) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM encomendas WHERE utilizador_id = ? ORDER BY criado_em DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Histórico detalhado de compras com estado das avaliações.
     */
    public static function getHistoricoDetalhado($userId) {
        $db = getDB();
        $stmt = $db->prepare(
            "SELECT
                e.id AS encomenda_id,
                e.subtotal,
                e.envio,
                e.desconto,
                e.total,
                e.estado,
                e.metodo_pagamento,
                e.criado_em AS encomenda_criada_em,
                ei.id AS item_id,
                ei.produto_id,
                ei.quantidade,
                ei.preco_unitario,
                p.nome,
                p.autor,
                p.volume,
                p.imagem,
                p.cor1,
                p.cor2,
                av.classificacao AS minha_classificacao,
                av.comentario AS meu_comentario,
                av.criado_em AS minha_avaliacao_em,
                ratings.rating_media,
                COALESCE(ratings.rating_total, 0) AS rating_total
             FROM encomendas e
             INNER JOIN encomenda_itens ei ON ei.encomenda_id = e.id
             LEFT JOIN produtos p ON p.id = ei.produto_id
             LEFT JOIN produto_avaliacoes av
                ON av.produto_id = ei.produto_id
               AND av.utilizador_id = e.utilizador_id
             LEFT JOIN (
                SELECT produto_id,
                       ROUND(AVG(classificacao), 1) AS rating_media,
                       COUNT(*) AS rating_total
                FROM produto_avaliacoes
                GROUP BY produto_id
             ) ratings ON ratings.produto_id = ei.produto_id
             WHERE e.utilizador_id = ?
             ORDER BY e.criado_em DESC, ei.id DESC"
        );
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll();

        $historico = [];

        foreach ($rows as $row) {
            $encomendaId = (int) $row['encomenda_id'];

            if (!isset($historico[$encomendaId])) {
                $historico[$encomendaId] = [
                    'id' => $encomendaId,
                    'subtotal' => (float) $row['subtotal'],
                    'envio' => (float) $row['envio'],
                    'desconto' => (float) $row['desconto'],
                    'total' => (float) $row['total'],
                    'estado' => $row['estado'],
                    'metodo_pagamento' => $row['metodo_pagamento'],
                    'criado_em' => $row['encomenda_criada_em'],
                    'itens' => [],
                ];
            }

            $item = [
                'item_id' => (int) $row['item_id'],
                'produto_id' => (int) $row['produto_id'],
                'nome' => $row['nome'] ?? 'Produto indisponível',
                'autor' => $row['autor'] ?? 'Desconhecido',
                'volume' => $row['volume'] ?? '',
                'imagem' => $row['imagem'] ?? null,
                'cor1' => $row['cor1'] ?? '#0a0a0a',
                'cor2' => $row['cor2'] ?? '#e8002d',
                'quantidade' => (int) $row['quantidade'],
                'preco_unitario' => (float) $row['preco_unitario'],
                'minha_classificacao' => $row['minha_classificacao'] !== null ? (int) $row['minha_classificacao'] : null,
                'meu_comentario' => $row['meu_comentario'] ?? '',
                'minha_avaliacao_em' => $row['minha_avaliacao_em'] ?? null,
                'rating_media' => $row['rating_media'] !== null ? (float) $row['rating_media'] : null,
                'rating_total' => (int) $row['rating_total'],
            ];

            $item = applyProductCatalogFallbacks($item);
            $item['pode_avaliar'] = $item['minha_classificacao'] === null
                && in_array($row['estado'], ['pago', 'enviado', 'entregue'], true);

            $historico[$encomendaId]['itens'][] = $item;
        }

        return array_values($historico);
    }
}
