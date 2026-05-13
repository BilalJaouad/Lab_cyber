<?php
// ============================================================
//  index.php — Page de connexion
//  P01 · XSS Stocké · Plateforme Universitaire · EMINES 2026
// ============================================================
require_once 'config.php';

// Si déjà connecté → dashboard
if (!empty($_SESSION['user_id'])) {
    header("Location: /dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    // Requête préparée pour l'authentification
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND password = MD5(?)");
    $stmt->execute([$email, $password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nom']     = $user['nom'];
        $_SESSION['prenom']  = $user['prenom'];
        $_SESSION['email']   = $user['email'];
        $_SESSION['role']    = $user['role'];
        header("Location: /dashboard.php");
        exit;
    } else {
        $error = "Email ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Connexion · UniPortail</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', Arial, sans-serif; background: linear-gradient(135deg,#1a237e,#283593);
           min-height: 100vh; display: flex; align-items: center; justify-content: center; }
    .login-box { background: #fff; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,.25);
                 padding: 2.5rem; width: 380px; max-width: 95vw; }
    .login-box h1 { text-align: center; font-size: 1.8rem; color: #1a237e; margin-bottom: .3rem; }
    .login-box p.sub { text-align: center; color: #90a4ae; font-size: .88rem; margin-bottom: 1.8rem; }
    label { display: block; font-weight: 600; font-size: .85rem; color: #37474f; margin-bottom: .3rem; }
    input { width: 100%; padding: .6rem .9rem; border: 1px solid #b0bec5; border-radius: 6px;
            font-size: .92rem; margin-bottom: 1rem; }
    input:focus { outline: none; border-color: #1a237e; }
    .btn { width: 100%; padding: .7rem; background: #1a237e; color: #fff; font-size: 1rem;
           font-weight: 700; border: none; border-radius: 6px; cursor: pointer; transition: background .2s; }
    .btn:hover { background: #283593; }
    .error { background: #ffebee; color: #c62828; padding: .7rem; border-radius: 5px;
             font-size: .88rem; margin-bottom: 1rem; border: 1px solid #ef9a9a; }
    .comptes { margin-top: 1.5rem; background: #e8eaf6; border-radius: 6px; padding: 1rem;
               font-size: .8rem; color: #37474f; }
    .comptes strong { display: block; margin-bottom: .4rem; color: #1a237e; }
    .comptes table { width: 100%; border-collapse: collapse; }
    .comptes td { padding: .2rem .4rem; }
  </style>
</head>
<body>
<div class="login-box">
  <h1>UniPortail</h1>
  <p class="sub">Plateforme universitaire EMINES UM6P</p>

  <?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <label for="email">Adresse e-mail</label>
    <input type="email" id="email" name="email" placeholder="utilisateur@uni.ma"
           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
    <label for="password">Mot de passe</label>
    <input type="password" id="password" name="password" placeholder="••••••••" required>
    <button type="submit" class="btn">Se connecter</button>
  </form>

  <!-- Comptes de test affichés pour faciliter la démo pédagogique -->
  <div class="comptes">
    <strong>Comptes de test :</strong>
    <table>
      <tr><td><b>Admin</b></td>    <td>admin@uni.ma</td>      <td>Admin@2026</td></tr>
      <tr><td><b>Prof 1</b></td>   <td>ybenali@uni.ma</td>    <td>Prof@2026</td></tr>
      <tr><td><b>Prof 2</b></td>   <td>souali@uni.ma</td>     <td>Prof@2026</td></tr>
      <tr><td><b>Étudiant 1</b></td><td>kalami@uni.ma</td>    <td>Etud@2026</td></tr>
      <tr><td><b>Étudiant 2</b></td><td>fchraibi@uni.ma</td>  <td>Etud@2026</td></tr>
      <tr><td><b>Étudiant 3</b></td><td>oidrissi@uni.ma</td>  <td>Etud@2026</td></tr>
    </table>
  </div>
</div>
</body>
</html>
