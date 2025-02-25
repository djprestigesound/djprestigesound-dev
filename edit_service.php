<?php
/***********************************************************
 * edit_service.php
 * Formulaire pour modifier un service existant
 ***********************************************************/

// On démarre la session
session_start();

// On inclut db.php pour la connexion PDO et config.php pour le token CSRF (si utilisé)
require_once 'db.php';
require_once 'config.php';

// Vérification du rôle admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$pdo = getPDO();
$error = null;
$service = null;

// Si le formulaire est soumis (méthode POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id'], $_POST['category'], $_POST['name'], $_POST['description'], $_POST['price'], $_POST['csrf_token'])) {
        $error = "Données invalides.";
    } elseif (!validateCsrfToken($_POST['csrf_token'])) {
        $error = "Token CSRF invalide.";
    } else {
        // Mise à jour
        $id = intval($_POST['id']);
        $category = trim($_POST['category']);
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = floatval($_POST['price']);

        $stmt = $pdo->prepare("UPDATE services SET category = ?, name = ?, description = ?, price = ? WHERE id = ?");
        if ($stmt->execute([$category, $name, $description, $price, $id])) {
            header("Location: services_admin.php");
            exit;
        } else {
            $error = "Erreur lors de la mise à jour.";
        }
    }
} else {
    // Sinon, on récupère l’ID en GET pour pré-remplir le formulaire
    if (!isset($_GET['id'])) {
        die("ID du service non spécifié.");
    }
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$id]);
    $service = $stmt->fetch();
    if (!$service) {
        die("Service introuvable.");
    }
}

// Génération du token CSRF
$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Modifier un Service</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
    h1 { color: #FF8C42; }
    label { display: block; margin-top: 10px; }
    input, textarea { width: 100%; padding: 8px; margin-top: 5px; }
    button, a { background: #FF8C42; color: #fff; padding: 6px 12px; text-decoration: none; border: none; border-radius: 4px; cursor: pointer; }
    button:hover, a:hover { background: #FFA564; }
    .error { color: red; }
  </style>
</head>
<body>

<h1>Modifier un Service</h1>

<?php if ($error): ?>
  <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if ($service): ?>
<form method="POST" action="">
  <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
  <input type="hidden" name="id" value="<?= htmlspecialchars($service['id']) ?>">

  <label>Catégorie :</label>
  <input type="text" name="category" value="<?= htmlspecialchars($service['category']) ?>" required>

  <label>Nom :</label>
  <input type="text" name="name" value="<?= htmlspecialchars($service['name']) ?>" required>

  <label>Description :</label>
  <textarea name="description" rows="4"><?= htmlspecialchars($service['description']) ?></textarea>

  <label>Prix :</label>
  <input type="number" name="price" step="0.01" value="<?= htmlspecialchars($service['price']) ?>">

  <br><br>
  <button type="submit">Enregistrer</button>
</form>
<?php endif; ?>

<p><a href="services_admin.php">⬅ Retour</a></p>

</body>
</html>
