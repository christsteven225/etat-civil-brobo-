<?php
$conn = new mysqli('localhost', 'root', '', 'mairie_brobo');

// Vérifier la connexion
if ($conn->connect_error) {
  die("Erreur de connexion: " . $conn->connect_error);
}

// Mot de passe maître (à changer par toi seul)
$mot_de_passe_maitre = 'PCT';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $maitre = $_POST['maitre'];
  $nom = $_POST['nom'];
  $email = $_POST['email'];
  $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);

  if ($maitre !== $mot_de_passe_maitre) {
    echo "<p style='color:red;'>Mot de passe maître incorrect !</p>";
  } else {
    // Vérifiez si l'e-mail existe déjà
    $stmt = $conn->prepare("SELECT COUNT(*) FROM administrateurs WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
      echo "<p style='color:red;'>Erreur : L'adresse e-mail existe déjà.</p>";
    } else {
      // Si l'e-mail n'existe pas, procédez à l'insertion
      $stmt = $conn->prepare("INSERT INTO administrateurs (nom, email, mot_de_passe) VALUES (?, ?, ?)");
      $stmt->bind_param('sss', $nom, $email, $mot_de_passe);

      if ($stmt->execute()) {
        echo "<p style='color:green;'>Administrateur ajouté avec succès.</p>";
      } else {
        echo "<p style='color:red;'>Erreur: " . $stmt->error . "</p>";
      }
      $stmt->close();
    }
  }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Formulaire d'inscription</title>
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f9fafb;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }

    .container {
      background: white;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 0 12px rgba(0, 0, 0, 0.1);
      max-width: 400px;
      width: 100%;
    }

    h2 {
      text-align: center;
      font-family: 'Pacifico', cursive;
      margin-bottom: 1.5rem;
      color: black;
    }

    label {
      display: block;
      margin-bottom: 0.3rem;
      font-weight: 600;
      color: #374151;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"],
    input[type="date"] {
      width: 100%;
      padding: 0.5rem 0.75rem;
      margin-bottom: 1rem;
      border: 1.5px solid #d1d5db;
      border-radius: 8px;
      font-size: 1rem;
      transition: border-color 0.2s;
    }

    input[type="text"]:focus,
    input[type="email"]:focus,
    input[type="password"]:focus,
    input[type="date"]:focus {
      outline: none;
      border-color: #3b82f6;
      box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3);
    }

    button {
      width: 100%;
      background-color: #3b82f6;
      color: white;
      border: none;
      padding: 0.75rem;
      font-size: 1.1rem;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 700;
      transition: background-color 0.3s;
    }

    button:hover {
      background-color: #2563eb;
    }
  </style>
</head>

<body>
  <form class="space-y-6" method="POST">
    <div>
      <label class="block mb-2 text-sm font-medium text-gray-700">
        Mot de passe maitre
      </label>
      <input class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="email" name="maitre" required="" type="password" />
    </div>
    <div>
      <label class="block mb-2 text-sm font-medium text-gray-700">
        Nom
      </label>
      <input class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="email" name="nom" placeholder="" required="" type="text" />
    </div>
    <div>
      <label class="block mb-2 text-sm font-medium text-gray-700" for="email">
        Email
      </label>
      <input class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="email" name="email" placeholder="votre.email@example.com" required="" type="email" />
    </div>
    <div>
      <label class="block mb-2 text-sm font-medium text-gray-700" for="password">
        Mot de passe
      </label>
      <input class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="password" name="mot_de_passe" placeholder="" required="" type="password" />
    </div>
    <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" type="submit">
      Ajouter l'Administrateur
    </button>
  </form>
</body>

</html>