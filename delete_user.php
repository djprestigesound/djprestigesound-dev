<?php
session_start();
require_once 'db.php';

// Contrôle d'accès
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (!isset($_POST['id'])) {
    die("⛔ Données invalides.");
}

$pdo = getPDO();
$userId = intval($_POST['id']);
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
if ($stmt->execute([$userId])) {
    echo "✅ Utilisateur supprimé avec succès.";
} else {
    echo "❌ Erreur lors de la suppression.";
}
?>
