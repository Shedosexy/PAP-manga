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

function canManageMarketplaceProduct(array $product, ?array $user = null): bool {
    $user = $user ?? getLoggedUser();
    if (!$user) {
        return false;
    }

    $role = $user['role'] ?? 'cliente';
    if ($role === 'admin') {
        return true;
    }

    if ($role !== 'vendedor') {
        return false;
    }

    return (int) ($product['vendedor_id'] ?? 0) === (int) ($user['id'] ?? 0);
}

// ── Resposta JSON ────────────────────────────────────────
function jsonResponse(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function normalizeProductImagePath(?string $image): ?string {
    $image = trim((string) $image);
    if ($image === '') {
        return null;
    }

    $image = str_replace('\\', '/', $image);
    while (strpos($image, './') === 0) {
        $image = substr($image, 2);
    }

    $image = ltrim($image, '/');

    if (preg_match('#^https?://#i', $image)) {
        return $image;
    }

    if (strpos($image, 'assets/') === 0) {
        return $image;
    }

    if (strpos($image, 'images/') === 0) {
        return 'assets/' . $image;
    }

    return 'assets/images/' . $image;
}

function saveUploadedProductImage(array $file): ?string {
    if (empty($file) || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Erro ao enviar a imagem.');
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Upload inválido.');
    }

    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new RuntimeException('A imagem não pode ter mais de 5 MB.');
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions, true)) {
        throw new RuntimeException('Formato de imagem inválido. Usa JPG, PNG, WEBP ou GIF.');
    }

    $mime = mime_content_type($file['tmp_name']) ?: '';
    if (strpos($mime, 'image/') !== 0) {
        throw new RuntimeException('O ficheiro enviado não é uma imagem válida.');
    }

    $targetDir = dirname(__DIR__) . '/images';
    if (!is_dir($targetDir) && !mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
        throw new RuntimeException('Não foi possível preparar a pasta de imagens.');
    }

    $baseName = strtolower(pathinfo($file['name'], PATHINFO_FILENAME));
    $baseName = preg_replace('/[^a-z0-9]+/i', '-', $baseName);
    $baseName = trim((string) $baseName, '-');
    if ($baseName === '') {
        $baseName = 'manga';
    }

    $fileName = sprintf('%s-%s.%s', $baseName, date('YmdHis'), $extension);
    $targetPath = $targetDir . '/' . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new RuntimeException('Não foi possível guardar a imagem enviada.');
    }

    return 'assets/images/' . $fileName;
}

function getProductCatalogFallbacks(): array {
    static $fallbacks = [
        'One Piece' => [
            'imagem' => 'assets/images/one piece vol 104.jpg',
            'descricao' => 'A aventura épica de Monkey D. Luffy para se tornar o Rei dos Piratas.',
            'volume' => 'Vol. 104',
        ],
        'Jujutsu Kaisen' => [
            'imagem' => 'assets/images/jujutsu kaisen vol 24.jpg',
            'descricao' => 'Yuji Itadori junta-se à escola de feiticeiros para combater maldições.',
            'volume' => 'Vol. 24',
        ],
        'Chainsaw Man' => [
            'imagem' => 'assets/images/chainsaw man vol16.jpg',
            'descricao' => 'Denji funde-se com o seu demónio motosserra para caçar demónios.',
            'volume' => 'Vol. 16',
        ],
        'Berserk' => [
            'imagem' => 'assets/images/berserk vol 41.jpg',
            'descricao' => 'A jornada sombria do espadachim Guts num mundo medieval.',
            'volume' => 'Vol. 41',
        ],
        'Attack on Titan' => [
            'imagem' => 'assets/images/attack on titan vol 32.jpg',
            'descricao' => 'A humanidade luta pela sobrevivência contra titãs gigantes.',
            'volume' => 'Vol. 34',
        ],
        'Demon Slayer' => [
            'imagem' => 'assets/images/demon slayer vol 23.jpg',
            'descricao' => 'Tanjiro embarca numa jornada para curar a sua irmã e vingar a sua família.',
            'volume' => 'Vol. 23',
        ],
        'Vinland Saga' => [
            'imagem' => 'assets/images/vinland saga vol 27.jpg',
            'descricao' => 'A saga viking de Thorfinn na era dos exploradores nórdicos.',
            'volume' => 'Vol. 27',
        ],
        'Tokyo Ghoul' => [
            'imagem' => 'assets/images/tokyo ghoul vol 14.jpg',
            'descricao' => 'Ken Kaneki torna-se meio-ghoul após um encontro fatídico.',
            'volume' => 'Vol. 14',
        ],
        'Blue Period' => [
            'imagem' => 'assets/images/blue period vol 14.jpg',
            'descricao' => 'Um jovem descobre a sua paixão pela arte e luta para entrar na universidade.',
            'volume' => 'Vol. 14',
        ],
    ];

    return $fallbacks;
}

function applyProductCatalogFallbacks(array $product): array {
    $nome = trim((string)($product['nome'] ?? ''));
    $fallbacks = getProductCatalogFallbacks();

    if ($nome === '' || !isset($fallbacks[$nome])) {
        return $product;
    }

    $fallback = $fallbacks[$nome];

    foreach (['imagem', 'descricao', 'volume'] as $field) {
        if ((empty($product[$field]) || $product[$field] === null) && !empty($fallback[$field])) {
            $product[$field] = $fallback[$field];
        }
    }

    if (!empty($product['imagem'])) {
        $product['imagem'] = normalizeProductImagePath($product['imagem']);
    }

    return $product;
}

// ── Compatibilidade: manter $conn para código legado ─────
$conn = new mysqli('localhost', 'root', '', 'mangaverse_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>