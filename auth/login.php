<?php
session_start();
include "../config/database.php";

$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matricule = trim($_POST["matricule"]);

    if (!empty($matricule)) {

        try {

            //  Vérifier si l'étudiant existe
            $sql = "SELECT * FROM etudiant WHERE matricule = :matricule";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":matricule", $matricule);
            $stmt->execute();
            $etudiant = $stmt->fetch(PDO::FETCH_ASSOC);

               if ($etudiant) {

                $_SESSION["id_etudiant"] = $etudiant["id_etudiant"];
                $_SESSION["nom"] = $etudiant["nom"];

                //recuperer la moyenne de l'etudient
                $moyenne = $etudiant["moyenne"];

                if ($moyenne !== null) {

                    //  Condition pour savoir l'etudiant a validé ou echoué
                    if ($moyenne >= 10) {
                        header("Location: ../student/result1.php");
                        exit();
                    } else {
                        header("Location: ../student/result2.php");
                        exit();
                    }

                } else {
                    $message = "Aucune note trouvée pour cet étudiant.";
                }

            } else {
                $message = "Matricule introuvable";
            }

        } catch (PDOException $e) {
            $message = "Erreur : " . $e->getMessage();
        }

    } else {
        $message = "Veuillez entrer votre matricule.";
    }
}
?>





<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Identification - ESMA</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CSS personnalisé -->
    <link rel="stylesheet" href="../assets/css/student-login.css">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">

</head>

<body>

    <!-- HEADER -->
    <nav class="navbar navbar-dark custom-navbar">
        <div class="container d-flex justify-content-between">
            <span class="navbar-brand mb-0 h1">ESMA RIVIERA II</span>
            <a href="../index.html" class="btn btn-sm btn-outline-light border-0 opacity-75">Accueil</a>
        </div>
    </nav>

    <!-- SECTION LOGIN -->
    <div class="container d-flex justify-content-center align-items-center vh-100">

        <div class="card login-card shadow border-0">

            <div class="card-body text-center p-0">


                <div class="icon">
                    <img src="../assets/images/logo-esma.png" alt="Logo ESMA">
                </div>




                <h4 class="mb-2"><strong>Identification</strong></h4>
                <p class="text-muted small mb-4">
                    Entrez votre matricule .
                </p>

                <form  method="POST" action="">

                    <!-- Matricule -->
                    <div class="mb-4 text-start">
                        <label class="form-label">NUMÉRO MATRICULE</label>

                        <input type="text" name="matricule"  id="matricule" class="form-control" placeholder="Ex: EC-2026-STD-1234" required>
                       
                    </div>

                    <!-- Bouton -->
                    <button type="submit" class="btn btn-consulter w-100">
                        Consulter mon resultat
                    </button>

                </form>

                <div class="footer-text mt-4 opacity-75">
                    Besoin d'aide ? | Contactez la scolarité
                </div>

            </div>
        </div>

    </div>
    <script src="../assets/js/bootstrap.min.js"></script>
    <!-- <script>   
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const matricule = document.getElementById('matricule').value.toUpperCase();
            
            // Matricules de test
            if (matricule === 'ADM001') {
                window.location.href = 'student-resultat-admis.html';
            } else if (matricule === 'AJR001') {
                window.location.href = 'student-resultat-ajourne.html';
            } else {
                alert('Matricule non reconnu. Utilisez ADM001 (Admis) ou AJR001 (Ajourné) pour tester.');
            }
        });
    </script> -->
</body>

</html>     

