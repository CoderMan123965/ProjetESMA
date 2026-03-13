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
//  Récupérer les matières + les notes
$sqlNotes = "SELECT nom_mat, coef, note  FROM composer JOIN matiere  ON composer.id_mat = matiere.id_mat WHERE composer.id_etudiant = :id";

$stmtNotes = $pdo->prepare($sqlNotes);
$stmtNotes->bindParam(":id", $id);
$stmtNotes->execute();
$modules = $stmtNotes->fetchAll(PDO::FETCH_ASSOC);

//  Calcul de la moyenne pondérée
$totalPoints = 0;
$totalCoeff = 0;

foreach ($modules as $module) {
    $totalPoints += $module["note"] * $module["coef"];
    $totalCoeff += $module["coef"];
}
    $moyenne = $totalCoeff > 0 ? $totalPoints / $totalCoeff : 0;
*/

// Recuperer la moyenne de l'etudiant
$moyenne = $etudiant["moyenne"];

// Mention automatique
if ($moyenne >= 16) $mention = "Très Bien";
elseif ($moyenne >= 14) $mention = "Bien";
elseif ($moyenne >= 12) $mention = "Assez Bien";
elseif ($moyenne >= 10) $mention = "Passable";
else $mention = "Ajourné";
?>



<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails Résultats - Ajourné</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/student-details.css">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
   
    
</head>


<body>


    <!-- logo déconnexion -->
    <div class="logo-container">
        <button class="logout-btn" onclick="window.location.href='../auth/logout.php'">
            <img src="../assets/images/logout.png" alt="Se déconnecter">
        </button>
    </div>

   

    <!-- NAVBAR PREMIUM -->
    <nav class="navbar navbar-expand-lg navbar-custom py-2 px-4">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                <img src="../assets/images/logo-esma.png" alt="Logo ESMA" style="height: 45px;">
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <a href="../index.html" class="text-decoration-none text-white opacity-75 mb-4 d-inline-block">
            <i class="fa-solid fa-arrow-left"></i> Retour à l'accueil
        </a>

        <div class="row">
            <div class="col-lg-8">
                <!-- CARTE RÉSULTATS GÉNÉRAUX -->
                <div class="card card-custom p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                        <div class="d-flex align-items-center">
                            <div class="student-avatar me-3 bg-danger bg-opacity-75">
                                <i class="fa-solid fa-user-xmark"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-1 border-0">
                                    <?= htmlspecialchars($etudiant["nom"]) ?></h4>
                                <p class="text-muted small mb-0"><strong>MARS 2026 • <?= htmlspecialchars($etudiant["filiere"]) ?> </strong></p>
                            </div>
                        </div>
                        <span class="badge-status status-ajourne">
                            <i class="fa-solid fa-triangle-exclamation"></i> AJOURNÉ
                        </span>
                    </div>

                    <div class="text-center score-container mb-4">
                        <div class="text-muted small fw-bold mb-2">MOYENNE GÉNÉRALE</div>
                        <div>
                        <span class="score-huge ajourne"><?= number_format($moyenne, 2) ?></span>
                            <span class="fs-4 text-muted">/ 20</span>
                        </div>
                        <span class="badge bg-danger bg-opacity-10 text-danger mt-3 px-4 py-2 rounded-pill">
                            <i class="fa-solid fa-clock-rotate-left me-2"></i> <?= $moyenne < 10 ? "Session de Rattrapage" : $mention ?>
                        </span>
                    </div>

                </div>

                <!-- DÉTAILS DES MODULES -->
               <!-- <div class="card card-custom p-4">
                    <h6 class="fw-bold mb-4 d-flex align-items-center">
                        <div class="p-1 bg-danger bg-opacity-10 rounded me-2">
                            <i class="fa-solid fa-list-ul text-danger"></i>
                        </div>
                        Détail des notes obtenues
                    </h6>

                    <div class="table-responsive">
                        <table class="table-premium">
                            <thead>
                                <tr>
                                    <th>MATIÈRE</th>
                                    <th class="text-center">COEFFICIENT</th>
                                    <th class="text-center">MOYENNE</th>
                                </tr>
                            </thead>
                            <tbody>
           <?php //foreach ($modules as $module): ?>   
            <tr>
                <td><?//= htmlspecialchars($module["nom_mat"]) ?></td>
                <td class="text-center"><span class="coeff-badge"><?//= $module["coef"] ?></span></td>
                <td class="grade-value text-danger text-center"><?//= number_format($module["note"], 2) ?></td>
            </tr>
            <?php // endforeach; ?>
            </tbody>
                                    </table>
                    </div>
                </div>  --> 
        </div>

            <div class="col-lg-4">
                <!-- ACTIONS HARMONISÉES -->
                <div class="card card-custom p-4 sticky-lg-top" style="top: 20px;">
                    <h5 class="fw-bold mb-3">Documents Officiels</h5>
                    <p class="text-muted small mb-4">Le relevé de notes provisoire est disponible au téléchargement.</p>

                    <button class="btn-premium btn-premium-orange mb-3" onclick="generatePDF()">
                        <i class="fa-solid fa-file-pdf me-2"></i> Télécharger le Relevé
                    </button>

                    <button class="btn-premium bg-white text-dark border-0 mb-3" onclick="window.print()">
                        <i class="fa-solid fa-print me-2 text-danger"></i> Imprimer
                    </button>

                    <button class="btn-premium bg-white text-dark border-0" onclick="shareResults()">
                        <i class="fa-solid fa-share-nodes me-2 text-danger"></i> Partager le Relevé
                    </button>

                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPTS ACTIONS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>


