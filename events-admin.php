<?php
session_start();
require_once 'db.php';
require_once 'config.php'; // Assure-toi que ce fichier est bien inclus

// 🔹 Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ✅ Vérification de l'accès admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$pdo = getPDO();
?>
<?php
// ✅ Récupérer les événements avec leurs services et prestataires
$stmt = $pdo->prepare("
    SELECT 
        events.id, 
        users.email AS client_email, 
        event_name, 
        event_date, 
        event_location, 
        event_status,
        COALESCE(
            GROUP_CONCAT(DISTINCT services.name ORDER BY services.name SEPARATOR ', '), 
            'Aucun'
        ) AS services_list,
        prestataires.email AS prestataire_email
    FROM events 
    JOIN users ON events.client_id = users.id
    LEFT JOIN event_services ON events.id = event_services.event_id
    LEFT JOIN services ON event_services.service_id = services.id
    LEFT JOIN users AS prestataires ON events.prestataire_id = prestataires.id
    GROUP BY events.id
    ORDER BY event_date DESC
");
$stmt->execute();
$events = $stmt->fetchAll();
?>
<?php
// ✅ Récupérer la liste des clients
$clientsStmt = $pdo->prepare("SELECT id, email FROM users WHERE role = 'client'");
$clientsStmt->execute();
$clients = $clientsStmt->fetchAll();

// ✅ Récupérer la liste des prestataires
$prestatairesStmt = $pdo->prepare("SELECT id, email FROM users WHERE role = 'prestataire'");
$prestatairesStmt->execute();
$prestataires = $prestatairesStmt->fetchAll();

// ✅ Récupérer la liste des services disponibles
$servicesStmt = $pdo->prepare("SELECT id, name FROM services ORDER BY name ASC");
$servicesStmt->execute();
$services = $servicesStmt->fetchAll();
?>
<?php
// 📊 Statistiques des événements
$totalEvents = count($events);
$confirmedEvents = count(array_filter($events, fn($e) => $e['event_status'] === 'Confirmé'));
$pendingEvents = count(array_filter($events, fn($e) => $e['event_status'] === 'En attente'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Événements</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body { font-family: 'Inter', sans-serif; background: #111; color: #fff; padding: 20px; }
        h1 { color: #FFA564; text-align: center; }
        .stats { text-align: center; margin-bottom: 20px; }
        .stat-box { display: inline-block; padding: 10px 20px; margin: 5px; border-radius: 5px; }
        .total { background: #28a745; }
        .pending { background: #FFC107; color: #111; }
        .confirmed { background: #17a2b8; }
        table { width: 90%; max-width: 1000px; border-collapse: collapse; margin: 20px auto; }
        th, td { padding: 10px; border: 1px solid #FFA564; text-align: center; }
        th { background: #FF8C42; }
        .services-list span { display: inline-block; padding: 5px 10px; margin: 2px; border-radius: 5px; background: #007bff; color: #fff; font-size: 12px; }
        .delete-btn { background: #dc3545; }
        .delete-btn:hover { background: #c82333; }
        .add-service-btn { background: #28a745; color: white; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>

<h1>📅 Gestion des Événements</h1>

<div class="stats">
    <span class="stat-box total">Total : <?= $totalEvents ?> événements</span>
    <span class="stat-box confirmed">✅ Confirmés : <?= $confirmedEvents ?></span>
    <span class="stat-box pending">⚠️ En attente : <?= $pendingEvents ?></span>
</div>

<table>
    <tr>
        <th>ID</th>
        <th>Client</th>
        <th>Nom</th>
        <th>Date</th>
        <th>Lieu</th>
        <th>Prestataire</th>
        <th>Services</th>
        <th>Statut</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($events as $event) : ?>
    <tr>
        <td><?= htmlspecialchars($event['id']) ?></td>
        <td><?= htmlspecialchars($event['client_email']) ?></td>
        <td><?= htmlspecialchars($event['event_name']) ?></td>
        <td><?= htmlspecialchars($event['event_date']) ?></td>
        <td><?= htmlspecialchars($event['event_location']) ?></td>
        <td><?= !empty($event['prestataire_email']) ? htmlspecialchars($event['prestataire_email']) : "<span style='color:#ccc;'>Aucun</span>"; ?></td>
        
        <!-- ✅ Affichage des services avec suppression dynamique -->
        <td class="services-list" data-event-id="<?= $event['id'] ?>">
            <?php 
            if (!empty($event['services_list']) && $event['services_list'] !== 'Aucun') {
                foreach (explode(', ', $event['services_list']) as $service) {
                    echo "<span class='service-badge' data-service='$service'>$service 
                    <button onclick='removeService({$event['id']}, \"$service\")' class='remove-service-btn'>🗑️</button></span> ";
                }
            } else {
                echo "<span style='color:#ccc;'>Aucun</span>";
            }
            ?>
            <button onclick="openServiceModal(<?= $event['id'] ?>)" class="add-service-btn">+ Associer</button>
        </td>

        <!-- ✅ Sélection du statut -->
        <td>
            <select class="status-select" data-event-id="<?= $event['id'] ?>">
                <option value="En attente" <?= $event['event_status'] === 'En attente' ? 'selected' : '' ?>>En attente</option>
                <option value="Confirmé" <?= $event['event_status'] === 'Confirmé' ? 'selected' : '' ?>>Confirmé</option>
                <option value="Terminé" <?= $event['event_status'] === 'Terminé' ? 'selected' : '' ?>>Terminé</option>
            </select>
        </td>

        <td>
            <button onclick="updateEvent(<?= htmlspecialchars($event['id']) ?>)">✏️ Modifier</button>
            <button class="delete-btn" onclick="deleteEvent(<?= htmlspecialchars($event['id']) ?>)">🗑️ Supprimer</button>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
	
	<!-- ✅ Fenêtre modale pour ajouter un service -->
<div id="serviceModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeServiceModal()">&times;</span>
        <h2>Associer un Service</h2>
        <input type="text" id="searchService" placeholder="Rechercher un service..." oninput="filterServices()">
        <form id="addServiceForm">
            <input type="hidden" id="event_id" name="event_id">
            <label>Choisissez un service :</label>
            <select id="serviceSelect" name="service_id">
                <!-- Rempli dynamiquement en JS -->
            </select>
            <button type="submit">Ajouter</button>
        </form>
    </div>
</div>
	
	function openServiceModal(eventId) {
    document.getElementById('event_id').value = eventId;

    fetch('get_services.php')
        .then(response => response.json())
        .then(data => {
            let serviceSelect = document.getElementById('serviceSelect');
            serviceSelect.innerHTML = ""; // ✅ Nettoyage de la liste

            let existingServices = [...document.querySelectorAll(`.services-list[data-event-id="${eventId}"] .service-badge`)]
                .map(span => span.getAttribute("data-service")); // Liste des services déjà associés

            data.forEach(service => {
                if (!existingServices.includes(service.name)) { // ✅ Vérifie que le service n'est pas déjà ajouté
                    let option = document.createElement('option');
                    option.value = service.id;
                    option.textContent = service.name;
                    serviceSelect.appendChild(option);
                }
            });
        })
        .catch(error => console.error("Erreur lors de la récupération des services :", error));

    document.getElementById('serviceModal').style.display = 'block';
}

function closeServiceModal() {
    document.getElementById('serviceModal').style.display = 'none';
}
	
	document.getElementById('addServiceForm').addEventListener('submit', function(e) {
    e.preventDefault();
    let formData = new FormData(this);
    let eventId = document.getElementById('event_id').value;
    let serviceSelect = document.getElementById('serviceSelect');
    let selectedService = serviceSelect.options[serviceSelect.selectedIndex].text;
    let serviceList = document.querySelector(`.services-list[data-event-id="${eventId}"]`);
    
    // ✅ Vérification si le service est déjà ajouté
    let alreadyExists = [...serviceList.children].some(span => span.getAttribute("data-service") === selectedService);
    if (alreadyExists) {
        alert("⚠️ Ce service est déjà ajouté !");
        return;
    }

    fetch('add_service_to_event.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("✅ Service ajouté !");
            
            let newService = document.createElement("span");
            newService.classList.add("service-badge");
            newService.setAttribute("data-service", selectedService);
            newService.innerHTML = `${selectedService} <button onclick='removeService(${eventId}, "${selectedService}")' class='remove-service-btn'>🗑️</button>`;
            
            serviceList.appendChild(newService);
            closeServiceModal();
        } else {
            alert("❌ Erreur : " + data.message);
        }
    })
    .catch(error => console.error("Erreur lors de l'ajout du service :", error));
});
	
	function removeService(eventId, serviceName) {
    if (!confirm(`⚠️ Voulez-vous retirer "${serviceName}" de cet événement ?`)) return;

    fetch('remove_service_from_event.php', {
        method: 'POST',
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ event_id: eventId, service_name: serviceName })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("✅ Service supprimé !");
            let serviceList = document.querySelector(`.services-list[data-event-id="${eventId}"]`);

            serviceList.querySelectorAll(".service-badge").forEach(span => {
                if (span.getAttribute("data-service") === serviceName) {
                    span.remove(); // ✅ Supprime le service visuellement
                }
            });

            // ✅ Vérifie s'il ne reste aucun service et affiche "Aucun"
            if (serviceList.querySelectorAll(".service-badge").length === 0) {
                serviceList.innerHTML = "<span style='color:#ccc;'>Aucun</span><button onclick='openServiceModal(" + eventId + ")' class='add-service-btn'>+ Associer</button>";
            }

        } else {
            alert("❌ Erreur : " + data.message);
        }
    })
    .catch(error => console.error("Erreur lors de la suppression :", error));
}
	
	document.querySelectorAll('.status-select').forEach(select => {
    select.addEventListener('change', function() {
        let eventId = this.getAttribute('data-event-id');
        let newStatus = this.value;

        fetch('update_event_status.php', {
            method: 'POST',
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({ event_id: eventId, status: newStatus })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("✅ Statut mis à jour !");
            } else {
                alert("❌ Erreur : " + data.message);
            }
        })
        .catch(error => console.error("Erreur lors de la mise à jour du statut :", error));
    });
});

	/* ✅ Styles pour la popup */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    width: 350px;
    background: #222;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
    text-align: center;
}

.modal-content {
    color: #fff;
    position: relative;
}

.modal-content label,
.modal-content select,
.modal-content button {
    display: block;
    width: 100%;
    margin: 10px 0;
    padding: 8px;
    border-radius: 5px;
}

/* ✅ Boutons améliorés */
.add-service-btn {
    background: #28a745;
    color: white;
    padding: 5px 10px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.add-service-btn:hover {
    background: #218838;
}

.remove-service-btn {
    background: #dc3545;
    border: none;
    color: white;
    padding: 4px 6px;
    margin-left: 5px;
    cursor: pointer;
    border-radius: 4px;
    font-size: 12px;
}

.remove-service-btn:hover {
    background: #c82333;
}

/* ✅ Amélioration du tableau */
table {
    width: 95%;
    max-width: 1200px;
    border-collapse: collapse;
    margin: 20px auto;
}

th, td {
    padding: 10px;
    border: 1px solid #FFA564;
    text-align: center;
}

th {
    background: #FF8C42;
    color: white;
}

/* ✅ Ajustements des couleurs des stats */
.stat-box {
    display: inline-block;
    padding: 10px 20px;
    margin: 5px;
    border-radius: 5px;
    font-weight: bold;
}

.total {
    background: #28a745;
}

.pending {
    background: #FFC107;
    color: #111;
}

.confirmed {
    background: #17a2b8;
}