<?php
session_start();
require_once 'db.php';

// Contrôle d'accès
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Vérification des données POST
if (!isset($_POST['id'], $_POST['email'], $_POST['role'])) {
    die("⛔ Données invalides.");
}

$pdo = getPDO();
$userId = intval($_POST['id']);
$email = trim($_POST['email']);
$role = trim($_POST['role']);

$validRoles = ['client', 'prestataire', 'admin'];
if (!in_array($role, $validRoles)) {
    die("⛔ Rôle invalide.");
}

$stmt = $pdo->prepare("UPDATE users SET email = ?, role = ? WHERE id = ?");
if ($stmt->execute([$email, $role, $userId])) {
    echo "✅ Utilisateur mis à jour avec succès.";
} else {
    echo "❌ Erreur lors de la mise à jour.";
}
?>
