<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
if (!isset($_POST['id'])) {
    die("⛔ Données invalides.");
}
$pdo = getPDO();
$eventId = intval($_POST['id']);
$stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
if ($stmt->execute([$eventId])) {
    echo "✅ Événement supprimé avec succès.";
} else {
    echo "❌ Erreur lors de la suppression.";
}
?>
