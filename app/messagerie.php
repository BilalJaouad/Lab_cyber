<?php
// ============================================================
//  messagerie.php — Messagerie interne
//  P01 · XSS Stocké · Plateforme Universitaire · EMINES 2026
//
//  ⚠️  VULNÉRABILITÉ INTENTIONNELLE — XSS STOCKÉ
//  Le corps du message est affiché directement dans le HTML
//  sans aucun échappement (htmlspecialchars manquant).
//  Un attaquant peut injecter du JavaScript malveillant.
// ============================================================
require_once 'config.php';
require_once 'layout.php';
requireLogin();

$user = currentUser();
$uid  = $user['user_id'];
$role = $user['role'];

$success = '';
$error   = '';

// ── Envoi d'un nouveau message ─────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'envoyer') {
    $dest_id = (int)($_POST['destinataire_id'] ?? 0);
    $sujet   = trim($_POST['sujet'] ?? '');
    $corps   = trim($_POST['corps'] ?? '');  // ⚠️ PAS DE SANITISATION DU CORPS

    if ($dest_id && $sujet !== '' && $corps !== '') {
        // ⚠️ VULNÉRABILITÉ : le corps est stocké tel quel en base de données
        //    sans aucun filtre ni échappement du contenu HTML/JavaScript
        $stmt = $pdo->prepare(
            "INSERT INTO messages (expediteur_id, destinataire_id, sujet, corps)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$uid, $dest_id, htmlspecialchars($sujet), $corps]);
        $success = "Message envoyé avec succès.";
    } else {
        $error = "Tous les champs sont obligatoires.";
    }
}

// ── Marquer un message comme lu ────────────────────────────
if (isset($_GET['lire'])) {
    $mid = (int)$_GET['lire'];
    $pdo->prepare("UPDATE messages SET lu=1 WHERE id=? AND destinataire_id=?")
        ->execute([$mid, $uid]);
    header("Location: /messagerie.php?msg=$mid");
    exit;
}

// ── Récupérer la liste des utilisateurs (pour la liste déroulante) ──
$users = $pdo->query(
    "SELECT id, nom, prenom, role FROM users ORDER BY role, nom"
)->fetchAll(PDO::FETCH_ASSOC);

// ── Boîte de réception ─────────────────────────────────────
$inbox = $pdo->prepare(
    "SELECT m.id, m.sujet, m.date_envoi, m.lu,
            u.nom, u.prenom, u.role AS exp_role
     FROM messages m
     JOIN users u ON u.id = m.expediteur_id
     WHERE m.destinataire_id = ?
     ORDER BY m.date_envoi DESC"
);
$inbox->execute([$uid]);
$inbox = $inbox->fetchAll(PDO::FETCH_ASSOC);

// ── Lecture d'un message sélectionné ───────────────────────
$currentMsg = null;
if (isset($_GET['msg'])) {
    $mid = (int)$_GET['msg'];
    $stmt = $pdo->prepare(
        "SELECT m.*, u.nom, u.prenom, u.role AS exp_role
         FROM messages m
         JOIN users u ON u.id = m.expediteur_id
         WHERE m.id = ? AND m.destinataire_id = ?"
    );
    $stmt->execute([$mid, $uid]);
    $currentMsg = $stmt->fetch(PDO::FETCH_ASSOC);
}

renderHeader("Messagerie");
?>
<div class="container">
  <h1>✉️ Messagerie</h1>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div style="display:grid;grid-template-columns:320px 1fr;gap:1.5rem;align-items:start;">

    <!-- ── Panneau gauche : boîte de réception ─────────── -->
    <div>
      <div class="card" style="padding:1rem;">
        <h2 style="margin-bottom:.8rem;">Boîte de réception</h2>
        <?php if (empty($inbox)): ?>
          <p style="color:#90a4ae;font-size:.9rem;">Aucun message.</p>
        <?php else: ?>
          <?php foreach ($inbox as $msg): ?>
          <a href="/messagerie.php?msg=<?= $msg['id'] ?>&lire=<?= $msg['id'] ?>"
             style="display:block;padding:.6rem .8rem;border-radius:5px;margin-bottom:.4rem;
                    background:<?= $msg['lu'] ? '#f5f5f5' : '#e8eaf6' ?>;
                    border-left:3px solid <?= $msg['lu'] ? '#b0bec5' : '#1a237e' ?>;
                    text-decoration:none;color:inherit;">
            <div style="font-weight:<?= $msg['lu'] ? '400' : '700' ?>;font-size:.9rem;">
              <?= htmlspecialchars($msg['sujet']) ?>
            </div>
            <div style="font-size:.78rem;color:#546e7a;">
              De : <?= htmlspecialchars($msg['prenom'].' '.$msg['nom']) ?> ·
              <?= date('d/m H:i', strtotime($msg['date_envoi'])) ?>
            </div>
          </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- ── Panneau droit : lecture + composition ────────── -->
    <div>

      <?php if ($currentMsg): ?>
      <!-- Lecture du message sélectionné -->
      <div class="card">
        <h2><?= htmlspecialchars($currentMsg['sujet']) ?></h2>
        <div style="font-size:.85rem;color:#546e7a;margin-bottom:1rem;">
          De : <strong><?= htmlspecialchars($currentMsg['prenom'].' '.$currentMsg['nom']) ?></strong>
          (<?= htmlspecialchars(labelRole($currentMsg['exp_role'])) ?>) ·
          <?= htmlspecialchars($currentMsg['date_envoi']) ?>
        </div>
        <hr style="border:none;border-top:1px solid #e0e0e0;margin-bottom:1rem;">

        <!--
          ╔══════════════════════════════════════════════════════════╗
          ║  ⚠️  VULNÉRABILITÉ XSS STOCKÉ — LIGNE CI-DESSOUS       ║
          ║  Le corps du message est injecté directement dans le    ║
          ║  DOM sans htmlspecialchars() ni aucune sanitisation.    ║
          ║  Tout script <script>...</script> s'exécutera ici.      ║
          ╚══════════════════════════════════════════════════════════╝
        -->
        <div class="message-body" style="line-height:1.7;white-space:pre-wrap;">
          <?= $currentMsg['corps'] ?>
        </div>

      </div>
      <?php endif; ?>

      <!-- Formulaire de composition -->
      <div class="card">
        <h2>Nouveau message</h2>
        <form method="POST">
          <input type="hidden" name="action" value="envoyer">
          <div class="form-group">
            <label for="destinataire_id">Destinataire</label>
            <select name="destinataire_id" id="destinataire_id" required>
              <option value="">— Sélectionner —</option>
              <?php foreach ($users as $u): ?>
                <?php if ($u['id'] == $uid) continue; ?>
                <option value="<?= $u['id'] ?>">
                  <?= htmlspecialchars($u['prenom'].' '.$u['nom']) ?>
                  (<?= htmlspecialchars(labelRole($u['role'])) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="sujet">Sujet</label>
            <input type="text" name="sujet" id="sujet" placeholder="Objet du message" required>
          </div>
          <div class="form-group">
            <label for="corps">Message</label>
            <!--
              ⚠️ Ce champ accepte du HTML/JavaScript.
              Le contenu est stocké non-filtré dans la BDD,
              puis réaffiché sans échappement à tous les destinataires.
            -->
            <textarea name="corps" id="corps" placeholder="Votre message..." required></textarea>
          </div>
          <button type="submit" class="btn btn-primary">Envoyer</button>
        </form>
      </div>

    </div>
  </div><!-- /grid -->
</div>
<?php renderFooter(); ?>
