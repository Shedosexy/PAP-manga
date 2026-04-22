<?php
// ════════════════════════════════════════════════════════
//  Model — Suporte (Tickets)
// ════════════════════════════════════════════════════════
require_once __DIR__ . '/../config/database.php';

class ModelSuporte {

    /**
     * Criar ticket de suporte
     */
    public static function criarTicket($dados) {
        $db = getDB();
        $stmt = $db->prepare("
            INSERT INTO suporte_tickets (utilizador_id, nome, email, categoria, prioridade, assunto, mensagem)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $dados['utilizador_id'] ?? null,
            $dados['nome'],
            $dados['email'],
            $dados['categoria'] ?? 'outro',
            $dados['prioridade'] ?? 'media',
            $dados['assunto'],
            $dados['mensagem']
        ]);

        return [
            'success'   => true,
            'message'   => 'Ticket criado com sucesso! Receberás uma resposta em breve.',
            'ticket_id' => $db->lastInsertId()
        ];
    }

    /**
     * Obter tickets do utilizador
     */
    public static function getTickets($userId) {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT * FROM suporte_tickets
            WHERE utilizador_id = ?
            ORDER BY criado_em DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Obter todos os tickets (admin)
     */
    public static function getAllTickets() {
        $db = getDB();
        $stmt = $db->query("
            SELECT t.*, u.nome AS utilizador_nome
            FROM suporte_tickets t
            LEFT JOIN utilizadores u ON t.utilizador_id = u.id
            ORDER BY
                CASE t.estado WHEN 'aberto' THEN 0 WHEN 'em_progresso' THEN 1 ELSE 2 END,
                t.criado_em DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Obter ticket por ID
     */
    public static function getTicket($ticketId) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM suporte_tickets WHERE id = ?");
        $stmt->execute([$ticketId]);
        return $stmt->fetch();
    }

    /**
     * Obter respostas de um ticket
     */
    public static function getRespostas($ticketId) {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT r.*, u.nome AS autor_nome
            FROM suporte_respostas r
            LEFT JOIN utilizadores u ON r.utilizador_id = u.id
            WHERE r.ticket_id = ?
            ORDER BY r.criado_em ASC
        ");
        $stmt->execute([$ticketId]);
        return $stmt->fetchAll();
    }

    /**
     * Adicionar resposta a um ticket
     */
    public static function responder($ticketId, $userId, $mensagem, $isAdmin = false) {
        $db = getDB();
        $stmt = $db->prepare("
            INSERT INTO suporte_respostas (ticket_id, utilizador_id, mensagem, is_admin)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$ticketId, $userId, $mensagem, $isAdmin ? 1 : 0]);

        // Update ticket state
        $estado = $isAdmin ? 'em_progresso' : 'aberto';
        $stmt2 = $db->prepare("UPDATE suporte_tickets SET estado = ? WHERE id = ?");
        $stmt2->execute([$estado, $ticketId]);

        return ['success' => true, 'message' => 'Resposta enviada.'];
    }

    /**
     * Fechar ticket
     */
    public static function fecharTicket($ticketId) {
        $db = getDB();
        $stmt = $db->prepare("UPDATE suporte_tickets SET estado = 'fechado' WHERE id = ?");
        $stmt->execute([$ticketId]);
        return ['success' => true, 'message' => 'Ticket fechado com sucesso.'];
    }

    /**
     * FAQ estáticas
     */
    public static function getFAQ() {
        return [
            ['pergunta' => 'Quanto tempo demora o envio?', 'resposta' => 'Os envios são processados em 24-48h úteis. Entregas em Portugal continental demoram 2-3 dias úteis.'],
            ['pergunta' => 'Posso devolver um produto?', 'resposta' => 'Sim, tens 14 dias para devolver qualquer produto em estado original. Os portes de devolução são gratuitos.'],
            ['pergunta' => 'Como faço o pagamento?', 'resposta' => 'Aceitamos Visa, Mastercard, MB Way e referência Multibanco através do Stripe.'],
            ['pergunta' => 'Os mangás são em português?', 'resposta' => 'Temos edições em português, inglês e japonês. A língua está indicada em cada produto.'],
            ['pergunta' => 'Como vendo no Marketplace?', 'resposta' => 'Regista-te, vai ao Marketplace e clica em "Vender agora". Preenche os dados do produto e publica o anúncio.'],
            ['pergunta' => 'Qual é a comissão do Marketplace?', 'resposta' => 'A comissão é de 5% do valor da venda. O pagamento é processado em 48h após confirmação de entrega.'],
        ];
    }
}
