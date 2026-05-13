<?php
// ============================================================
//  config.php — Connexion base de données
//  P01 · XSS Stocké · Plateforme Universitaire · EMINES 2026
// ============================================================

define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_NAME', getenv('DB_NAME') ?: 'universite');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: 'root');

// ✅ FIX 1 — Cookie HttpOnly : document.cookie retourne vide même si XSS passe
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Strict',
        'secure'   => false,
    ]);
    session_start();
}

// ✅ FIX 2 — En-têtes sécurité (CSP bloque JS inline, style-src garde le CSS intact)
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; object-src 'none';");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("Referrer-Policy: strict-origin-when-cross-origin");

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
