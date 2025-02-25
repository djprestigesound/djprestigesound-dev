<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$pdo = getPDO();

// 📊 Récupérer les stats des événements par mois
$statsStmt = $pdo->prepare("SELECT DATE_FORMAT(event_date, '%Y-%m') as month, COUNT(*) as count FROM events GROUP BY month ORDER BY month ASC");
$statsStmt->execute();
$stats = $statsStmt->fetchAll(PDO::FETCH_ASSOC);

$months = array_map(fn($s) => $s['month'], $stats);
$eventCounts = array_map(fn($s) => $s['count'], $stats);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de Bord</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<h1>📊 Tableau de Bord</h1>

<canvas id="eventsChart"></canvas>

<script>
const ctx = document.getElementById('eventsChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($months) ?>,
        datasets: [{
            label: 'Événements par Mois',
            data: <?= json_encode($eventCounts) ?>,
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1
        }]
    }
});
</script>

<a href="events-admin.php">⬅ Retour</a>

</body>
</html>