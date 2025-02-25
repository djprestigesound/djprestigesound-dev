<?php
session_start();
require_once 'db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['email'], $_POST['password'])) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            // Rediriger selon le rôle
            if ($user['role'] === 'admin') {
                header("Location: dashboard-admin.php");
            } elseif ($user['role'] === 'prestataire') {
                header("Location: espace-prestataire.php");
            } elseif ($user['role'] === 'client') {
                header("Location: espace-client.php");
            } else {
                header("Location: index.html");
            }
            exit;
        } else {
            $error = "Email ou mot de passe incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion | DJ Prestige Sound</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: 'Inter', sans-serif; background: #111; color: #fff; text-align: center; padding: 20px; }
        form { max-width: 400px; margin: 20px auto; background: #222; padding: 20px; border-radius: 8px; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: none; border-radius: 5px; }
        button { padding: 10px 20px; background: #FFA564; border: none; border-radius: 5px; cursor: pointer; }
        .message { margin-top: 20px; }
    </style>
</head>
<body>
<h1>Connexion</h1>
<form method="POST" action="">
    <input type="email" name="email" placeholder="Votre email" required>
    <input type="password" name="password" placeholder="Votre mot de passe" required>
    <button type="submit">Se connecter</button>
</form>
<div class="message">
    <?php if(isset($error)) echo "<p style='color: #dc3545;'>$error</p>"; ?>
</div>
<a href="forgot_password.php">Mot de passe oublié ?</a> | <a href="register.php">Créer un compte</a>
</body>
</html>
