<?php
require_once __DIR__ . '/../config/database.php';

class ModelAvaliacoes {

    public static function getSiteResumo(bool $fallbackVisual = true): array {
        $db = getDB();
        $row = $db->query(
            "SELECT COUNT(*) AS total, ROUND(AVG(classificacao), 1) AS media
             FROM produto_avaliacoes"
        )->fetch();

        $total = (int) ($row['total'] ?? 0);
        $media = $total > 0
            ? (float) $row['media']
            : ($fallbackVisual ? 4.9 : 0.0);

        return [
            'media' => $media,
            'total' => $total,
        ];
    }

    public static function getProdutoResumo(int $produtoId): array {
        $db = getDB();
        $stmt = $db->prepare(
            "SELECT COUNT(*) AS total, ROUND(AVG(classificacao), 1) AS media
             FROM produto_avaliacoes
             WHERE produto_id = ?"
        );
        $stmt->execute([$produtoId]);
        $row = $stmt->fetch();

        $total = (int) ($row['total'] ?? 0);

        return [
            'media' => $total > 0 ? (float) $row['media'] : null,
            'total' => $total,
        ];
    }

    public static function utilizadorJaAvaliou(int $userId, int $produtoId): bool {
        $db = getDB();
        $stmt = $db->prepare(
            "SELECT 1
             FROM produto_avaliacoes
             WHERE utilizador_id = ? AND produto_id = ?
             LIMIT 1"
        );
        $stmt->execute([$userId, $produtoId]);

        return (bool) $stmt->fetchColumn();
    }

    public static function podeAvaliar(int $userId, int $produtoId): bool {
        return self::getEligibleOrderId($userId, $produtoId) !== null
            && !self::utilizadorJaAvaliou($userId, $produtoId);
    }

    public static function criar(int $userId, int $produtoId, int $classificacao, string $comentario = ''): array {
        if ($produtoId <= 0) {
            return ['success' => false, 'message' => 'Produto inválido.'];
        }

        if ($classificacao < 1 || $classificacao > 5) {
            return ['success' => false, 'message' => 'A classificação tem de estar entre 1 e 5 estrelas.'];
        }

        $encomendaId = self::getEligibleOrderId($userId, $produtoId);
        if ($encomendaId === null) {
            return ['success' => false, 'message' => 'Só podes avaliar produtos que compraste.'];
        }

        if (self::utilizadorJaAvaliou($userId, $produtoId)) {
            return ['success' => false, 'message' => 'Este produto já foi avaliado por ti.'];
        }

        $comentario = trim($comentario);
        if ($comentario !== '') {
            $comentario = substr($comentario, 0, 500);
        }

        $db = getDB();
        $stmt = $db->prepare(
            "INSERT INTO produto_avaliacoes (produto_id, utilizador_id, encomenda_id, classificacao, comentario)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $produtoId,
            $userId,
            $encomendaId,
            $classificacao,
            $comentario !== '' ? $comentario : null,
        ]);

        return [
            'success' => true,
            'message' => 'Avaliação registada com sucesso.',
            'classificacao' => $classificacao,
            'comentario' => $comentario,
            'produto_resumo' => self::getProdutoResumo($produtoId),
            'site_resumo' => self::getSiteResumo(false),
        ];
    }

    private static function getEligibleOrderId(int $userId, int $produtoId): ?int {
        $db = getDB();
        $stmt = $db->prepare(
            "SELECT e.id
             FROM encomendas e
             INNER JOIN encomenda_itens ei ON ei.encomenda_id = e.id
             WHERE e.utilizador_id = ?
               AND ei.produto_id = ?
               AND e.estado IN ('pago', 'enviado', 'entregue')
             ORDER BY e.criado_em DESC
             LIMIT 1"
        );
        $stmt->execute([$userId, $produtoId]);
        $encomendaId = $stmt->fetchColumn();

        return $encomendaId ? (int) $encomendaId : null;
    }
}