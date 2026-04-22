<?php
// ════════════════════════════════════════════════════════
//  Model — Contacto
// ════════════════════════════════════════════════════════
require_once __DIR__ . '/../config/database.php';

class ModelContacto {

    /**
     * Enviar mensagem de contacto
     */
    public static function enviar($nome, $email, $assunto, $mensagem) {
        $db = getDB();
        $stmt = $db->prepare("
            INSERT INTO contactos (nome, email, assunto, mensagem)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$nome, $email, $assunto, $mensagem]);
        return ['success' => true, 'message' => 'Mensagem enviada com sucesso! Responderemos brevemente.'];
    }

    /**
     * Listar mensagens (admin)
     */
    public static function listar() {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM contactos ORDER BY criado_em DESC");
        return $stmt->fetchAll();
    }
}
