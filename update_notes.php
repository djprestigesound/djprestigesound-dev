<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'client') {
    header("Location: login.php");
    exit;
}
if (!isset($_POST['event_id'], $_POST['note'])) {
    die("⛔ Données invalides.");
}
$pdo = getPDO();
$event_id = intval($_POST['event_id']);
$note = trim($_POST['note']);
$stmt = $pdo->prepare("UPDATE events SET client_note = ? WHERE id = ? AND client_id = ?");
if ($stmt->execute([$note, $event_id, $_SESSION['user']['id']])) {
    echo "✅ Note mise à jour avec succès.";
} else {
    echo "❌ Erreur lors de la mise à jour de la note.";
}
?>
