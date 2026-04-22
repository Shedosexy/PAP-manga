<?php
// ════════════════════════════════════════════════════════
//  MangaVerse — Error Handler
//  Captura erros fatais e redireciona para erro.php
// ════════════════════════════════════════════════════════

set_exception_handler(function (Throwable $e) {
    // Log do erro (não expor detalhes ao utilizador)
    error_log('[MangaVerse] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

    // Se já foram enviados headers (output já começou), não redirecionar
    if (headers_sent()) {
        echo '<script>window.location.href="erro.php?code=500";</script>';
        exit;
    }

    http_response_code(500);
    header('Location: erro.php?code=500');
    exit;
});

set_error_handler(function (int $severity, string $message, string $file, int $line) {
    // Converter errors fatais em exceções
    if ($severity & (E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR)) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
        // Warnings e notices: só logar
    error_log("[MangaVerse Warning] {$message} in {$file}:{$line}");
    return true;
});

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
        error_log('[MangaVerse Fatal] ' . $error['message'] . ' in ' . $error['file'] . ':' . $error['line']);
        if (!headers_sent()) {
            header('Location: erro.php?code=500');
            exit;
        }
    }
});
