<?php
$password = "monmotdepasse";
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Mot de passe : $password<br>";
echo "Hash : $hash<br>";
?>
