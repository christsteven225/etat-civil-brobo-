<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'mairie_brobo');

if ($conn->connect_error) {
    die("Erreur de connexion: " . $conn->connect_error);
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = trim($_POST['email']);
        $mot_de_passe = $_POST['password'];

        $stmt = $conn->prepare("SELECT id, mot_de_passe FROM citoyens WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $citoyen = $result->fetch_assoc();
            if (password_verify($mot_de_passe, $citoyen['mot_de_passe'])) {
                $_SESSION['citoyen_id'] = $citoyen['id'];
                $success = "Connexion réussie. Redirection en cours...";
                echo "<script>
                        setTimeout(function() {
                            window.location.href = 'declarationactes.php';
                        }, 1500);
                      </script>";
            } else {
                $error = "Mot de passe incorrect.";
            }
        } else {
            $error = "Aucun compte trouvé avec cet email.";
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" type="x-icon" href="../public/image/icone2.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Citoyen</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .form-container {
            transition: all 0.3s ease;
        }

        .input-field:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .btn-hover:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .btn-hover:active {
            transform: translateY(0);
        }

        .error-message {
            animation: fadeIn 0.3s ease-in-out;
        }

        .success-message {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .password-container {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6b7280;
        }
    </style>
</head>

<body class="bg-gray-50">
    <main class="form-container w-full max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden p-8">
        <div class="text-center mb-8">
            <h1 class="text-2xl sm:text-3xl font-semibold text-gray-800 mb-2 font-pacifico text-blue-600">
                Connexion Citoyen
            </h1>
            <p class="text-gray-600">Accédez à votre espace personnel</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message mb-6 p-4 bg-red-50 text-red-600 rounded-lg border border-red-200 flex items-start">
                <i class="fas fa-exclamation-circle mt-1 mr-3"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message mb-6 p-4 bg-green-50 text-green-600 rounded-lg border border-green-200 flex items-start">
                <i class="fas fa-check-circle mt-1 mr-3"></i>
                <span><?php echo $success; ?></span>
            </div>
        <?php endif; ?>

        <form class="space-y-6" method="POST" id="loginForm">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="email">
                    Adresse e-mail
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-envelope text-gray-400"></i>
                    </div>
                    <input class="input-field w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                        id="email"
                        name="email"
                        placeholder="votre.email@example.com"
                        required
                        type="email"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="password">
                    Mot de passe
                </label>
                <div class="password-container relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-400"></i>
                    </div>
                    <input class="input-field w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                        id="password"
                        name="password"
                        placeholder="••••••••"
                        required
                        type="password">
                    <span class="toggle-password" onclick="togglePasswordVisibility()">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </span>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        id="remember"
                        name="remember"
                        type="checkbox">
                    <label class="ml-2 block text-sm text-gray-700" for="remember">
                        Se souvenir de moi
                    </label>
                </div>
                <a class="text-sm text-blue-600 hover:text-blue-800 hover:underline transition duration-200"
                    href="#">
                    Mot de passe oublié?
                </a>
            </div>

            <button class="btn-hover w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200 ease-in-out transform"
                type="submit">
                <i class="fas fa-sign-in-alt mr-2"></i> Se connecter
            </button>

            <div class="text-center text-sm text-gray-600 mt-4">
                Pas encore de compte?
                <a class="text-blue-600 hover:text-blue-800 hover:underline transition duration-200"
                    href="../View/inscription_citoyen.php">
                    Créer un compte
                </a>
            </div>
        </form>
    </main>

    <script>
        // Toggle password visibility
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Add animation on form load
        document.addEventListener('DOMContentLoaded', () => {
            const formContainer = document.querySelector('.form-container');
            formContainer.style.opacity = '0';
            formContainer.style.transform = 'translateY(20px)';

            setTimeout(() => {
                formContainer.style.opacity = '1';
                formContainer.style.transform = 'translateY(0)';
            }, 100);
        });

        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            if (!email || !password) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs');
            }
        });

        
    </script>
</body>

</html>