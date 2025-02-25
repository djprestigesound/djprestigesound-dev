<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Vérification des données POST
if (!isset($_POST['id'], $_POST['name'], $_POST['date'], $_POST['location'], $_POST['status'])) {
    die("⛔ Données invalides.");
}

$pdo = getPDO();

$eventId = intval($_POST['id']);
$name = trim($_POST['name']);
$date = trim($_POST['date']);
$location = trim($_POST['location']);
$status = trim($_POST['status']);
$service_id = isset($_POST['service_id']) && !empty($_POST['service_id']) ? intval($_POST['service_id']) : null;

$validStatuses = ['En attente', 'Confirmé', 'Terminé'];
if (!in_array($status, $validStatuses)) {
    die("⛔ Statut invalide.");
}

// Mettre à jour les informations de l'événement
$stmt = $pdo->prepare("UPDATE events SET event_name = ?, event_date = ?, event_location = ?, event_status = ? WHERE id = ?");
$eventUpdated = $stmt->execute([$name, $date, $location, $status, $eventId]);

// Mettre à jour le service associé
if (!empty($service_id)) {
    // Vérifier si un service est déjà lié à cet événement
    $checkService = $pdo->prepare("SELECT COUNT(*) FROM event_services WHERE event_id = ?");
    $checkService->execute([$eventId]);
    $serviceExists = $checkService->fetchColumn();

    if ($serviceExists) {
        // Mettre à jour le service existant
        $stmtService = $pdo->prepare("UPDATE event_services SET service_id = ? WHERE event_id = ?");
        $stmtService->execute([$service_id, $eventId]);
    } else {
        // Ajouter un service si aucun n'était lié
        $stmtService = $pdo->prepare("INSERT INTO event_services (event_id, service_id) VALUES (?, ?)");
        $stmtService->execute([$eventId, $service_id]);
    }
} else {
    // Supprimer le service associé si l'utilisateur choisit "Aucun"
    $stmtService = $pdo->prepare("DELETE FROM event_services WHERE event_id = ?");
    $stmtService->execute([$eventId]);
}

if ($eventUpdated) {
    echo "✅ Événement mis à jour avec succès.";
} else {
    echo "❌ Erreur lors de la mise à jour.";
}
?>