<?php
// ============================================================
//  notes.php — Consultation des notes
//  P01 · XSS Stocké · Plateforme Universitaire · EMINES 2026
// ============================================================
require_once 'config.php';
require_once 'layout.php';
requireLogin();

$user = currentUser();
$role = $user['role'];
$uid  = $user['user_id'];

// ── Requête selon le rôle ────────────────────────────────────
if ($role === 'etudiant') {
    // L'étudiant ne voit que ses propres notes
    $stmt = $pdo->prepare(
        "SELECT n.matiere, n.note, n.semestre
         FROM notes n WHERE n.etudiant_id = ?
         ORDER BY n.semestre, n.matiere"
    );
    $stmt->execute([$uid]);
    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Moyenne
    $stmt2 = $pdo->prepare("SELECT AVG(note) FROM notes WHERE etudiant_id=?");
    $stmt2->execute([$uid]);
    $moyenne = number_format((float)$stmt2->fetchColumn(), 2);

} elseif ($role === 'professeur' || $role === 'admin') {
    // Profs et admin voient tous les étudiants
    $notes = $pdo->query(
        "SELECT u.nom, u.prenom, n.matiere, n.note, n.semestre
         FROM notes n
         JOIN users u ON u.id = n.etudiant_id
         ORDER BY u.nom, u.prenom, n.semestre, n.matiere"
    )->fetchAll(PDO::FETCH_ASSOC);
}

renderHeader("Notes");
?>
<div class="container">
  <h1>📊 Relevé de notes</h1>

  <?php if ($role === 'etudiant'): ?>
  <!-- Vue étudiant : ses propres notes + moyenne -->
  <div class="card" style="text-align:center;padding:1.2rem;max-width:250px;">
    <div style="font-size:2.5rem;font-weight:700;color:<?= $moyenne >= 10 ? '#2e7d32' : '#c62828' ?>;">
      <?= $moyenne ?> / 20
    </div>
    <div style="color:#546e7a;font-size:.9rem;">Moyenne générale — S1</div>
  </div>

  <div class="card">
    <h2>Semestre 1</h2>
    <table>
      <thead><tr><th>Matière</th><th>Note / 20</th><th>Mention</th></tr></thead>
      <tbody>
        <?php foreach ($notes as $n): ?>
        <?php
          $note = (float)$n['note'];
          $mention = match(true) {
              $note >= 16 => 'Très Bien',
              $note >= 14 => 'Bien',
              $note >= 12 => 'Assez Bien',
              $note >= 10 => 'Passable',
              default     => 'Insuffisant',
          };
          $color = $note >= 10 ? '#2e7d32' : '#c62828';
        ?>
        <tr>
          <td><?= htmlspecialchars($n['matiere']) ?></td>
          <td style="font-weight:700;color:<?= $color ?>"><?= number_format($note,2) ?></td>
          <td><?= $mention ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php else: ?>
  <!-- Vue professeur / admin : tous les étudiants -->
  <div class="card">
    <table>
      <thead><tr><th>Étudiant</th><th>Matière</th><th>Note / 20</th><th>Semestre</th></tr></thead>
      <tbody>
        <?php foreach ($notes as $n): ?>
        <?php $color = (float)$n['note'] >= 10 ? '#2e7d32' : '#c62828'; ?>
        <tr>
          <td><?= htmlspecialchars($n['prenom'] . ' ' . $n['nom']) ?></td>
          <td><?= htmlspecialchars($n['matiere']) ?></td>
          <td style="font-weight:700;color:<?= $color ?>"><?= number_format((float)$n['note'],2) ?></td>
          <td><?= htmlspecialchars($n['semestre']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php renderFooter(); ?>
