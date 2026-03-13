<?php
session_start();

// détruire toutes les données de session
session_destroy();

// redirection vers la page d'accueil (login.php par exemple)
header("Location: ../index.html");
exit();
?>