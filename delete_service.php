<?php
session_start();
require_once 'db.php';
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id'], $_POST['csrf_token'])) {
        die("Données invalides.");
    }

    $id = intval($_POST['id']);
    $pdo = getPDO();

    $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
    if ($stmt->execute([$id])) {
        header("Location: services_admin.php");
        exit;
    } else {
        die("Erreur lors de la suppression du service.");
    }
} else {
    die("Méthode non autorisée.");
}