<?php
// ════════════════════════════════════════════════════════
//  MangaVerse — Configuração & Helpers
// ════════════════════════════════════════════════════════

// ── Conexão PDO (singleton) ──────────────────────────────
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=localhost;dbname=mangaverse_db;charset=utf8mb4';
        $pdo = new PDO($dsn, 'root', '', [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

// ── Sessão ───────────────────────────────────────────────
function initSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// ── Utilizador autenticado ───────────────────────────────
function isLoggedIn(): bool {
    initSession();
    return !empty($_SESSION['user_id']);
}

function getLoggedUser(): ?array {
    initSession();
    if (!isLoggedIn()) return null;
    return [
        'id'    => $_SESSION['user_id'],
        'nome'  => $_SESSION['user_nome']  ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'role'  => $_SESSION['user_role']  ?? 'cliente',
    ];
}

// ── Autorização por role ─────────────────────────────────
function requireRole(array $roles): void {
    initSession();
    if (!isLoggedIn() || !in_array($_SESSION['user_role'] ?? '', $roles, true)) {
        jsonResponse(['success' => false, 'message' => 'Acesso negado.'], 403);
    }
}

function requireAdmin(string $redirect = ''): void {
    initSession();
    if (!isLoggedIn() || ($_SESSION['user_role'] ?? '') !== 'admin') {
        if ($redirect) {
            header('Location: ' . $redirect . 'login.php');
            exit;
        }
        jsonResponse(['success' => false, 'message' => 'Acesso negado.'], 403);
    }
}

// ── Resposta JSON ────────────────────────────────────────
function jsonResponse(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Compatibilidade: manter $conn para código legado ─────
$conn = new mysqli('localhost', 'root', '', 'mangaverse_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>