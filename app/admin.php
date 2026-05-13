<?php
// ============================================================
//  admin.php — Panneau d'administration (admin uniquement)
//  P01 · XSS Stocké · Plateforme Universitaire · EMINES 2026
// ============================================================
require_once 'config.php';
require_once 'layout.php';
requireRole(['admin']);

$success = '';
$error   = '';

// Ajout d'un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_user') {
    $nom    = trim($_POST['nom']    ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email  = trim($_POST['email']  ?? '');
    $pass   = trim($_POST['password'] ?? '');
    $role   = $_POST['role'] ?? 'etudiant';

    if ($nom && $prenom && $email && $pass && in_array($role, ['admin','professeur','etudiant'])) {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO users (nom, prenom, email, password, role) VALUES (?,?,?,MD5(?),?)"
            );
            $stmt->execute([$nom, $prenom, $email, $pass, $role]);
            $success = "Utilisateur créé avec succès.";
        } catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    } else {
        $error = "Tous les champs sont obligatoires.";
    }
}

// Suppression
if (isset($_GET['delete']) && (int)$_GET['delete'] > 1) {
    $pdo->prepare("DELETE FROM users WHERE id=?")->execute([(int)$_GET['delete']]);
    header("Location: /admin.php?deleted=1");
    exit;
}

$users = $pdo->query("SELECT * FROM users ORDER BY role, nom")->fetchAll(PDO::FETCH_ASSOC);
$msgs  = $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();

renderHeader("Administration");
?>
<div class="container">
  <h1>⚙️ Administration</h1>

  <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
  <?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Utilisateur supprimé.</div><?php endif; ?>

  <!-- Stats rapides -->
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem;">
    <?php
    $counts = [
      'Total'       => count($users),
      'Admins'      => count(array_filter($users, fn($u)=>$u['role']==='admin')),
      'Professeurs' => count(array_filter($users, fn($u)=>$u['role']==='professeur')),
      'Étudiants'   => count(array_filter($users, fn($u)=>$u['role']==='etudiant')),
    ];
    foreach ($counts as $label => $val): ?>
    <div class="card" style="text-align:center;padding:1rem;">
      <div style="font-size:2rem;font-weight:700;color:#1a237e;"><?= $val ?></div>
      <div style="color:#546e7a;font-size:.85rem;"><?= $label ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Ajouter un utilisateur -->
  <div class="card">
    <h2>Ajouter un utilisateur</h2>
    <form method="POST" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
      <input type="hidden" name="action" value="add_user">
      <div class="form-group">
        <label>Nom</label>
        <input type="text" name="nom" placeholder="Nom de famille" required>
      </div>
      <div class="form-group">
        <label>Prénom</label>
        <input type="text" name="prenom" placeholder="Prénom" required>
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" placeholder="email@uni.ma" required>
      </div>
      <div class="form-group">
        <label>Mot de passe</label>
        <input type="text" name="password" placeholder="Mot de passe initial" required>
      </div>
      <div class="form-group">
        <label>Rôle</label>
        <select name="role">
          <option value="etudiant">Étudiant</option>
          <option value="professeur">Professeur</option>
          <option value="admin">Administrateur</option>
        </select>
      </div>
      <div class="form-group" style="align-self:end;">
        <button type="submit" class="btn btn-primary">Créer l'utilisateur</button>
      </div>
    </form>
  </div>

  <!-- Liste des utilisateurs -->
  <div class="card">
    <h2>Utilisateurs (<?= count($users) ?>)</h2>
    <table>
      <thead>
        <tr><th>#</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Créé le</th><th>Action</th></tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <?php
          $roleClass = match($u['role']) {
            'admin'       => 'role-admin',
            'professeur'  => 'role-prof',
            default       => 'role-etud',
          };
        ?>
        <tr>
          <td><?= $u['id'] ?></td>
          <td><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><span class="<?= $roleClass ?>"><?= htmlspecialchars(labelRole($u['role'])) ?></span></td>
          <td><?= htmlspecialchars($u['date_creation']) ?></td>
          <td>
            <?php if ($u['id'] > 1): ?>
            <a href="/admin.php?delete=<?= $u['id'] ?>"
               class="btn btn-danger btn-sm"
               onclick="return confirm('Supprimer cet utilisateur ?')">Supprimer</a>
            <?php else: ?>
            <span style="color:#b0bec5;font-size:.8rem;">Protégé</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php renderFooter(); ?>
