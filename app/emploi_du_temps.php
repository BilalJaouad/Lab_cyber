<?php
// ============================================================
//  emploi_du_temps.php — Emploi du temps
//  P01 · XSS Stocké · Plateforme Universitaire · EMINES 2026
// ============================================================
require_once 'config.php';
require_once 'layout.php';
requireLogin();

$user = currentUser();
$role = $user['role'];
$uid  = $user['user_id'];

// Tous les rôles voient l'emploi du temps complet
$edt = $pdo->query(
    "SELECT e.jour, e.heure_debut, e.heure_fin, e.matiere, e.salle,
            u.nom, u.prenom
     FROM emploi_du_temps e
     JOIN users u ON u.id = e.professeur_id
     ORDER BY FIELD(e.jour,'Lundi','Mardi','Mercredi','Jeudi','Vendredi'), e.heure_debut"
)->fetchAll(PDO::FETCH_ASSOC);

// Grouper par jour
$parJour = [];
foreach ($edt as $cours) {
    $parJour[$cours['jour']][] = $cours;
}

$jours = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi'];
$colors = [
    'Mathématiques' => '#e3f2fd',
    'Informatique'  => '#e8f5e9',
    'Physique'      => '#fff3e0',
    'Anglais'       => '#fce4ec',
];

renderHeader("Emploi du temps");
?>
<div class="container">
  <h1>📅 Emploi du temps — Semestre 1</h1>

  <div class="card">
    <table>
      <thead>
        <tr>
          <th style="width:120px;">Jour</th>
          <th>Horaire</th>
          <th>Matière</th>
          <th>Salle</th>
          <th>Enseignant</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($jours as $jour): ?>
          <?php if (!empty($parJour[$jour])): ?>
            <?php foreach ($parJour[$jour] as $i => $cours): ?>
            <tr style="background:<?= $colors[$cours['matiere']] ?? '#fff' ?>;">
              <?php if ($i === 0): ?>
              <td rowspan="<?= count($parJour[$jour]) ?>"
                  style="font-weight:700;color:#1a237e;vertical-align:middle;text-align:center;
                         border-right:2px solid #1a237e;">
                <?= htmlspecialchars($jour) ?>
              </td>
              <?php endif; ?>
              <td style="font-family:monospace;">
                <?= substr($cours['heure_debut'],0,5) ?> – <?= substr($cours['heure_fin'],0,5) ?>
              </td>
              <td style="font-weight:600;"><?= htmlspecialchars($cours['matiere']) ?></td>
              <td><?= htmlspecialchars($cours['salle']) ?></td>
              <td><?= htmlspecialchars($cours['prenom'] . ' ' . $cours['nom']) ?></td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
          <tr>
            <td style="font-weight:700;color:#1a237e;text-align:center;"><?= $jour ?></td>
            <td colspan="4" style="color:#90a4ae;font-style:italic;">Pas de cours</td>
          </tr>
          <?php endif; ?>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php renderFooter(); ?>
