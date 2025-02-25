<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'client') {
    header("Location: login.php");
    exit;
}
$pdo = getPDO();
$client_id = $_SESSION['user']['id'];
// Récupérer les événements du client
$stmt = $pdo->prepare("SELECT * FROM events WHERE client_id = ?");
$stmt->execute([$client_id]);
$events = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Espace Client | DJ Prestige Sound</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="espace-client.css">
    <style>
        .notification { margin: 10px 0; padding: 10px; border-radius: 5px; }
        .success { background-color: #28a745; color: #fff; }
        .error { background-color: #dc3545; color: #fff; }
    </style>
</head>
<body>
<header class="site-header">
    <div class="container">
        <h1 class="client-title">🎧 Espace Client</h1>
        <a href="logout.php" class="back-link">Déconnexion</a>
    </div>
</header>
<div class="container">
    <h2>Mes Événements</h2>
    <?php if(empty($events)): ?>
        <p>Aucun événement trouvé.</p>
    <?php else: ?>
        <?php foreach($events as $event): ?>
            <div class="event-card">
                <h2><?= htmlspecialchars($event['event_name']) ?></h2>
                <p><strong>Date :</strong> <?= htmlspecialchars($event['event_date']) ?></p>
                <p><strong>Lieu :</strong> <?= htmlspecialchars($event['event_location']) ?></p>
                <p><strong>Statut :</strong> <?= htmlspecialchars($event['event_status']) ?></p>
                <!-- Gestion de playlist et de notes -->
                <div class="playlist-section">
                    <h3>Playlist</h3>
                    <input type="text" id="playlist-<?= $event['id'] ?>" placeholder="Ajouter un lien Spotify/YouTube">
                    <button onclick="updatePlaylist(<?= $event['id'] ?>)">Mettre à jour</button>
                </div>
                <div class="notes-section">
                    <h3>Ajouter une note</h3>
                    <textarea id="note-<?= $event['id'] ?>" placeholder="Votre note ici..."></textarea>
                    <button onclick="updateNote(<?= $event['id'] ?>)">Enregistrer la note</button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<script>
function updatePlaylist(eventId) {
    const playlist = document.getElementById(`playlist-${eventId}`).value;
    fetch('update_playlist.php', {
        method: 'POST',
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ event_id: eventId, playlist: playlist })
    })
    .then(response => response.text())
    .then(data => { alert(data); location.reload(); })
    .catch(error => console.error("Erreur :", error));
}
function updateNote(eventId) {
    const note = document.getElementById(`note-${eventId}`).value;
    fetch('update_notes.php', {
        method: 'POST',
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ event_id: eventId, note: note })
    })
    .then(response => response.text())
    .then(data => { alert(data); location.reload(); })
    .catch(error => console.error("Erreur :", error));
}
</script>
</body>
</html>
