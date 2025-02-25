<?php
session_start();
require_once 'db.php';

// -- SI VOUS AVEZ UN FICHIER config.php AVEC LA FONCTION generateCsrfToken() --
// require_once 'config.php';

// Vérifier que l'utilisateur est admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$pdo = getPDO();

// Récupérer la liste des utilisateurs (clients, prestataires et admin)
$stmt = $pdo->prepare("SELECT id, email, role FROM users WHERE role IN ('client', 'prestataire', 'admin')");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Génération du token CSRF (à activer si vous avez déjà la fonction)
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Gestion des utilisateurs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Pour une maintenance facilitée, pensez à externaliser ce CSS -->
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #111;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 20px;
        }
        .admin-container {
            background: #222;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(255, 140, 66, 0.3);
            width: 80%;
            max-width: 800px;
            text-align: center;
        }
        h2 { color: #FFA564; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #FFA564;
            text-align: center;
        }
        th { background: #FF8C42; }
        input, select {
            padding: 5px;
            background: #333;
            color: #fff;
            border: none;
            text-align: center;
        }
        button {
            padding: 8px 12px;
            background: #FFA564;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover { background: #FF8C42; }
        .delete-btn { background: #dc3545; }
        .delete-btn:hover { background: #c82333; }
        .logout, .manage-events {
            margin-top: 20px;
            display: inline-block;
            padding: 10px 20px;
            background: #FF8C42;
            color: #111;
            font-weight: bold;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 10px;
        }
        .logout:hover, .manage-events:hover {
            background: #FFA564;
        }
    </style>
</head>
<body>

<div class="admin-container">
    <h1>Bienvenue, <?= htmlspecialchars($_SESSION['user']['email']) ?> !</h1>
    <p>Vous êtes connecté en tant qu'administrateur.</p>

    <h2>📋 Liste des Clients & Prestataires</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Rôle</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($users as $user) : ?>
            <tr>
                <td><?= htmlspecialchars($user['id']) ?></td>
                <td>
                    <input type="text" id="email-<?= htmlspecialchars($user['id']) ?>"
                           value="<?= htmlspecialchars($user['email']) ?>">
                </td>
                <td>
                    <select id="role-<?= htmlspecialchars($user['id']) ?>">
                        <option value="client" <?= $user['role'] === 'client' ? 'selected' : '' ?>>Client</option>
                        <option value="prestataire" <?= $user['role'] === 'prestataire' ? 'selected' : '' ?>>Prestataire</option>
                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </td>
                <td>
                    <button onclick="updateUser(<?= htmlspecialchars($user['id']) ?>)">✏️ Modifier</button>
                    <button class="delete-btn" onclick="deleteUser(<?= htmlspecialchars($user['id']) ?>)">🗑️ Supprimer</button>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <!-- Boutons pour accéder aux autres pages admin -->
    <a href="events-admin.php" class="manage-events">📅 Gérer les Événements</a>
    <a href="services_admin.php" class="manage-events">🛠 Gérer les Services</a>
    <a href="logout.php" class="logout">Déconnexion</a>
</div>

<script>
function updateUser(userId) {
    const email = document.getElementById(`email-${userId}`).value;
    const role = document.getElementById(`role-${userId}`).value;
    fetch('edit_user.php', {
        method: 'POST',
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
            id: userId,
            email: email,
            role: role,
            csrf_token: '<?= $csrfToken ?>'
        })
    })
    .then(response => response.text())
    .then(data => {
        alert(data);
        location.reload();
    })
    .catch(error => console.error("Erreur lors de la mise à jour :", error));
}

function deleteUser(userId) {
    if (!confirm("⚠️ Êtes-vous sûr de vouloir supprimer cet utilisateur ?")) return;
    fetch('delete_user.php', {
        method: 'POST',
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
            id: userId,
            csrf_token: '<?= $csrfToken ?>'
        })
    })
    .then(response => response.text())
    .then(data => {
        alert(data);
        location.reload();
    })
    .catch(error => console.error("Erreur lors de la suppression :", error));
}
</script>

</body>
</html>
