<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION["id_etudiant"])) {
    header("Location: ../auth/login.php");
    exit();
}

$id = $_SESSION["id_etudiant"];

//  Récupérer les informations de l'étudiant
$sql = "SELECT * FROM etudiant WHERE id_etudiant = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(":id", $id);
$stmt->execute();
$etudiant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$etudiant) {
    header("Location: ../auth/login.php");
    exit();
}
/*
//  Calcul moyenne depuis la table composer
$sqlNotes = "SELECT AVG(note) AS moyenne 
             FROM composer 
             WHERE id_etudiant = :id";

$stmtNotes = $pdo->prepare($sqlNotes);
$stmtNotes->bindParam(":id", $id);
$stmtNotes->execute();
$result = $stmtNotes->fetch(PDO::FETCH_ASSOC);

$moyenne = $result["moyenne"];  */

$moyenne = $etudiant["moyenne"];

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/student-resultat-ajourne.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <title>Résultat Ajourné</title>
</head>

<body>
    <div class="container">
        <!-- Logo Ecole en haut de la carte -->
        <div class="school-logo">
            <img src="../assets/images/logo-esma.png" alt="ESMA">
        </div>

        <div class="check-icon">
            <i class="fa-solid fa-xmark"></i>
        </div>

        <div class="valid-badge danger">RÉSULTAT : AJOURNÉ ✕</div>

        <div class="student-info">
            <h2 class="student-name">
                       <?= htmlspecialchars($etudiant["nom"]) ?>
              </h2>
           <p class="student-filiere">
                <?= htmlspecialchars($etudiant["filiere"]) ?>
            </p>
        </div>

        <div class="result-message">
            <h1>Pas de chance ! </h1>
            <p>Ne vous découragez pas. L'échec est une étape vers la réussite. Nous croyons en vos capacités pour la
                suite.</p>
        </div>

        <div class="actions">
            <a href="index.html" class="btn-back">Retour à l'accueil</a>
             <!-- <a href="student/echec.php" class="btn-details danger">Voir le détail des notes</a>  -->
        </div>
    </div>

    <script>


// Redirection après 3 secondes vers echec.php
setTimeout(function() {
    window.location.href = "echec.php";
}, 3000);
</script>
</body>

</html>