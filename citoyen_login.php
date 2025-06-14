<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'mairie_brobo');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $mot_de_passe = $_POST['mot_de_passe']; // Assurez-vous que le nom est correct

    // Préparation de la requête pour récupérer l'utilisateur par email
    $stmt = $conn->prepare("SELECT * FROM citoyens WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $citoyen = $result->fetch_assoc();

    // Vérifiez si l'utilisateur existe et si le mot de passe correspond avec password_verify()
    if ($citoyen && password_verify($mot_de_passe, $citoyen['mot_de_passe'])) {
        $_SESSION['citoyen_id'] = $citoyen['id'];
        header('Location: ../view/mes_demandes.php');
        exit();
    } else {
        $erreur = "Email ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Citoyen</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter&amp;display=swap" rel="stylesheet"/>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
        }
    </style>
</head>
<body>
    <!-- Login form section -->
    <main class="max-w-md mx-auto mt-12 bg-white p-8 rounded-lg shadow-md">
        <h1 class="text-lg sm:text-xl font-semibold text-gray-800 mb-6 text-center font-pacifico">
            Veuillez vous connecter
        </h1>
        
        <form class="space-y-6" method="POST">
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700" for="email">
                    Adresse e-mail
                </label>
                <input class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="email" name="email" placeholder="votre.email@example.com" required="" type="email"/>
            </div>
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700" for="mot_de_passe"> <!-- Changer l'ID ici -->
                    Mot de passe
                </label>
                <input class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="mot_de_passe" name="mot_de_passe" placeholder="••••••••" required="" type="password"/> <!-- Changer le nom ici -->
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" id="remember" name="remember" type="checkbox"/>
                    <label class="ml-2 block text-sm text-gray-900" for="remember">
                        Se souvenir de moi
                    </label>
                </div>
                <a class="text-sm text-blue-600 hover:underline" href="#">
                    Mot de passe oublié?
                
                </a>
     </div>
     <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" type="submit">
       Se connecter
     </button>
    </form>
   </main>
</body>
</html>