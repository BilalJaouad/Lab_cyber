<?php
// ============================================================
//  config.php — Connexion base de données
//  P01 · XSS Stocké · Plateforme Universitaire · EMINES 2026
// ============================================================

define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_NAME', getenv('DB_NAME') ?: 'universite');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: 'root');

// Démarrage de session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
