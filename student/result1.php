<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION["id_etudiant"])) {
    header("Location: ../auth/login.php");
    exit();
}

$id = $_SESSION["id_etudiant"];

//  Recuperer les information de l'etudiant 
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

//  Calculer la moyenne depuis la table composer
$sqlNotes = "SELECT AVG(note) AS moyenne 
             FROM composer 
             WHERE id_etudiant = :id";

$stmtNotes = $pdo->prepare($sqlNotes);
$stmtNotes->bindParam(":id", $id);
$stmtNotes->execute();
$result = $stmtNotes->fetch(PDO::FETCH_ASSOC);

$moyenne = $result["moyenne"]; */
$moyenne = $etudiant["moyenne"];

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/student-resultat-admis.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <title>Résultat Admis</title>
</head>

<body>
    <div class="container">
        <!-- Logo Ecole en haut de la carte -->
        <div class="school-logo">
            <img src="../assets/images/logo-esma.png" alt="ESMA">
        </div>

        <div class="check-icon">
            <i class="fa-solid fa-check"></i>
        </div>

        <div class="valid-badge">RÉSULTAT : ADMIS ✓</div>

        <div class="student-info">
           <h2 class="student-name">
                     <?= htmlspecialchars($etudiant["nom"]) ?>
            </h2>
             <p class="student-filiere">
                    <?= htmlspecialchars($etudiant["filiere"]) ?>
              </p>

        </div>

        <div class="result-message">
            <h1>Félicitations !</h1>
            <p>Votre persévérance et votre talent ont porté leurs fruits. Vous avez validé votre semestre avec une
                mention d'excellence.</p>
        </div>

        <div class="actions">
            <a href="index.html" class="btn-back">Retour à l'accueil</a>
           <!-- <a href="student/succes.php" class="btn-details">Voir le détail des notes</a>  -->
        </div>
    </div>
    <!-- Confetti Script -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>
    <script>
        window.onload = function () {
            function shoot() {
                var duration = 3 * 1000;
                var end = Date.now() + duration;

                (function frame() {
                    confetti({
                        particleCount: 3,
                        angle: 60,
                        spread: 55,
                        origin: { x: 0, y: 0.6 },
                        colors: ['#22c55e', '#e8521a', '#ffffff']
                    });
                    confetti({
                        particleCount: 3,
                        angle: 120,
                        spread: 55,
                        origin: { x: 1, y: 0.6 },
                        colors: ['#22c55e', '#e8521a', '#ffffff']
                    });

                    if (Date.now() < end) {
                        requestAnimationFrame(frame);
                    }
                }());
            }

            // Lancement initial
            shoot();

            // Répétition toutes les 9 secondes (3s de fête + 6s de pause)
            setInterval(shoot, 9000);
        };

        // Redirection après 3 secondes vers succes.php
        setTimeout(function() {
      window.location.href = "succes.php";
          }, 5000);
</script>
    </script>
</body>

</html>