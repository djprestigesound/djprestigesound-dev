<?php
session_start();
require_once 'db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['email']) || empty(trim($_POST['email']))) {
        $error = "Veuillez entrer votre email.";
    } else {
        $email = trim($_POST['email']);
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user) {
            // Générer un token de réinitialisation
            $token = bin2hex(random_bytes(16));
            // Enregistrer le token (et sa date d'expiration) dans la base – à adapter à votre schéma
            $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = ?");
            $stmt->execute([$token, $user['id']]);
            // Envoyer l'email de réinitialisation (simulation)
            $message = "Lien de réinitialisation : https://djprestigesound.com/reset_password.php?token=$token";
            // mail($email, "Réinitialisation de mot de passe", $message);
            $success = "Un email de réinitialisation a été envoyé à votre adresse.";
        } else {
            $error = "Aucun utilisateur trouvé avec cet email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mot de passe oublié</title>
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
<h1>Mot de passe oublié</h1>
<form method="POST" action="">
    <input type="email" name="email" placeholder="Votre email" required>
    <button type="submit">Envoyer</button>
</form>
<div class="message">
    <?php if(isset($error)) echo "<p style='color: #dc3545;'>$error</p>"; ?>
    <?php if(isset($success)) echo "<p style='color: #28a745;'>$success</p>"; ?>
</div>
<a href="login.php">Retour à la connexion</a>
</body>
</html>
