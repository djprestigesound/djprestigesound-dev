<?php
header("Content-Type: application/json");
require_once 'db.php';

$pdo = getPDO();
$type = isset($_GET['type']) ? $_GET['type'] : '';
$data = [];

try {
  switch ($type) {
    case 'clients':
      $stmt = $pdo->query("SELECT * FROM events_clients");
      $data = $stmt->fetchAll();
      break;
    case 'prestataires':
      $stmt = $pdo->query("SELECT * FROM events_prestataires");
      $data = $stmt->fetchAll();
      break;
    default:
      $data = [];
      break;
  }
} catch (PDOException $e) {
  error_log("Erreur dans fetch_data.php: " . $e->getMessage());
  $data = [];
}

echo json_encode($data);
?>