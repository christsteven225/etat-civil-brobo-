<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'mairie_brobo');

if ($conn->connect_error) {
    die("Erreur de connexion: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];

    // Validation côté serveur
    $errors = [];
    if (empty($nom)) $errors[] = "Le nom est requis";
    if (empty($prenom)) $errors[] = "Le prénom est requis";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide";
    if (strlen($mot_de_passe) < 8) $errors[] = "Le mot de passe doit contenir au moins 8 caractères";

    if (empty($errors)) {
        // Vérifier si l'email existe déjà
        $check = $conn->prepare("SELECT id FROM citoyens WHERE email = ?");
        $check->bind_param('s', $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $_SESSION['error'] = "Cet email est déjà inscrit.";
        } else {
            $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO citoyens (nom, prenom, email, mot_de_passe) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('ssss', $nom, $prenom, $email, $mot_de_passe_hash);

            if ($stmt->execute()) {
                $_SESSION['user_email'] = $email;
                $_SESSION['success'] = "Inscription réussie. Bienvenue!";
                header("Location: connexion_citoyen.php");
                exit();
            } else {
                $_SESSION['error'] = "Erreur lors de l'inscription: " . $stmt->error;
            }
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <link rel="shortcut icon" type="x-icon" href="../public/image/icone2.png">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Formulaire d'inscription</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Pacifico&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #3b82f6;
      --primary-hover: #2563eb;
      --error: #ef4444;
      --success: #10b981;
      --gray-light: #d1d5db;
      --gray-dark: #374151;
    }
    
    * {
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f9fafb;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      margin: 0;
      padding: 1rem;
      line-height: 1.5;
    }
    
    .container {
      background: white;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 0 12px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 400px;
      transition: all 0.3s ease;
    }
    
    h2 {
      text-align: center;
      font-family: 'Pacifico', cursive;
      margin-bottom: 1.5rem;
      color: var(--gray-dark);
      font-size: 1.8rem;
    }
    
    .form-group {
      margin-bottom: 1.2rem;
      position: relative;
    }
    
    label {
      display: block;
      margin-bottom: 0.3rem;
      font-weight: 600;
      color: var(--gray-dark);
      font-size: 0.9rem;
    }
    
    input[type="text"],
    input[type="email"],
    input[type="password"],
    input[type="date"] {
      width: 100%;
      padding: 0.75rem;
      border: 1.5px solid var(--gray-light);
      border-radius: 8px;
      font-size: 1rem;
      transition: all 0.2s;
    }
    
    input:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    }
    
    button {
      width: 100%;
      background-color: var(--primary);
      color: white;
      border: none;
      padding: 0.75rem;
      font-size: 1.1rem;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 700;
      transition: all 0.3s;
      margin-top: 0.5rem;
    }
    
    button:hover {
      background-color: var(--primary-hover);
      transform: translateY(-1px);
    }
    
    .error-message {
      color: var(--error);
      font-size: 0.8rem;
      margin-top: 0.2rem;
      display: none;
    }
    
    .alert {
      padding: 0.75rem 1rem;
      border-radius: 8px;
      margin-bottom: 1rem;
      font-size: 0.9rem;
    }
    
    .alert-error {
      background-color: #fee2e2;
      color: var(--error);
      border: 1px solid #fecaca;
    }
    
    .alert-success {
      background-color: #d1fae5;
      color: var(--success);
      border: 1px solid #a7f3d0;
    }
    
    .input-error {
      border-color: var(--error) !important;
    }
    
    .password-strength {
      height: 4px;
      background-color: #e5e7eb;
      border-radius: 2px;
      margin-top: 0.5rem;
      overflow: hidden;
      display: none;
    }
    
    .password-strength-bar {
      height: 100%;
      width: 0;
      transition: width 0.3s, background-color 0.3s;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Inscription</h2>
    
    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert alert-error">
        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
      </div>
    <?php endif; ?>
    
    <form id="inscriptionForm" method="POST" action="">
      <div class="form-group">
        <label for="nom">Nom</label>
        <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required />
        <span class="error-message" id="nom-error">Veuillez entrer un nom valide</span>
      </div>
      
      <div class="form-group">
        <label for="prenom">Prénom</label>
        <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" required />
        <span class="error-message" id="prenom-error">Veuillez entrer un prénom valide</span>
      </div>
      
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required />
        <span class="error-message" id="email-error">Veuillez entrer un email valide</span>
      </div>
      
      <div class="form-group">
        <label for="mot_de_passe">Mot de passe</label>
        <input type="password" id="mot_de_passe" name="mot_de_passe" required />
        <div class="password-strength" id="password-strength">
          <div class="password-strength-bar" id="password-strength-bar"></div>
        </div>
        <span class="error-message" id="password-error">Le mot de passe doit contenir au moins 8 caractères</span>
      </div>
      
      <button type="submit">S'inscrire</button>
    </form>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('inscriptionForm');
      const nomInput = document.getElementById('nom');
      const prenomInput = document.getElementById('prenom');
      const emailInput = document.getElementById('email');
      const passwordInput = document.getElementById('mot_de_passe');
      const passwordStrength = document.getElementById('password-strength');
      const passwordStrengthBar = document.getElementById('password-strength-bar');
      
      // Validation en temps réel
      nomInput.addEventListener('input', validateNom);
      prenomInput.addEventListener('input', validatePrenom);
      emailInput.addEventListener('input', validateEmail);
      passwordInput.addEventListener('input', validatePassword);
      
      // Validation à la soumission
      form.addEventListener('submit', function(e) {
        const isNomValid = validateNom();
        const isPrenomValid = validatePrenom();
        const isEmailValid = validateEmail();
        const isPasswordValid = validatePassword();
        
        if (!(isNomValid && isPrenomValid && isEmailValid && isPasswordValid)) {
          e.preventDefault();
        }
      });
      
      function validateNom() {
        const value = nomInput.value.trim();
        const errorElement = document.getElementById('nom-error');
        
        if (value === '' || value.length < 2) {
          nomInput.classList.add('input-error');
          errorElement.style.display = 'block';
          return false;
        } else {
          nomInput.classList.remove('input-error');
          errorElement.style.display = 'none';
          return true;
        }
      }
      
      function validatePrenom() {
        const value = prenomInput.value.trim();
        const errorElement = document.getElementById('prenom-error');
        
        if (value === '' || value.length < 2) {
          prenomInput.classList.add('input-error');
          errorElement.style.display = 'block';
          return false;
        } else {
          prenomInput.classList.remove('input-error');
          errorElement.style.display = 'none';
          return true;
        }
      }
      
      function validateEmail() {
        const value = emailInput.value.trim();
        const errorElement = document.getElementById('email-error');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (!emailRegex.test(value)) {
          emailInput.classList.add('input-error');
          errorElement.style.display = 'block';
          return false;
        } else {
          emailInput.classList.remove('input-error');
          errorElement.style.display = 'none';
          return true;
        }
      }
      
      function validatePassword() {
        const value = passwordInput.value;
        const errorElement = document.getElementById('password-error');
        
        if (value.length < 8) {
          passwordInput.classList.add('input-error');
          errorElement.style.display = 'block';
          passwordStrength.style.display = 'none';
          return false;
        } else {
          passwordInput.classList.remove('input-error');
          errorElement.style.display = 'none';
          passwordStrength.style.display = 'block';
          updatePasswordStrength(value);
          return true;
        }
      }
      
      function updatePasswordStrength(password) {
        let strength = 0;
        
        if (password.length >= 8) strength += 20;
        if (password.length >= 12) strength += 20;
        if (/[A-Z]/.test(password)) strength += 20;
        if (/[0-9]/.test(password)) strength += 20;
        if (/[^A-Za-z0-9]/.test(password)) strength += 20;
        
        strength = Math.min(strength, 100);
        passwordStrengthBar.style.width = strength + '%';
        
        if (strength < 40) {
          passwordStrengthBar.style.backgroundColor = 'var(--error)';
        } else if (strength < 70) {
          passwordStrengthBar.style.backgroundColor = '#f59e0b';
        } else {
          passwordStrengthBar.style.backgroundColor = 'var(--success)';
        }
      }
    });
  </script>
</body>
</html>