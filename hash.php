<?php
$password = "admin1234";
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
echo "Nouveau hash : " . $hashedPassword;
?>