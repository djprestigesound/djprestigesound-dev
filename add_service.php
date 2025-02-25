<?php
session_start();
require_once 'db.php';
require_once 'config.php';

// Vérification de l'accès admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$pdo = getPDO();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = trim($_POST['category'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '0');

    // ✅ Vérification des champs obligatoires
    if (empty($category) || empty($name) || empty($price)) {
        $errors[] = "Veuillez remplir tous les champs obligatoires.";
    }

    // ✅ Vérification que le prix est bien un nombre valide
    if (!is_numeric($price) || floatval($price) < 0) {
        $errors[] = "Le prix doit être un nombre valide et positif.";
    }

    // ✅ Si aucune erreur, on insère dans la base de données
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO services (category, name, description, price, created_at) VALUES (:cat, :name, :descr, :price, NOW())");
            $stmt->execute([
                'cat' => $category,
                'name' => $name,
                'descr' => $description,
                'price' => $price
            ]);

            // ✅ Redirection après succès
            header("Location: services_admin.php?success=1");
            exit;
        } catch (PDOException $e) {
            $errors[] = "❌ Erreur lors de l'ajout du service : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ajouter un Service</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f4f4;
      color: #333;
      text-align: center;
      padding: 20px;
    }
    h1 {
      color: #444;
    }
    form {
      background: white;
      padding: 20px;
      max-width: 400px;
      margin: auto;
      border-radius: 5px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    label {
      display: block;
      margin-top: 10px;
      font-weight: bold;
    }
    input, select, textarea {
      width: 100%;
      padding: 8px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    textarea {
      height: 80px;
    }
    button {
      background: #28a745;
      color: white;
      border: none;
      padding: 10px;
      margin-top: 15px;
      cursor: pointer;
      width: 100%;
      border-radius: 4px;
    }
    button:hover {
      background: #218838;
    }
    .error {
      color: red;
      margin-bottom: 10px;
    }
    .success {
      color: green;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>

  <h1>Ajouter un Service</h1>

  <?php if (!empty($errors)) : ?>
    <div class="error"><?= implode("<br>", $errors) ?></div>
  <?php endif; ?>

  <form action="" method="POST">
    <label>Catégorie :</label>
    <select name="category" required>
      <option value="DJ, Son & Lumières">DJ, Son & Lumières</option>
      <option value="Photo & Vidéo">Photo & Vidéo</option>
      <option value="Cérémonie">Cérémonie</option>
      <option value="Organisation & Extras">Organisation & Extras</option>
      <option value="Transport">Transport</option>
    </select>

    <label>Nom :</label>
    <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">

    <label>Description :</label>
    <textarea name="description"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>

    <label>Prix (€) :</label>
    <input type="number" name="price" step="0.01" min="0" required value="<?= htmlspecialchars($_POST['price'] ?? '0') ?>">

    <button type="submit">Ajouter</button>
  </form>

</body>
</html>
