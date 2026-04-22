<?php
// ════════════════════════════════════════════════════════
//  Model — Autenticação (Login / Registo)
// ════════════════════════════════════════════════════════
require_once __DIR__ . '/../config/database.php';

class ModelAuth {

    /**
     * Registar novo utilizador
     */
    public static function registar($nome, $email, $password) {
        $db = getDB();

        // Verificar se email já existe
        $stmt = $db->prepare("SELECT id FROM utilizadores WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Este email já está registado.'];
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare("INSERT INTO utilizadores (nome, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$nome, $email, $hash]);

        return ['success' => true, 'message' => 'Conta criada com sucesso!'];
    }

    /**
     * Login de utilizador
     */
    public static function login($email, $password) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM utilizadores WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Email ou password incorretos.'];
        }

        initSession();
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_nome']  = $user['nome'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role']  = $user['role'];

        return [
            'success' => true,
            'message' => 'Login efetuado com sucesso!',
            'user'    => [
                'id'   => $user['id'],
                'nome' => $user['nome'],
                'email'=> $user['email'],
                'role' => $user['role']
            ]
        ];
    }

    /**
     * Logout
     */
    public static function logout() {
        initSession();
        session_unset();
        session_destroy();
        return ['success' => true, 'message' => 'Sessão terminada.'];
    }

    /**
     * Obter perfil do utilizador
     */
    public static function getPerfil($userId) {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, nome, email, role, criado_em FROM utilizadores WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
}
