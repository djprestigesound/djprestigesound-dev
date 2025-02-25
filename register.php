<?php
session_start();
require_once 'db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['email'], $_POST['password'], $_POST['confirm_password'])) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm = $_POST['confirm_password'];
        if ($password !== $confirm) {
            $error = "Les mots de passe ne correspondent pas.";
        } else {
            $pdo = getPDO();
            // Vérifier si l'email existe déjà
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "Cet email est déjà utilisé.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                // Enregistrer l'utilisateur avec le rôle 'client' par défaut
                $stmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, 'client')");
                if ($stmt->execute([$email, $hash])) {
                    $success = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
                } else {
                    $error = "Erreur lors de l'inscription.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription | DJ Prestige Sound</title>
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
<h1>Inscription</h1>
<form method="POST" action="">
    <input type="email" name="email" placeholder="Votre email" required>
    <input type="password" name="password" placeholder="Votre mot de passe" required>
    <input type="password" name="confirm_password" placeholder="Confirmez votre mot de passe" required>
    <button type="submit">S'inscrire</button>
</form>
<div class="message">
    <?php if(isset($error)) echo "<p style='color: #dc3545;'>$error</p>"; ?>
    <?php if(isset($success)) echo "<p style='color: #28a745;'>$success</p>"; ?>
</div>
<a href="login.php">Déjà inscrit ? Connectez-vous ici</a>
</body>
</html>
