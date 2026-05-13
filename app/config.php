<?php
// ============================================================
//  config.php — Connexion base de données
//  P01 · XSS Stocké · Plateforme Universitaire · EMINES 2026
// ============================================================

define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_NAME', getenv('DB_NAME') ?: 'universite');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: 'root');

// ✅ CORRIGÉ : Cookie de session inaccessible depuis JavaScript (HttpOnly)
//    SameSite=Strict bloque l'envoi cross-site du cookie
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,          // ← le JS ne peut plus lire document.cookie
        'samesite' => 'Strict',      // ← bloque CSRF cross-origin
        'secure'   => false,         // mettre true si HTTPS
    ]);
    session_start();
}

// ✅ CORRIGÉ : En-têtes HTTP de sécurité
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
// Content-Security-Policy : n'autorise les scripts que depuis la même origine
header("Content-Security-Policy: default-src 'self'; script-src 'self'; object-src 'none';");

// Connexion PDO
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("<h2 style='color:red'>Erreur de connexion BDD : " . $e->getMessage() . "</h2>");
}

// ─── Helpers ─────────────────────────────────────────────────────────────────

function requireLogin() {
    if (empty($_SESSION['user_id'])) {
        header("Location: /index.php");
        exit;
    }
}

function requireRole(array $roles) {
    requireLogin();
    if (!in_array($_SESSION['role'], $roles)) {
        die("<h2 style='color:red'>Accès refusé.</h2>");
    }
}

function currentUser(): array {
    return $_SESSION ?? [];
}

// ─── Affichage du nom de rôle (FR) ───────────────────────────────────────────
function labelRole(string $role): string {
    return match($role) {
        'admin'       => 'Administrateur',
        'professeur'  => 'Professeur',
        'etudiant'    => 'Étudiant',
        default       => $role,
    };
}
