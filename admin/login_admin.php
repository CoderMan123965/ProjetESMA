<?php
session_start();
include "../config/database.php";

$message = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $email = $_POST["email"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM admin WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if($admin && $password == $admin['mot_de_passe']) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_nom'] = $admin['nom'];

        header("Location: ../admin/admin_dash.php"); 
        exit();
    } else {
        $message = "Email ou mot de passe incorrect";
    }
}


?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Administration - ESMA</title>
    <!-- Bootstrap CSS (pour le carousel + icônes éventuelles) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/login-admin.css">
</head>
<body>
    <div class="admin-login-page">
        <div class="admin-login-card">
            <!-- Colonne gauche : formulaire -->
            <div class="admin-login-left">
                <div class="brand-header">
                    <div class="brand-icon">
                        <span class="brand-icon-inner">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </span>
                    </div>
                    <div class="brand-text">
                        <span class="brand-title">PORTAIL ÉDUCATIF</span>
                        <span class="brand-subtitle">Système de gestion académique</span>
                    </div>
                </div>

                <div class="login-content">
                    <h1 class="login-title">Connexion Administration</h1>
                    <p class="login-description">
                        Accès sécurisé au tableau de bord de gestion des résultats.
                    </p>

                    <?php if($message): ?>
                        <div class="alert-error">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="login-form">
                        <div class="form-group">
                            <label for="email">Identifiant</label>
                            <div class="input-wrapper">
                                <span class="input-icon"><i class="fa-solid fa-user"></i></span>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    placeholder="Ex : admin_results@esma.ci"
                                    required
                                >
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="label-row">
                                <label for="password">Mot de passe</label>
                                <a href="#" class="link-muted">Mot de passe oublié ?</a>
                            </div>
                            <div class="input-wrapper">
                                <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    placeholder="••••••••••"
                                    required
                                >
                                <button type="button" class="toggle-password" aria-label="Afficher le mot de passe">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn-primary">
                            <span>Se connecter</span>
                            <span class="btn-icon"><i class="fa-solid fa-right-to-bracket"></i></span>
                        </button>

                        <div class="login-footer-links">
                            <a href="../index.html">Retour au portail des résultats</a>
                        </div>
                    </form>
                </div>

                <div class="login-footer-meta">
                    <span>© <?php echo date('Y'); ?> ESMA - Système de Gestion Académique</span>
                    <div class="meta-links">
                        <a href="#">Aide</a>
                        <span class="dot">•</span>
                        <a href="#">Confidentialité</a>
                    </div>
                </div>
            </div>

            <!-- Colonne droite : carousel plein écran + texte en overlay -->
            <div class="admin-login-right">
                <div id="esmaCarousel" class="carousel slide h-100" data-bs-ride="carousel" data-bs-interval="6000">
                    <div class="carousel-indicators">
                        <button type="button" data-bs-target="#esmaCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                        <button type="button" data-bs-target="#esmaCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                        <button type="button" data-bs-target="#esmaCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
                    </div>
                    <div class="carousel-inner h-100">
                        <div class="carousel-item active">
                            <img src="../assets/images/remise.jpg" class="d-block w-100 carousel-img" alt="Remise de diplômes ESMA">
                        </div>
                        <div class="carousel-item">
                            <img src="../assets/images/groupe-esma.jpg" class="d-block w-100 carousel-img" alt="Campus ESMA et étudiants">
                        </div>
                        <div class="carousel-item">
                            <img src="../assets/images/esma.jpg" class="d-block w-100 carousel-img" alt="Vue de l'ESMA">
                        </div>
                    </div>

                    <!-- Overlay texte sur les images -->
                    <div class="right-overlay">
                        <div class="badge-secure">
                            <span class="badge-icon">
                                <i class="fa-solid fa-shield-halved"></i>
                            </span>
                            <span class="badge-text">Système de Certification Sécurisé</span>
                        </div>
                        <h2>Contrôlez l’intégrité des résultats académiques</h2>
                        <p>
                            Gérez les validations, les imports de notes et le reporting des résultats
                            en temps réel, au sein d’un environnement protégé pour l’équipe
                            administrative de l’ESMA.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle affichage mot de passe
        const passwordInput = document.getElementById('password');
        const toggleBtn = document.querySelector('.toggle-password');

        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', () => {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                const icon = toggleBtn.querySelector('i');
                if (icon) {
                    icon.classList.toggle('fa-eye');
                    icon.classList.toggle('fa-eye-slash');
                }
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>



