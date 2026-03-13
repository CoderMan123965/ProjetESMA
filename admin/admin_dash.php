<?php
session_start();
include "../config/database.php";

// Ici c'est pourvérifier si admin connecté
if(!isset($_SESSION['admin_id'])){
    header("Location: ../admin/login_admin.php");
    exit();
}

// Messages pour la page Paramètres
$settingsSuccess = "";
$settingsError   = "";

// Traitement des formulaires Paramètres
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])) {
    $action = $_POST["action"];

    if ($action === "create_admin") {
        $nom  = trim($_POST["nom_admin"] ?? "");
        $email = trim($_POST["email_admin"] ?? "");
        $password = trim($_POST["password_admin"] ?? "");

        if ($nom === "" || $email === "" || $password === "") {
            $settingsError = "Veuillez renseigner tous les champs pour créer un administrateur.";
        } else {
            // vérifier si email déjà utilisé
            $check = $pdo->prepare("SELECT id FROM admin WHERE email = :email");
            $check->bindParam(":email", $email);
            $check->execute();

            if ($check->fetch()) {
                $settingsError = "Cet email est déjà utilisé par un autre administrateur.";
            } else {
                $insert = $pdo->prepare("INSERT INTO admin (nom, email, mot_de_passe) VALUES (:nom, :email, :pwd)");
                $insert->bindParam(":nom", $nom);
                $insert->bindParam(":email", $email);
                // Fais attention en prod Sarah , n'oublie pas d'hasher le mot de passse
                $insert->bindParam(":pwd", $password);
                $insert->execute();

                $settingsSuccess = "Nouvel administrateur créé avec succès.";
            }
        }
    } elseif ($action === "change_password") {
        $oldPassword = trim($_POST["old_password"] ?? "");
        $newPassword = trim($_POST["new_password"] ?? "");
        $confirmPassword = trim($_POST["confirm_password"] ?? "");

        if ($oldPassword === "" || $newPassword === "" || $confirmPassword === "") {
            $settingsError = "Veuillez remplir tous les champs pour changer votre mot de passe.";
        } elseif ($newPassword !== $confirmPassword) {
            $settingsError = "La confirmation du mot de passe ne correspond pas.";
        } else {
            // récupérer le mot de passe actuel de l'admin connecté
            $adminId = (int) $_SESSION["admin_id"];
            $stmtPwd = $pdo->prepare("SELECT mot_de_passe FROM admin WHERE id = :id");
            $stmtPwd->bindParam(":id", $adminId, PDO::PARAM_INT);
            $stmtPwd->execute();
            $row = $stmtPwd->fetch(PDO::FETCH_ASSOC);

            if (!$row || $row["mot_de_passe"] !== $oldPassword) {
                $settingsError = "L'ancien mot de passe est incorrect.";
            } else {
                $upd = $pdo->prepare("UPDATE admin SET mot_de_passe = :pwd WHERE id = :id");
                $upd->bindParam(":pwd", $newPassword);
                $upd->bindParam(":id", $adminId, PDO::PARAM_INT);
                $upd->execute();

                $settingsSuccess = "Votre mot de passe a été mis à jour avec succès.";
            }
        }
    }
}

// ICI c'est pour les statistiques globaux
$totalEtudiants   = (int) $pdo->query("SELECT COUNT(*) FROM etudiant")->fetchColumn();
$totalAdmis       = (int) $pdo->query("SELECT COUNT(*) FROM etudiant WHERE moyenne >= 10")->fetchColumn();
$moyenneGlobale   = (float) $pdo->query("SELECT AVG(moyenne) FROM etudiant WHERE moyenne IS NOT NULL")->fetchColumn();
$tauxReussite     = $totalEtudiants > 0 ? round($totalAdmis * 100 / $totalEtudiants, 1) : 0;

// ICI c'est pour les statistiques sur les fichiers (imports)
$sql = "SELECT f.id_fichier, f.nom_fichier, COUNT(e.id_etudiant) AS nb_etudiants
        FROM fichiers f
        LEFT JOIN etudiant e ON e.id_fichier = f.id_fichier
        GROUP BY f.id_fichier, f.nom_fichier
        ORDER BY f.id_fichier DESC";
$stmt = $pdo->query($sql);
$fichiers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Liste des étudiants (derniers ajoutés)
$sqlEtudiants = "SELECT matricule, nom, prenom, filiere, niveau, moyenne 
                 FROM etudiant 
                 ORDER BY id_etudiant DESC 
                 LIMIT 50";
$etudiantsStmt = $pdo->query($sqlEtudiants);
$etudiants = $etudiantsStmt->fetchAll(PDO::FETCH_ASSOC);

// Liste des administrateurs
$sqlAdmins = "SELECT id, nom, email FROM admin ORDER BY id ASC";
$adminsStmt = $pdo->query($sqlAdmins);
$admins = $adminsStmt->fetchAll(PDO::FETCH_ASSOC);

?>
  
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Administration ESMA</title> 
    <!-- Bootstrap & icônes -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/upload.css">   
