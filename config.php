<?php
// config.php

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Génère et retourne un token CSRF.
 * Si un token existe déjà dans la session, il est réutilisé.
 *
 * @return string Le token CSRF.
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valide un token CSRF fourni par rapport à celui stocké en session.
 *
 * @param string $token Le token à vérifier.
 * @return bool Vrai si le token est valide, faux sinon.
 */
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