const studentName = "<?= htmlspecialchars($etudiant['nom']) ?>";
const studentMatricule = "<?= htmlspecialchars($etudiant['matricule']) ?>";
const studentMoyenne = "<?= number_format($moyenne, 2) ?>";
const studentMention = "<?= $mention ?>";
//const modulesData = <?//= json_encode($modules) ?>;

        // Fonction Impression
        function printPage() {
            window.print();
        }

        // Fonction Partage
        function shareResults() {
            if (navigator.share) {
                navigator.share({
                    title: 'Mon Relevé de Notes - ESMA',
                    text: 'Je viens de consulter mes résultats sur le portail ESMA. On continue les efforts !',
                    url: window.location.href
                }).then(() => console.log('Partage réussi'))
                    .catch((error) => console.log('Erreur de partage', error));
            } else {
                alert("Le partage n'est pas supporté sur votre navigateur. Copiez l'URL : " + window.location.href);
            }
        }

        // Fonction PDF (jsPDF) - DESIGN CORPORATE 3.0 AJOURNÉ
        function generatePDF() 
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            const primaryColor = [26, 31, 75];
            const dangerColor = [239, 68, 68];
            const accentColor = [232, 82, 26];

            // --- HEADER GRAPHIQUE ---
            doc.setFillColor(...primaryColor);
            doc.rect(0, 0, 210, 50, 'F');

            // Texte Header
            doc.setTextColor(255, 255, 255);
            doc.setFont("helvetica", "bold");
            doc.setFontSize(24);
            doc.text("BULLETIN DE NOTES", 105, 25, { align: "center" });
            doc.setFontSize(10);
            doc.setFont("helvetica", "normal");
            doc.text("SESSION FÉVRIER 2026 • PORTAIL NUMÉRIQUE ESMA", 105, 35, { align: "center" });

            // --- FILIGRANE (Watermark) ---
            doc.setTextColor(240, 240, 240);
            doc.setFontSize(60);
            doc.setFont("helvetica", "bold");
            doc.text("ESMA RIVIERA II", 105, 150, { align: "center", angle: 45, opacity: 0.1 });

            // --- BLOC ÉTUDIANT ---
            doc.setFillColor(245, 245, 245);
            doc.roundedRect(15, 60, 180, 45, 3, 3, 'F');

            doc.setTextColor(...primaryColor);
            doc.setFontSize(10);
            doc.text("ÉTUDIANT :", 20, 70);
            doc.setFontSize(16);
            doc.setFont("helvetica", "bold");
            doc.text(studentName, 20, 80);

            doc.setFontSize(10);
            doc.setFont("helvetica", "normal");
            doc.text("MATRICULE : " + studentMatricule, 20, 90);
            doc.text("FILIÈRE : " + studentFiliere, 20, 98);  


            // Badge Statut
            doc.setFillColor(...dangerColor);
            doc.roundedRect(140, 68, 45, 12, 2, 2, 'F');
            doc.setTextColor(255, 255, 255);
            doc.setFontSize(10);
            doc.text("STATUT : AJOURNÉ", 162.5, 76, { align: "center" });

            // --- TABLEAU DES RÉSULTATS ---
            const startY = 120;
            doc.setTextColor(...primaryColor);
            doc.setFontSize(12);
            doc.setFont("helvetica", "bold");
            doc.text("UNITÉS D'ENSEIGNEMENT", 15, startY - 5);

            // Header Tableau
            doc.setFillColor(...primaryColor);
            doc.rect(15, startY, 180, 12, 'F');
            doc.setTextColor(255, 255, 255);
            doc.setFontSize(10);
           // doc.text("INTITULÉ DE LA MATIÈRE", 20, startY + 8);
            //doc.text("COEFF", 130, startY + 8);
            doc.text("MOYENNE", 165, startY + 8);

            // Lignes Tableau
           //const data = modulesData.map(matiere => [ matiere.nom_mat, matiere.coef, parseFloat(m.note).toFixed(2)]);

            let currentY = startY + 12;
            doc.setTextColor(50, 50, 50);
            doc.setFont("helvetica", "normal");

            data.forEach((row, i) => {
                if (i % 2 === 0) doc.setFillColor(250, 250, 250);
                else doc.setFillColor(255, 255, 255);

                doc.rect(15, currentY, 180, 10, 'F');
                doc.text(row[0], 20, currentY + 7);
                doc.text(row[1], 135, currentY + 7, { align: "center" });

                // Mettre les notes < 10 en rouge
                if (parseFloat(row[2]) < 10) doc.setTextColor(...dangerColor);
                doc.text(row[2], 175, currentY + 7, { align: "center" });
                doc.setTextColor(50, 50, 50);

                currentY += 10;
            });

            // --- RÉCAPITULATIF FINAL ---
            const summaryY = currentY + 20;
            doc.setDrawColor(...primaryColor);
            doc.setLineWidth(1);
            doc.line(120, summaryY, 195, summaryY);

            doc.setFont("helvetica", "bold");
            doc.setFontSize(12);
            doc.text("MOYENNE GÉNÉRALE :", 120, summaryY + 10);
            doc.setTextColor(...dangerColor);
            doc.setFontSize(18);
            doc.text(studentMoyenne + " / 20", 195, summaryY + 10, { align: "right" });

              if (parseFloat(studentMoyenne) < 10) {
            doc.text("SESSION : RATTRAPAGE", 195, summaryY + 18, { align: "right" });
        } else {
               doc.text("MENTION : " + studentMention.toUpperCase(), 195, summaryY + 18, { align: "right" });
          }

            doc.setTextColor(...primaryColor);
            doc.setFontSize(10);
           
            // --- SIGNATURE & DATE ---
            const footerY = 250;
            doc.setTextColor(100, 100, 100);
            doc.setFontSize(9);
            doc.setFont("helvetica", "italic");
            doc.text("Fait à Abidjan, le " + new Date().toLocaleDateString('fr-FR'), 15, footerY);

            doc.setFont("helvetica", "normal");
            doc.text("La Direction Académique", 160, footerY);

            doc.setDrawColor(...accentColor);
            doc.setLineWidth(0.2);
            doc.circle(175, footerY + 15, 12);
            doc.setFontSize(6);
            doc.text("CERTIFIÉ", 175, footerY + 14, { align: "center" });
            doc.text("DIGITAL", 175, footerY + 17, { align: "center" });

            // --- FOOTER LÉGAL ---
            doc.setFillColor(240, 240, 240);
            doc.rect(0, 280, 210, 17, 'F');
            doc.setTextColor(150, 150, 150);
            doc.setFontSize(8);
            doc.setFont("helvetica", "normal");
            doc.text("ESMA Riviera II - École de Specialités Multimedia d'Abidjan", 105, 287, { align: "center" });
            doc.text("Portail Étudiant • Document officiel à caractère provisoire", 105, 292, { align: "center" });

            doc.save(`Bulletin_Notes_ESMA_${studentName.replace(/ /g, "_")}_Ajourne.pdf`);
    </script>
    <script src="../assets/js/bootstrap.min.js"></script>
</body>

</html>