<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("⛔ Accès non autorisé.");
}

if (!isset($_POST['event_id'], $_POST['service_id'])) {
    die("⛔ Données invalides.");
}

$pdo = getPDO();
$eventId = intval($_POST['event_id']);
$serviceId = intval($_POST['service_id']);

// Vérifier si le service est déjà ajouté
$stmt = $pdo->prepare("SELECT COUNT(*) FROM event_services WHERE event_id = ? AND service_id = ?");
$stmt->execute([$eventId, $serviceId]);
$exists = $stmt->fetchColumn();

if ($exists) {
    die("⚠️ Ce service est déjà associé à l'événement.");
}

// Ajouter l'association
$stmt = $pdo->prepare("INSERT INTO event_services (event_id, service_id) VALUES (?, ?)");
if ($stmt->execute([$eventId, $serviceId])) {
    echo "✅ Service ajouté avec succès !";
} else {
    echo "❌ Erreur lors de l'ajout du service.";
}
?>