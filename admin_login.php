<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'mairie_brobo');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Vérifiez si les clés existent dans $_POST
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = $_POST['email'];
        $mot_de_passe = $_POST['password']; // Utilisez 'password' ici

        $stmt = $conn->prepare("SELECT * FROM administrateurs WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();

        // Vérifiez si l'administrateur existe et si le mot de passe est correct
        if ($admin && password_verify($mot_de_passe, $admin['mot_de_passe'])) { // Utilisez password_verify si le mot de passe est hashé
            $_SESSION['admin_id'] = $admin['id'];
            header('Location: ../admin/admin_dashboard.php');
            exit();
        } else {
            $erreur = "Email ou mot de passe incorrect.";
        }
    } else {
        $erreur = "Veuillez remplir tous les champs.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord des administrateurs</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter&amp;display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
        }
    </style>

</head>

<body>

    <!-- En-tête avec navigation -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="bg-[#FF6B00] text-white py-2">
            <div class="container mx-auto px-4 flex justify-between items-center">
                <div class="flex items-center">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/fe/Flag_of_C%C3%B4te_d%27Ivoire.svg/20px-Flag_of_C%C3%B4te_d%27Ivoire.svg.png" alt="Drapeau" class="w-6 h-4 mr-2">
                    <span class="text-sm font-medium">RÉPUBLIQUE DE CÔTE D'IVOIRE</span>
                </div>
                <div class="flex items-center">
                    <span class="text-sm">Union - Discipline - Travail</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Login form section -->
    <main class="max-w-md mx-auto mt-12 bg-white p-8 rounded-lg shadow-md">
        <h1 class="text-lg sm:text-xl font-semibold text-gray-800 mb-6 text-center font-pacifico">
            Bienvenue sur l'espace administrateur<br />veuillez vous connecter
        </h1>

        <?php if (isset($erreur)) echo "<div class='alert alert-danger'>$erreur</div>"; ?>

        <form class="space-y-6" method="POST">
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700" for="email">
                    Adresse e-mail
                </label>
                <input class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="email" name="email" placeholder="votre.email@example.com" required="" type="email" />
            </div>
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700" for="password">
                    Mot de passe
                </label>
                <input class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="password" name="password" placeholder="••••••••" required="" type="password" />
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-30 rounded" id="remember" name="remember" type="checkbox" />
                    <label class="ml-2 block text-sm text-gray-900" for="remember">
                        Se souvenir de moi
                    </label>
                </div>
                <a class="text-sm text-blue-600 hover:underline" href="#">
                    Mot de passe oublié ?
                </a>
            </div>
            <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" type="submit">
                Se connecter
            </button>
        </form>
    </main>


    <!-- Pied de Page  -->
    <footer class="bg-[#1c2735] text-[#a0a9b8]">
        <div class="max-w-7xl mx-auto px-6 py-12 flex flex-col md:flex-row md:justify-between md:items-start gap-10 md:gap-0">
            <div class="md:w-1/4 space-y-4">
                <div class="text-white font-script text-2xl select-none" style="font-family: 'Pacifico', cursive;">
                    BROBO
                </div>
                <p class="text-sm leading-relaxed max-w-[220px]">
                    Plateforme officielle de gestion des actes d'état civil de la commune de BroBo.
                </p>
                <div class="flex space-x-4">
                    <a aria-label="Facebook" class="w-8 h-8 rounded-full bg-[#2f3a4a] flex items-center justify-center hover:bg-[#3b4a6b] transition-colors" href="#">
                        <i class="fab fa-facebook-f text-[#a0a9b8] text-sm">
                        </i>
                    </a>
                    <a aria-label="Twitter" class="w-8 h-8 rounded-full bg-[#2f3a4a] flex items-center justify-center hover:bg-[#3b4a6b] transition-colors" href="#">
                        <i class="fab fa-twitter text-[#a0a9b8] text-sm">
                        </i>
                    </a>
                    <a aria-label="Instagram" class="w-8 h-8 rounded-full bg-[#2f3a4a] flex items-center justify-center hover:bg-[#3b4a6b] transition-colors" href="#">
                        <i class="fab fa-instagram text-[#a0a9b8] text-sm">
                        </i>
                    </a>
                </div>
            </div>
            <div class="md:w-1/5 space-y-2">
                <h3 class="text-white font-semibold text-sm mb-3">
                    Liens rapides
                </h3>
                <ul class="space-y-2 text-sm">
                    <li>
                        <a class="hover:text-white transition-colors" href="../View/index.html">
                            Accueil
                        </a>
                    </li>
                    <li>
                        <a class="hover:text-white transition-colors" href="../View/connexion_citoyen.php">
                            Demande d'actes
                        </a>
                    </li>
                    <li>
                        <a class="hover:text-white transition-colors" href="../View/citoyen_login.php">
                            Mes demandes
                        </a>
                    </li>
                    <li>
                        <a class="hover:text-white transition-colors" href="../admin/admin_notice.html">
                            Tableau de bord
                        </a>
                    </li>
                    <li>
                        <a class="hover:text-white transition-colors" href="../View/contact.html">
                            Contact
                        </a>
                    </li>
                </ul>
            </div>
            <div class="md:w-1/5 space-y-2">
                <h3 class="text-white font-semibold text-sm mb-3">
                    Informations
                </h3>
                <ul class="space-y-2 text-sm">
                    <li>
                        <a class="hover:text-white transition-colors" href="#">
                            À propos de
                        </a>
                    </li>
                    <li>
                        <a class="hover:text-white transition-colors" href="#">
                            Mentions légales
                        </a>
                    </li>
                    <li>
                        <a class="hover:text-white transition-colors" href="#">
                            Politique de confidentialité
                        </a>
                    </li>
                    <li>
                        <a class="hover:text-white transition-colors" href="#">
                            Conditions d'utilisation
                        </a>
                    </li>
                    <li>
                        <a class="hover:text-white transition-colors" href="#">
                            Plan du site
                        </a>
                    </li>
                </ul>
            </div>
            <div class="md:w-1/4 space-y-2 text-sm">
                <h3 class="text-white font-semibold text-sm mb-3">
                    Contact
                </h3>
                <ul class="space-y-3">
                    <li class="flex items-start gap-2">
                        <i class="fas fa-map-marker-alt mt-1 text-[#a0a9b8]">
                        </i>
                        <span>
                            Quatier Mairie, BroBo, Côte d'Ivoire
                        </span>
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fas fa-phone-alt text-[#a0a9b8]">
                        </i>
                        <span>
                            +225 27 30 64 00 00
                        </span>
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fas fa-envelope text-[#a0a9b8]">
                        </i>
                        <span>
                            contact@mairie-brobo.ci
                        </span>
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fas fa-clock text-[#a0a9b8]">
                        </i>
                        <span>
                            Lun-Ven : 8h30-17h30
                        </span>
                    </li>
                </ul>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-6 py-4 border-t border-[#2f3a4a] flex flex-col md:flex-row justify-between items-center text-xs text-[#6b7587]">
            <div>
                © 2025 La commune de BroBo. Tous droits réservés.
            </div>
            <div class="flex items-center space-x-6 mt-3 md:mt-0">
                <img alt="MTN CI logo" class="h-6 w-auto" height="24" src="MTN.png" width="60" />
                <img alt="Orange CI logo" class="h-6 w-auto" height="24" src="ORANGE.png" width="60" />
                <img alt="Moov CI logo" class="h-6 w-auto" height="24" src="MOOV.png" width="60" />
            </div>
            <button aria-label="Support chat" class="fixed bottom-6 right-6 bg-[#4a90e2] w-12 h-12 rounded-full flex items-center justify-center shadow-lg hover:bg-[#357abd] transition-colors">
                <i class="fas fa-headset text-white text-lg">
                </i>
            </button>
    </footer>
</body>

</html>