</head>
<body>
    <div class="admin-layout" id="top">
        <!-- Contenu principal -->
        <main class="admin-main">
            <!-- Topbar -->
            <header class="admin-topbar">
                <div class="topbar-left">
                    <h1>Tableau de bord</h1>
                    <span class="topbar-subtitle">Vue d’ensemble des résultats académiques</span>
                </div>
                <div class="topbar-right">
                    <div class="search-box">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" placeholder="Rechercher un étudiant, un fichier…">
                    </div>
                    <div class="topbar-user">
                        <div class="user-initials">
                            <?php echo strtoupper(substr($_SESSION['admin_nom'] ?? 'A', 0, 1)); ?>
                        </div>
                        <div class="user-text">
                            <span class="user-name"><?php echo htmlspecialchars($_SESSION['admin_nom'] ?? 'Administrateur'); ?></span>
                            <span class="user-role">Contrôle des résultats</span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- PAGE DASHBOARD -->
            <section class="section-page active" id="section-dashboard">
            <!-- Cartes de stats -->
            <div class="stats-grid">
                <article class="stat-card">
                    <div class="stat-icon students">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-label">Total étudiants</span>
                        <span class="stat-value"><?php echo $totalEtudiants; ?></span>
                    </div>
                </article>

                <article class="stat-card">
                    <div class="stat-icon success">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-label">Taux de réussite</span>
                        <span class="stat-value"><?php echo $tauxReussite; ?>%</span>
                    </div>
                </article>

                <article class="stat-card">
                    <div class="stat-icon files">
                        <i class="fa-solid fa-file-import"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-label">Fichiers importés</span>
                        <span class="stat-value"><?php echo count($fichiers); ?></span>
                    </div>
                </article>

                <article class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fa-solid fa-chart-column"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-label">Note moyenne</span>
                        <span class="stat-value">
                            <?php echo $moyenneGlobale ? number_format($moyenneGlobale, 2, ',', ' ') : '0,00'; ?>
                        </span>
                    </div>
                </article>
            </div>

            <!-- Bloc principal : imports + upload -->
            <section class="panel-grid">
                <!-- Historique des imports -->
                <div class="panel" id="section-imports">
                    <div class="panel-header">
                        <div>
                            <h2>Historique des imports</h2>
                            <p>Fichiers CSV chargés et nombre d’étudiants associés.</p>
                        </div>
                        <a href="#section-upload" class="btn-primary-small">
                            <i class="fa-solid fa-plus"></i>
                            <span>Nouvel import</span>
                        </a>
                    </div>

                    <div class="table-wrapper">
                        <table class="table table-borderless align-middle imports-table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Nom du fichier</th>
                                    <th scope="col">Étudiants chargés</th>
                                    <th scope="col" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($fichiers) === 0): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            Aucun fichier importé pour le moment.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($fichiers as $fichier): ?>
                                        <tr>
                                            <td><?php echo (int) $fichier['id_fichier']; ?></td>
                                            <td><?php echo htmlspecialchars($fichier['nom_fichier']); ?></td>
                                            <td>
                                                <span class="pill pill-import">
                                                    <?php echo (int) $fichier['nb_etudiants']; ?> étudiants
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <a  class="action-delete icon-only" href="../admin/delete.php?id=<?php echo (int) $fichier['id_fichier']; ?>" 
                                                   onclick="return confirm('Voulez-vous vraiment supprimer ce fichier et les étudiants associés ?');"
                                                   title="Supprimer cet import">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Formulaire d'upload -->
                <div class="panel" id="section-upload">
                    <div class="panel-header">
                        <div>
                            <h2>Importer un fichier de résultats</h2>
                            <p>Charger un fichier CSV contenant les informations des étudiants et leurs moyennes.</p>
                        </div>
                    </div>
                    <form action="upload.php" method="POST" enctype="multipart/form-data" class="upload-form">
                        <label>Choisir un fichier CSV :</label>
                        <input type="file" name="fichier" accept=".csv" required>
                        <button type="submit">Télécharger et importer</button>
                        <p class="legal-notice">
                            * Vous vous engagez à respecter la confidentialité des données à caractère personnel contenues dans ce fichier.
                        </p>
                    </form>
                </div>
            </section>
            </section>

            <!-- PAGE ÉTUDIANTS -->
            <section class="section-page" id="section-etudiants">
            <div class="panel panel-full">
                <div class="panel-header">
                    <div>
                        <h2>Étudiants enregistrés</h2>
                        <p>Derniers étudiants importés dans le système.</p>
                    </div>
                </div>

                <div class="table-wrapper">
                    <table class="table table-borderless align-middle students-table">
                        <thead>
                            <tr>
                                <th scope="col">Matricule</th>
                                <th scope="col">Nom</th>
                                <th scope="col">Prénom</th>
                                <th scope="col">Filière</th>
                                <th scope="col">Niveau</th>
                                <th scope="col">Moyenne (/20)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($etudiants) === 0): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        Aucun étudiant enregistré pour le moment.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($etudiants as $etu): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($etu['matricule']); ?></td>
                                        <td><?php echo htmlspecialchars($etu['nom']); ?></td>
                                        <td><?php echo htmlspecialchars($etu['prenom']); ?></td>
                                        <td><?php echo htmlspecialchars($etu['filiere']); ?></td>
                                        <td>
                                            <span class="pill pill-level">
                                                <?php echo htmlspecialchars($etu['niveau']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                                if ($etu['moyenne'] !== null) {
                                                    $m = (float) $etu['moyenne'];
                                                    $class = $m >= 10 ? 'pill-grade pill-grade-ok' : 'pill-grade pill-grade-bad';
                                                    echo '<span class="'.$class.'">'.number_format($m, 2, ',', ' ').'</span>';
                                                } else {
                                                    echo '<span class="text-muted">N/A</span>';
                                                }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            </section>

            <!-- PAGE PARAMÈTRES / UTILISATEURS -->
            <section class="section-page" id="section-settings">
                <div class="panel panel-full">
                    <div class="panel-header">
                        <div>
                            <h2>Paramètres & Utilisateurs</h2>
                            <p>Gérez les comptes administrateur du portail des résultats.</p>
                        </div>
                    </div>

                    <?php if ($settingsSuccess): ?>
                        <div class="alert alert-success py-2 px-3 mb-3">
                            <?php echo htmlspecialchars($settingsSuccess); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($settingsError): ?>
                        <div class="alert alert-danger py-2 px-3 mb-3">
                            <?php echo htmlspecialchars($settingsError); ?>
                        </div>
                    <?php endif; ?>

                    <div class="row g-3">
                        <!-- Liste des admins -->
                        <div class="col-lg-7">
                            <div class="table-wrapper">
                                <table class="table table-borderless align-middle">
                                    <thead>
                                        <tr>
                                            <th scope="col">ID</th>
                                            <th scope="col">Nom</th>
                                            <th scope="col">Email</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($admins) === 0): ?>
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-4">
                                                    Aucun administrateur trouvé.
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($admins as $admin): ?>
                                                <tr>
                                                    <td><?php echo (int) $admin['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($admin['nom']); ?></td>
                                                    <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Formulaires rapides -->
                        <div class="col-lg-5">
                            <div class="settings-card">
                                <h3>Créer un nouvel admin</h3>
                                <p class="small text-muted mb-2">Ajoutez un nouvel utilisateur qui pourra se connecter à l’interface d’administration.</p>
                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="create_admin">
                                    <div class="mb-2">
                                        <label class="form-label">Nom complet</label>
                                        <input type="text" name="nom_admin" class="form-control form-control-sm" placeholder="Nom & prénom" required>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email_admin" class="form-control form-control-sm" placeholder="admin@esma.ci" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Mot de passe</label>
                                        <input type="password" name="password_admin" class="form-control form-control-sm" placeholder="********" required>
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-primary w-100">
                                        Créer l'administrateur
                                    </button>
                                </form>
                            </div>

                            <div class="settings-card mt-3">
                                <h3>Changer mon mot de passe</h3>
                                <p class="small text-muted mb-2">Modifiez le mot de passe du compte actuellement connecté.</p>
                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="change_password">
                                    <div class="mb-2">
                                        <label class="form-label">Ancien mot de passe</label>
                                        <input type="password" name="old_password" class="form-control form-control-sm" required>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Nouveau mot de passe</label>
                                        <input type="password" name="new_password" class="form-control form-control-sm" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Confirmer le mot de passe</label>
                                        <input type="password" name="confirm_password" class="form-control form-control-sm" required>
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-primary w-100">
                                        Mettre à jour mon mot de passe
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Tab bar type iPhone en bas -->
    <div class="admin-tabbar">
        <div class="admin-tabbar-inner">
            <a href="#section-dashboard" class="tab-item active" data-target="section-dashboard">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Dashboard</span>
            </a>
            <a href="#section-etudiants" class="tab-item" data-target="section-etudiants">
                <i class="fa-solid fa-users"></i>
                <span>Étudiants</span>
            </a>
            <a href="#section-settings" class="tab-item" data-target="section-settings">
                <i class="fa-solid fa-gear"></i>
                <span>Paramètres</span>
            </a>
            <a href="../auth/logout.php" class="tab-item">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                <span>Déconnexion</span>
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navigation par tab bar : bascule de "pages"
        const pages = document.querySelectorAll('.section-page');

        document.querySelectorAll('.admin-tabbar .tab-item[data-target]').forEach(link => {
            link.addEventListener('click', function (e) {
                const targetId = this.getAttribute('data-target');
                if (!targetId) return; // bouton Quitter
                e.preventDefault();

                // état actif sur la tab bar
                document.querySelectorAll('.admin-tabbar .tab-item').forEach(l => l.classList.remove('active'));
                this.classList.add('active');

                // affichage / masquage des pages
                pages.forEach(p => p.classList.remove('active'));
                const targetPage = document.getElementById(targetId);
                if (targetPage) {
                    targetPage.classList.add('active');
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            });
        });
    </script>
</body>
</html>