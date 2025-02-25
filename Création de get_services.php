<?php
require_once 'db.php';
$pdo = getPDO();

$stmt = $pdo->query("SELECT id, name FROM services ORDER BY name ASC");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($services);
?>