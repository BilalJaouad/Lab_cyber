<?php
// ============================================================
//  dashboard.php — Tableau de bord (selon rôle)
//  P01 · XSS Stocké · Plateforme Universitaire · EMINES 2026
// ============================================================
require_once 'config.php';
require_once 'layout.php';
requireLogin();

$user = currentUser();
$role = $user['role'];
$uid  = $user['user_id'];

// ── Statistiques selon le rôle ──────────────────────────────
$stats = [];

if ($role === 'admin') {
    $stats['Utilisateurs']  = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $stats['Professeurs']   = $pdo->query("SELECT COUNT(*) FROM users WHERE role='professeur'")->fetchColumn();
    $stats['Étudiants']     = $pdo->query("SELECT COUNT(*) FROM users WHERE role='etudiant'")->fetchColumn();
    $stats['Messages total']= $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
}

if ($role === 'professeur') {
    $stats['Cours assurés']  = $pdo->query("SELECT COUNT(*) FROM emploi_du_temps WHERE professeur_id=$uid")->fetchColumn();
    $stats['Messages reçus'] = $pdo->query("SELECT COUNT(*) FROM messages WHERE destinataire_id=$uid")->fetchColumn();
    $stats['Non lus']        = $pdo->query("SELECT COUNT(*) FROM messages WHERE destinataire_id=$uid AND lu=0")->fetchColumn();
}

if ($role === 'etudiant') {
    $stmt = $pdo->prepare("SELECT AVG(note) FROM notes WHERE etudiant_id=?");
    $stmt->execute([$uid]);
    $avg = number_format((float)$stmt->fetchColumn(), 2);
    $stats['Moyenne générale'] = "$avg / 20";

    $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE destinataire_id=? AND lu=0");
    $stmt2->execute([$uid]);
    $stats['Messages non lus'] = $stmt2->fetchColumn();
}

// ── Messages récents pour tous ───────────────────────────────
$stmt = $pdo->prepare(
    "SELECT m.sujet, m.date_envoi, m.lu,
            u.nom, u.prenom, u.role AS exp_role
     FROM messages m
     JOIN users u ON u.id = m.expediteur_id
     WHERE m.destinataire_id = ?
     ORDER BY m.date_envoi DESC
     LIMIT 5"
);
$stmt->execute([$uid]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

renderHeader("Tableau de bord");
?>
<div class="container">
  <h1>Tableau de bord
    <small style="font-size:.7em;color:#546e7a;margin-left:.5rem;">
      — <?= htmlspecialchars(labelRole($role)) ?>
    </small>
  </h1>

  <!-- Statistiques -->
  <?php if (!empty($stats)): ?>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:1.5rem;">
    <?php foreach ($stats as $label => $value): ?>
    <div class="card" style="text-align:center;padding:1.2rem;">
      <div style="font-size:2rem;font-weight:700;color:#1a237e;"><?= htmlspecialchars((string)$value) ?></div>
      <div style="color:#546e7a;font-size:.88rem;margin-top:.3rem;"><?= htmlspecialchars($label) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- Accès rapides -->
  <div class="card">
    <h2>Accès rapides</h2>
    <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-top:.8rem;">
      <a href="/notes.php"          class="btn btn-primary">📊 Notes</a>
      <a href="/emploi_du_temps.php" class="btn btn-primary">📅 Emploi du temps</a>
      <a href="/messagerie.php"     class="btn btn-primary">✉️ Messagerie</a>
      <?php if ($role === 'admin'): ?>
      <a href="/admin.php"          class="btn btn-danger">⚙️ Administration</a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Messages récents -->
  <div class="card">
    <h2>Messages récents</h2>
    <?php if (empty($messages)): ?>
      <p style="color:#90a4ae;">Aucun message reçu.</p>
    <?php else: ?>
    <table>
      <thead>
        <tr><th>De</th><th>Sujet</th><th>Date</th><th>Statut</th></tr>
      </thead>
      <tbody>
        <?php foreach ($messages as $msg): ?>
        <tr>
          <td><?= htmlspecialchars($msg['prenom'] . ' ' . $msg['nom']) ?>
              <span style="font-size:.75rem;color:#90a4ae;">(<?= htmlspecialchars(labelRole($msg['exp_role'])) ?>)</span>
          </td>
          <td><a href="/messagerie.php"><?= htmlspecialchars($msg['sujet']) ?></a></td>
          <td><?= htmlspecialchars($msg['date_envoi']) ?></td>
          <td><?= $msg['lu'] ? '<span style="color:#2e7d32;">Lu</span>' : '<strong style="color:#c62828;">Non lu</strong>' ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>
<?php renderFooter(); ?>
