<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Vérification des données POST
if (!isset($_POST['event_name'], $_POST['event_date'], $_POST['event_location'], $_POST['client_id'], $_POST['event_status'])) {
    die("⛔ Données invalides.");
}

$pdo = getPDO();

$event_name = trim($_POST['event_name']);
$event_date = trim($_POST['event_date']);
$event_location = trim($_POST['event_location']);
$client_id = intval($_POST['client_id']);
$prestataire_id = isset($_POST['prestataire_id']) && !empty($_POST['prestataire_id']) ? intval($_POST['prestataire_id']) : null;
$event_status = trim($_POST['event_status']);
$service_id = isset($_POST['service_id']) && !empty($_POST['service_id']) ? intval($_POST['service_id']) : null;

$validStatuses = ['En attente', 'Confirmé', 'Terminé'];
if (!in_array($event_status, $validStatuses)) {
    die("⛔ Statut invalide.");
}

// Insérer l'événement
$stmt = $pdo->prepare("INSERT INTO events (event_name, event_date, event_location, client_id, prestataire_id, event_status) VALUES (?, ?, ?, ?, ?, ?)");
if ($stmt->execute([$event_name, $event_date, $event_location, $client_id, $prestataire_id, $event_status])) {
    
    // Récupérer l'ID de l'événement créé
    $event_id = $pdo->lastInsertId();

    // Si un service a été sélectionné, l'associer à l'événement
    if (!empty($service_id)) {
        $stmtService = $pdo->prepare("INSERT INTO event_services (event_id, service_id) VALUES (?, ?)");
        $stmtService->execute([$event_id, $service_id]);
    }

    // Redirection vers la gestion des événements
    header("Location: events-admin.php");
    exit;
} else {
    die("❌ Erreur lors de l'ajout de l'événement.");
}
?>