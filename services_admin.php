<?php
// Activation des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once 'db.php';
require_once 'config.php';

// Vérification du rôle admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Connexion à la base
$pdo = getPDO();

// Récupération de tous les services
$stmt = $pdo->query("SELECT * FROM services ORDER BY created_at DESC");
$services = $stmt->fetchAll();

// Génération d’un token CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Gestion des Services</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background: #111;
      color: #fff;
      text-align: center;
      padding: 20px;
    }
    .admin-container {
      background: #222;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(255, 140, 66, 0.3);
      width: 80%;
      max-width: 900px;
      margin: auto;
    }
    h1 {
      color: #FFA564;
    }
    .top-links a {
      background: #FF8C42;
      color: #111;
      font-weight: bold;
      text-decoration: none;
      border-radius: 4px;
      padding: 10px 15px;
      margin: 5px;
      display: inline-block;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      background: #333;
    }
    th, td {
      padding: 10px;
      border: 1px solid #FFA564;
      text-align: center;
    }
    th {
      background: #FF8C42;
      color: #111;
    }
    .actions a, .actions button {
      background: #FFA564;
      color: #111;
      padding: 6px 12px;
      text-decoration: none;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
  </style>
</head>
<body>

<div class="admin-container">
  <h1>Gestion des Services</h1>

  <div class="top-links">
    <a href="add_service.php">➕ Ajouter un Service</a>
    <a href="dashboard-admin.php">⬅ Retour Admin</a>
  </div>

  <?php if (empty($services)): ?>
    <p>Aucun service n'est disponible.</p>
  <?php else: ?>
    <table>
      <tr>
        <th>ID</th>
        <th>Catégorie</th>
        <th>Nom</th>
        <th>Description</th>
        <th>Prix</th>
        <th>Actions</th>
      </tr>
      <?php foreach ($services as $service): ?>
      <tr>
        <td><?= htmlspecialchars($service['id']) ?></td>
        <td><?= htmlspecialchars($service['category']) ?></td>
        <td><?= htmlspecialchars($service['name']) ?></td>
        <td><?= htmlspecialchars($service['description']) ?></td>
        <td><?= htmlspecialchars($service['price']) ?></td>
        <td class="actions">
          <form action="delete_service.php" method="POST">
            <input type="hidden" name="id" value="<?= $service['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <button type="submit" onclick="return confirm('Voulez-vous vraiment supprimer ce service ?');">🗑 Supprimer</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
</div>

</body>
</html>