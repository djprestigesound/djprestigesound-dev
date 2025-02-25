<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'client') {
    header("Location: login.php");
    exit;
}
if (!isset($_POST['event_id'], $_POST['playlist'])) {
    die("⛔ Données invalides.");
}
$pdo = getPDO();
$event_id = intval($_POST['event_id']);
$playlist = trim($_POST['playlist']);
$stmt = $pdo->prepare("UPDATE events SET playlist = ? WHERE id = ? AND client_id = ?");
if ($stmt->execute([$playlist, $event_id, $_SESSION['user']['id']])) {
    echo "✅ Playlist mise à jour avec succès.";
} else {
    echo "❌ Erreur lors de la mise à jour de la playlist.";
}
?>
