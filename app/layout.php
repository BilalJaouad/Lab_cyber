<?php
// layout.php — En-tête et pied de page communs
// Inclure APRÈS config.php

function renderHeader(string $pageTitle = 'UniPortail'): void {
    $user = currentUser();
    $role = $user['role'] ?? '';
    $nom  = htmlspecialchars(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? ''));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?> · UniPortail</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body   { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f2f5; color: #1a1a2e; min-height: 100vh; }
    /* ── Barre de navigation ── */
    nav    { background: #1a237e; color: #fff; display: flex; align-items: center;
             justify-content: space-between; padding: 0 2rem; height: 60px; }
    nav .brand { font-size: 1.3rem; font-weight: 700; letter-spacing: 1px; }
    nav .brand span { color: #90caf9; }
    nav ul { list-style: none; display: flex; gap: 1.2rem; }
    nav ul li a { color: #cfd8dc; text-decoration: none; font-size: .92rem; padding: .4rem .7rem;
                  border-radius: 4px; transition: background .2s; }
    nav ul li a:hover, nav ul li a.active { background: rgba(255,255,255,.15); color: #fff; }
    nav .user-info { font-size: .85rem; color: #b0bec5; display: flex; align-items: center; gap: 1rem; }
    nav .user-info .badge { background: #283593; padding: .2rem .6rem; border-radius: 12px;
                             color: #90caf9; font-size: .75rem; font-weight: 600; }
    nav .user-info a { color: #ef9a9a; font-size: .82rem; text-decoration: none; }
    nav .user-info a:hover { text-decoration: underline; }
    /* ── Contenu principal ── */
    .container { max-width: 1100px; margin: 2rem auto; padding: 0 1.5rem; }
    h1  { font-size: 1.6rem; margin-bottom: 1.2rem; color: #1a237e; border-left: 4px solid #1a237e;
          padding-left: .8rem; }
    h2  { font-size: 1.2rem; margin-bottom: .8rem; color: #283593; }
    /* ── Cartes ── */
    .card { background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,.08);
            padding: 1.5rem; margin-bottom: 1.5rem; }
    /* ── Tables ── */
    table { width: 100%; border-collapse: collapse; font-size: .9rem; }
    th    { background: #1a237e; color: #fff; padding: .7rem 1rem; text-align: left; }
    td    { padding: .65rem 1rem; border-bottom: 1px solid #e8eaf6; }
    tr:hover td { background: #e8eaf6; }
    /* ── Formulaires ── */
    label { display: block; margin-bottom: .3rem; font-weight: 600; font-size: .88rem; color: #37474f; }
    input[type=text], input[type=email], input[type=password], select, textarea {
        width: 100%; padding: .55rem .8rem; border: 1px solid #b0bec5; border-radius: 5px;
        font-size: .9rem; transition: border .2s; }
    input:focus, select:focus, textarea:focus { outline: none; border-color: #1a237e; }
    textarea { resize: vertical; min-height: 120px; }
    .form-group { margin-bottom: 1rem; }
    /* ── Boutons ── */
    .btn { display: inline-block; padding: .5rem 1.2rem; border-radius: 5px; border: none;
           cursor: pointer; font-size: .9rem; font-weight: 600; transition: background .2s; }
    .btn-primary  { background: #1a237e; color: #fff; }
    .btn-primary:hover  { background: #283593; }
    .btn-danger   { background: #c62828; color: #fff; }
    .btn-danger:hover   { background: #b71c1c; }
    .btn-sm { padding: .3rem .8rem; font-size: .8rem; }
    /* ── Alertes ── */
    .alert { padding: .8rem 1rem; border-radius: 5px; margin-bottom: 1rem; font-size: .9rem; }
    .alert-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
    .alert-error   { background: #ffebee; color: #c62828; border: 1px solid #ef9a9a; }
    /* ── Badge rôle ── */
    .role-admin { color: #7b1fa2; font-weight: 700; }
    .role-prof  { color: #1565c0; font-weight: 700; }
    .role-etud  { color: #2e7d32; font-weight: 700; }
    /* ── Responsive ── */
    @media (max-width: 700px) { nav ul { display: none; } }
  </style>
</head>
<body>
<nav>
  <div class="brand">Uni<span>Portail</span></div>
  <?php if (!empty($user['user_id'])): ?>
  <ul>
    <li><a href="/dashboard.php">Tableau de bord</a></li>
    <?php if (in_array($role, ['etudiant','professeur','admin'])): ?>
    <li><a href="/notes.php">Notes</a></li>
    <li><a href="/emploi_du_temps.php">Emploi du temps</a></li>
    <li><a href="/messagerie.php">Messagerie</a></li>
    <?php endif; ?>
    <?php if ($role === 'admin'): ?>
    <li><a href="/admin.php">Admin</a></li>
    <?php endif; ?>
  </ul>
  <div class="user-info">
    <span class="badge"><?= htmlspecialchars(labelRole($role)) ?></span>
    <span><?= $nom ?></span>
    <a href="/logout.php">Déconnexion</a>
  </div>
  <?php endif; ?>
</nav>
<?php
}

function renderFooter(): void {
?>
<footer style="text-align:center;padding:1.5rem;color:#90a4ae;font-size:.8rem;margin-top:2rem;">
  UniPortail · EMINES UM6P · 2026 &nbsp;|&nbsp;
  <span style="color:#ef9a9a;">⚠ Application pédagogique — ne pas déployer en production</span>
</footer>
</body>
</html>
<?php
}
