<?php
session_start();
if (!isset($_SESSION['citoyen_id'])) {
  header('Location: ../view/connexion_citoyen.php');
  exit();
}

$conn = new mysqli('localhost', 'root', '', 'mairie_brobo');
if ($conn->connect_error) {
  die("Erreur : " . $conn->connect_error);
}
$message = '';
$messageType = '';

function uploadFiles(array $fileInfo, string $uploadDir): array
{
  $uploadedFiles = [];
  foreach ($fileInfo['name'] as $index => $originalName) {
    if ($fileInfo['error'][$index] === UPLOAD_ERR_OK) {
      $tmpName = $fileInfo['tmp_name'][$index];
      $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
      $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'gif'];
      if (!in_array($ext, $allowed)) {
        continue; // Ignorer fichiers non autorisés
      }

      if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
      }

      $uniqueName = uniqid('file_', true) . '.' . $ext;
      $destination = $uploadDir . DIRECTORY_SEPARATOR . $uniqueName;

      if (move_uploaded_file($tmpName, $destination)) {
        $uploadedFiles[] = $uniqueName;
      } else {
        echo "Erreur lors du déplacement du fichier : $originalName";
      }
    } else {
      echo "Erreur de téléchargement pour le fichier : $originalName";
    }
  }
  return $uploadedFiles;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $citoyen_id = $_SESSION['citoyen_id'];
  $type_acte = $_POST['type_acte'];

  $success = false;

  // Dossier de téléchargement
  $uploadDir = __DIR__ . '/uploads/'; // Correction du chemin

  // Gestion des fichiers uploadés
  $fichiersUploades = [];
  if (isset($_FILES['fichiers']) && is_array($_FILES['fichiers']['name'])) {
    foreach ($_FILES['fichiers']['name'] as $key => $val) {
      // Créer un tableau temporaire en format attendu uploadFiles
      $filesArray = [
        'name' => (array) $_FILES['fichiers']['name'][$key],
        'type' => (array) $_FILES['fichiers']['type'][$key],
        'tmp_name' => (array) $_FILES['fichiers']['tmp_name'][$key],
        'error' => (array) $_FILES['fichiers']['error'][$key],
        'size' => (array) $_FILES['fichiers']['size'][$key],
      ];
      $fichiersUploades[$key] = uploadFiles($filesArray, $uploadDir);
    }
  }

  // Concaténer les fichiers uploadés en une chaîne
  $fichiersMerge = [];
  foreach ($fichiersUploades as $groupFiles) {
    if (is_array($groupFiles)) {
      $fichiersMerge = array_merge($fichiersMerge, $groupFiles);
    }
  }

  $fichiersStr = implode(',', $fichiersMerge);

  if ($type_acte == 'naissance') {
    $nom_complet = $_POST['nom_complet'];
    $sex = $_POST['sex'];
    $nom_pere = $_POST['nom_pere'];
    $nom_mere = $_POST['nom_mere'];
    $lieu_naissance = $_POST['lieu_naissance'];
    $date_naissance = $_POST['date_naissance'];

    $stmt = $conn->prepare("INSERT INTO demandes_actes (citoyen_id, type_acte, fichiers) VALUES (?, 'naissance', ?)");
    $stmt->bind_param('is', $citoyen_id, $fichiersStr);
    if ($stmt->execute()) {
      $demande_id = $stmt->insert_id;

      $stmt2 = $conn->prepare("INSERT INTO naissances (demande_id, nom_complet, sex, nom_pere, nom_mere, lieu_naissance, date_naissance) VALUES (?, ?, ?, ?, ?, ?, ?)");
      $stmt2->bind_param('issssss', $demande_id, $nom_complet, $sex, $nom_pere, $nom_mere, $lieu_naissance, $date_naissance);
      if ($stmt2->execute()) {
        $success = true;
      } else {
        $message = "Erreur : " . $stmt2->error;
      }
    } else {
      $message = "Erreur : " . $stmt->error;
    }
  } elseif ($type_acte == 'mariage') {
    $nom_epoux = $_POST['nom_epoux'];
    $nom_epouse = $_POST['nom_epouse'];
    $temoin_epoux = $_POST['temoin_epoux'];
    $temoin_epouse = $_POST['temoin_epouse'];
    $lieu_mariage = $_POST['lieu_mariage'];
    $date_mariage = $_POST['date_mariage'];

    $stmt = $conn->prepare("INSERT INTO demandes_actes (citoyen_id, type_acte, fichiers) VALUES (?, 'mariage', ?)");
    $stmt->bind_param('is', $citoyen_id, $fichiersStr);
    if ($stmt->execute()) {
      $demande_id = $stmt->insert_id;

      $stmt2 = $conn->prepare("INSERT INTO mariages (demande_id, nom_epoux, nom_epouse, temoin_epoux, temoin_epouse, lieu_mariage, date_mariage) VALUES (?, ?, ?, ?, ?, ?, ?)");
      $stmt2->bind_param('issssss', $demande_id, $nom_epoux, $nom_epouse, $temoin_epoux, $temoin_epouse, $lieu_mariage, $date_mariage);
      if ($stmt2->execute()) {
        $success = true;
      } else {
        $message = "Erreur : " . $stmt2->error;
      }
    } else {
      $message = "Erreur : " . $stmt->error;
    }
  } elseif ($type_acte == 'deces') {
    $nom_defunt = $_POST['nom_defunt'];
    $nom_pere_defunt = $_POST['nom_pere_defunt'];
    $nom_mere_defunt = $_POST['nom_mere_defunt'];
    $lieu_deces = $_POST['lieu_deces'];
    $date_deces = $_POST['date_deces'];
    $cause_deces = $_POST['cause_deces'];

    $stmt = $conn->prepare("INSERT INTO demandes_actes (citoyen_id, type_acte, fichiers) VALUES (?, 'deces', ?)");
    $stmt->bind_param('is', $citoyen_id, $fichiersStr);
    if ($stmt->execute()) {
      $demande_id = $stmt->insert_id;

      $stmt2 = $conn->prepare("INSERT INTO deces (demande_id, nom_defunt, nom_pere_defunt, nom_mere_defunt, lieu_deces, date_deces, cause_deces) VALUES (?, ?, ?, ?, ?, ?, ?)");
      $stmt2->bind_param('issssss', $demande_id, $nom_defunt, $nom_pere_defunt, $nom_mere_defunt, $lieu_deces, $date_deces, $cause_deces);
      if ($stmt2->execute()) {
        $success = true;
        $_SESSION['message'] = "Demande d'acte de décès enregistrée avec succès.";
        $_SESSION['messageType'] = "success";
      } else {
        $message = "Erreur : " . $stmt2->error;
      }
    } else {
      $message = "Erreur : " . $stmt->error;
    }
  }

  if ($success) {
    $_SESSION['message'] = "Demande envoyée avec succès. La validation de votre demande peut prendre jusqu'à 2 jours.";
    $_SESSION['messageType'] = "success";
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
  } else {
    $_SESSION['message'] = $message ?: "Une erreur est survenue.";
    $_SESSION['messageType'] = "error";
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
  }
}

if (isset($_SESSION['message'])) {
  $message = $_SESSION['message'];
  $messageType = $_SESSION['messageType'] ?? 'success';
  unset($_SESSION['message'], $_SESSION['messageType']);
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <link rel="shortcut icon" type="x-icon" href="../public/image/icone2.png">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Tableau de Bord - Gestion Des Actes D'Etat Civil</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#3b82f6',
            secondary: '#64748b',
            danger: '#ef4444',
            success: '#10b981'
          },
          borderRadius: {
            'none': '0px',
            'sm': '4px',
            DEFAULT: '8px',
            'md': '12px',
            'lg': '16px',
            'xl': '20px',
            '2xl': '24px',
            '3xl': '32px',
            'full': '9999px',
            'button': '8px'
          }
        }
      }
    }
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f9fafb;
    }

    .logo-font {
      font-family: 'Pacifico', cursive;
    }

    .error-message {
      color: #ef4444;
      font-size: 0.875rem;
      margin-top: 0.25rem;
    }

    .tab-content {
      animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    #messageOverlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.5);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    #messageBox {
      position: relative;
      background: white;
      padding: 2rem 3rem;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
      max-width: 90%;
      text-align: center;
      font-size: 1.25rem;
      font-weight: 600;
    }

    #messageBox.success {
      color: #16a34a;
    }

    #messageBox.error {
      color: #dc2626;
    }

    #messageCloseBtn {
      position: absolute;
      top: 8px;
      right: 14px;
      background: none;
      border: none;
      font-size: 1.5rem;
      font-weight: bold;
      cursor: pointer;
      color: #888;
      transition: color 0.2s;
    }

    #messageCloseBtn:hover {
      color: #333;
    }
  </style>
</head>

<body class="min-h-screen flex flex-col">
  <?php if ($message): ?>
    <div id="messageOverlay">
      <div id="messageBox" class="<?= htmlspecialchars($messageType) ?>">
        <button id="messageCloseBtn" aria-label="Fermer le message">&times;</button>
        <?= htmlspecialchars($message) ?>
      </div>
    </div>
    <script>
      document.getElementById('messageCloseBtn').addEventListener('click', function() {
        document.getElementById('messageOverlay').style.display = 'none';
      });
    </script>
  <?php endif; ?>

  <header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="bg-[#FF6B00] text-white py-2 px-4">
      <div class="container mx-auto flex justify-between items-center">
        <div class="flex items-center">
          <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/fe/Flag_of_C%C3%B4te_d%27Ivoire.svg/20px-Flag_of_C%C3%B4te_d%27Ivoire.svg.png"
            alt="Drapeau"
            class="w-6 h-4 mr-2" />
          <span class="text-sm font-medium">RÉPUBLIQUE DE CÔTE D'IVOIRE</span>
        </div>
        <div class="hidden md:flex items-center">
          <span class="text-sm">Union - Discipline - Travail</span>
        </div>
      </div>
    </div>
    <div class="container mx-auto px-4 py-4 flex items-center justify-between">
      <div class="flex items-center">
        <div>
          <h1 class="text-2xl logo-font text-primary">BROBO</h1>
          <p class="text-gray-600 text-sm">Commune de BROBO</p>
        </div>
      </div>
      <button id="mobileMenuButton" class="md:hidden text-gray-600 focus:outline-none">
        <i class="fas fa-bars text-2xl"></i>
      </button>
      <nav class="hidden md:flex space-x-6">
        <a href="../View/index.php" class="text-gray-600 hover:text-primary transition">Accueil</a>
        <a href="../View/connexion_decla.php" class="text-gray-600 hover:text-primary transition">Faire une Declaration</a>
        <a href="../View/connexion_citoyen.php" class="text-primary font-medium">Demande d'actes</a>
        <a href="../View/citoyen_login.php" class="text-gray-600 hover:text-primary transition">Mes demandes</a>
        <a href="../admin/admin_notice.html" class="text-gray-600 hover:text-primary transition">Tableau de bord</a>
        <a href="../View/contact.html" class="text-gray-600 hover:text-primary transition">Contact</a>
      </nav>
      <button id="menuToggle" class="md:hidden text-primary focus:outline-none">
        <i class="ri-menu-line text-2xl"></i>
      </button>
    </div>
  </header>
  <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-10">
    <h1 class="text-2xl md:text-3xl font-semibold text-gray-800 mb-6 md:mb-8" style="text-align: center;">
      Demande d'acte civil
    </h1>

    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
      <div class="flex overflow-x-auto pb-2 mb-6 scrollbar-hide">
        <button aria-selected="true" class="tab-button flex-shrink-0 px-4 py-2 border-b-2 border-blue-600 text-blue-600 font-semibold flex items-center"
          data-tab="naissance" type="button">
          <i class="fas fa-baby mr-2 text-sm"></i>Acte de naissance
        </button>
        <button aria-selected="false" class="tab-button flex-shrink-0 px-4 py-2 border-b-2 border-transparent text-gray-600 hover:text-blue-600 font-semibold flex items-center"
          data-tab="mariage" type="button">
          <i class="fas fa-ring mr-2 text-sm"></i>Acte de mariage
        </button>
        <button aria-selected="false" class="tab-button flex-shrink-0 px-4 py-2 border-b-2 border-transparent text-gray-600 hover:text-blue-600 font-semibold flex items-center"
          data-tab="deces" type="button">
          <i class="fas fa-cross mr-2 text-sm"></i>Acte de décès
        </button>
      </div>

      <div class="relative">
        <div id="formLoading" class="hidden absolute inset-0 bg-white bg-opacity-70 z-10 flex items-center justify-center rounded-lg">
          <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
        </div>

        <section class="tab-content space-y-4" id="naissance">
          <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4" enctype="multipart/form-data">
            <input type="hidden" name="type_acte" value="naissance">

            <div>
              <label class="block text-gray-700 text-sm font-medium mb-1" for="fichiers">
                Une copie de l'extrait de naissance (PDF ou Image)*
              </label>
              <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 form-control"
                id="fichiers"
                multiple
                accept=".pdf,image/*"
                name="fichiers[pdfExN][]"
                required
                type="file" />
              <p class="error-message hidden mt-1 text-sm text-red-600" id="naissance-nom-error"></p>
            </div>
            <!-- piece d'itentité  -->
            <div>
              <label class="block text-gray-700 text-sm font-medium mb-1" for="fichier">
                Pièce d'identité du Demandeur(PDF ou Image)*
              </label>
              <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 form-control"
                id="fichiers"
                multiple
                accept=".pdf,image/*"
                name="fichiers[pdfDemandeur]"
                required
                type="file"
                placeholder="CNI,Extrait de Naissance,Passeport ou certificat de nationalité" />
              <p class="error-message hidden mt-1 text-sm text-red-600" id="naissance-nom-error"></p>
            </div>

            <!-- Bouton de soumission -->
            <div class="md:col-span-2 pt-2">
              <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center"
                type="submit">
                <i class="fas fa-paper-plane mr-2"></i>Demande
              </button>
            </div>
          </form>
        </section>

        <!-- Formulaire de mariage -->
        <section class="tab-content hidden space-y-4" id="mariage">
          <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input type="hidden" name="type_acte" value="mariage">

            <!-- Nom époux -->
            <div>
              <label class="block text-gray-700 text-sm font-medium mb-1" for="mariage-epoux">
                Nom complet de l'époux*
              </label>
              <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                id="mariage-epoux"
                name="nom_epoux"
                placeholder="Nom complet"
                required
                type="text" />
              <p class="error-message hidden mt-1 text-sm text-red-600" id="mariage-epoux-error"></p>
            </div>

            <!-- Nom épouse -->
            <div>
              <label class="block text-gray-700 text-sm font-medium mb-1" for="mariage-epouse">
                Nom complet de l'épouse*
              </label>
              <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                id="mariage-epouse"
                name="nom_epouse"
                placeholder="Nom complet"
                required
                type="text" />
              <p class="error-message hidden mt-1 text-sm text-red-600" id="mariage-epouse-error"></p>
            </div>

            <!-- Témoin époux -->
            <div>
              <label class="block text-gray-700 text-sm font-medium mb-1" for="mariage-temoin-epoux">
                Témoin de l'époux*
              </label>
              <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                id="mariage-temoin-epoux"
                name="temoin_epoux"
                placeholder="Nom complet du témoin"
                required
                type="text" />
              <p class="error-message hidden mt-1 text-sm text-red-600" id="mariage-temoin-epoux-error"></p>
            </div>

            <!-- Témoin épouse -->
            <div>
              <label class="block text-gray-700 text-sm font-medium mb-1" for="mariage-temoin-epouse">
                Témoin de l'épouse*
              </label>
              <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                id="mariage-temoin-epouse"
                name="temoin_epouse"
                placeholder="Nom complet du témoin"
                required
                type="text" />
              <p class="error-message hidden mt-1 text-sm text-red-600" id="mariage-temoin-epouse-error"></p>
            </div>

            <!-- Date mariage -->
            <div>
              <label class="block text-gray-700 text-sm font-medium mb-1" for="mariage-date">
                Date du mariage*
              </label>
              <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                id="mariage-date"
                name="date_mariage"
                required
                type="date" />
              <p class="error-message hidden mt-1 text-sm text-red-600" id="mariage-date-error"></p>
            </div>

            <!-- Lieu mariage -->
            <div>
              <label class="block text-gray-700 text-sm font-medium mb-1" for="mariage-lieu">
                Lieu du mariage*
              </label>
              <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                id="mariage-lieu"
                name="lieu_mariage"
                placeholder="Ex: Mairie de Brobo"
                required
                type="text" />
              <p class="error-message hidden mt-1 text-sm text-red-600" id="mariage-lieu-error"></p>
            </div>

            <!-- Bouton de soumission -->
            <div class="md:col-span-2 pt-2">
              <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center"
                type="submit">
                <i class="fas fa-paper-plane mr-2"></i> Demande
              </button>
            </div>
          </form>
        </section>

        <!-- Formulaire de décès -->
        <section class="tab-content hidden space-y-4" id="deces">
          <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input type="hidden" name="type_acte" value="deces">

            <!-- Nom défunt -->
            <div class="md:col-span-2">
              <label class="block text-gray-700 text-sm font-medium mb-1" for="deces-nom">
                Nom complet du défunt*
              </label>
              <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                id="deces-nom"
                name="nom_defunt"
                placeholder="Nom complet"
                required
                type="text" />
              <p class="error-message hidden mt-1 text-sm text-red-600" id="deces-nom-error"></p>
            </div>

            <!-- Nom père défunt -->
            <div>
              <label class="block text-gray-700 text-sm font-medium mb-1" for="deces-pere">
                Nom du père du défunt*
              </label>
              <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                id="deces-pere"
                name="nom_pere_defunt"
                placeholder="Nom complet"
                required
                type="text" />
              <p class="error-message hidden mt-1 text-sm text-red-600" id="deces-pere-error"></p>
            </div>

            <!-- Nom mère défunt -->
            <div>
              <label class="block text-gray-700 text-sm font-medium mb-1" for="deces-mere">
                Nom de la mère du défunt*
              </label>
              <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                id="deces-mere"
                name="nom_mere_defunt"
                placeholder="Nom complet"
                required
                type="text" />
              <p class="error-message hidden mt-1 text-sm text-red-600" id="deces-mere-error"></p>
            </div>

            <!-- Date décès -->
            <div>
              <label class="block text-gray-700 text-sm font-medium mb-1" for="deces-date">
                Date du décès*
              </label>
              <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                id="deces-date"
                name="date_deces"
                required
                type="date" />
              <p class="error-message hidden mt-1 text-sm text-red-600" id="deces-date-error"></p>
            </div>

            <!-- Lieu décès -->
            <div>
              <label class="block text-gray-700 text-sm font-medium mb-1" for="deces-lieu">
                Lieu du décès*
              </label>
              <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                id="deces-lieu"
                name="lieu_deces"
                placeholder="Ex: Hôpital de Brobo"
                required
                type="text" />
              <p class="error-message hidden mt-1 text-sm text-red-600" id="deces-lieu-error"></p>
            </div>

            <!-- Cause décès -->
            <div class="md:col-span-2">
              <label class="block text-gray-700 text-sm font-medium mb-1" for="deces-cause">
                Cause du décès*
              </label>
              <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                id="deces-cause"
                name="cause_deces"
                placeholder="Cause du décès"
                required
                type="text" />
              <p class="error-message hidden mt-1 text-sm text-red-600" id="deces-cause-error"></p>
            </div>

            <!-- Bouton de soumission -->
            <div class="md:col-span-2 pt-2">
              <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center"
                type="submit"><i class="fas fa-paper-plane mr-2"></i>
                Demande
              </button>
            </div>
          </form>
        </section>
      </div>
    </div>
  </main>
  <!-- Bouton de soumission -->
  <div class="md:col-span-2 pt-2">
    <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center"
      type="text">
      Après soumission de votre declaration en ligne, veillez vous rendre dans la mairie la plus proche pour finaliser votre declaration
    </button>
    <footer class="bg-[#1c2735] text-[#a0a9b8]">
      <div
        class="max-w-7xl mx-auto px-6 py-12 flex flex-col md:flex-row md:justify-between md:items-start gap-10 md:gap-0">
        <div class="md:w-1/4 space-y-4">
          <div
            class="text-white font-script text-2xl select-none"
            style="font-family: 'Pacifico', cursive">
            BROBO
          </div>
          <p class="text-sm leading-relaxed max-w-[220px]">
            Plateforme officielle de gestion des actes d'état civil de la commune de
            BroBo.
          </p>
          <div class="flex space-x-4">
            <a
              aria-label="Facebook"
              class="w-8 h-8 rounded-full bg-[#2f3a4a] flex items-center justify-center hover:bg-[#3b4a6b] transition-colors"
              href="#">
              <i class="fab fa-facebook-f text-[#a0a9b8] text-sm"></i>
            </a>
            <a
              aria-label="Twitter"
              class="w-8 h-8 rounded-full bg-[#2f3a4a] flex items-center justify-center hover:bg-[#3b4a6b] transition-colors"
              href="#">
              <i class="fab fa-twitter text-[#a0a9b8] text-sm"></i>
            </a>
            <a
              aria-label="Instagram"
              class="w-8 h-8 rounded-full bg-[#2f3a4a] flex items-center justify-center hover:bg-[#3b4a6b] transition-colors"
              href="#">
              <i class="fab fa-instagram text-[#a0a9b8] text-sm"></i>
            </a>
          </div>
        </div>
        <div class="md:w-1/5 space-y-2">
          <h3 class="text-white font-semibold text-sm mb-3">Liens rapides</h3>
          <ul class="space-y-2 text-sm">
            <li><a class="hover:text-white transition-colors" href="../View/index.php">Accueil</a></li>
            <li>
              <a
                class="hover:text-white transition-colors"
                href="../View/connexion_citoyen.php">Demande d'actes</a>
            </li>
            <li>
              <a
                class="hover:text-white transition-colors"
                href="../View/citoyen_login.php">Mes demandes</a>
            </li>
            <li>
              <a
                class="hover:text-white transition-colors"
                href="../admin/admin_notice.html">Tableau de bord</a>
            </li>
            <li><a class="hover:text-white transition-colors" href="../View/contact.html">Contact</a></li>
          </ul>
        </div>
        <div class="md:w-1/5 space-y-2">
          <h3 class="text-white font-semibold text-sm mb-3">Informations</h3>
          <ul class="space-y-2 text-sm">
            <li><a class="hover:text-white transition-colors" href="contact.html">À propos de</a></li>
            <li><a class="hover:text-white transition-colors" href="#">Mentions légales</a></li>
            <li>
              <a class="hover:text-white transition-colors" href="#">Politique de confidentialité</a>
            </li>
            <li><a class="hover:text-white transition-colors" href="#">Conditions d'utilisation</a></li>
            <li><a class="hover:text-white transition-colors" href="#">Plan du site</a></li>
          </ul>
        </div>
        <div class="md:w-1/4 space-y-2 text-sm">
          <h3 class="text-white font-semibold text-sm mb-3">Contact</h3>
          <ul class="space-y-3">
            <li class="flex items-start gap-2">
              <i class="fas fa-map-marker-alt mt-1 text-[#a0a9b8]"></i>
              <span>Quatier Mairie, BroBo, Côte d'Ivoire</span>
            </li>
            <li class="flex items-center gap-2">
              <i class="fas fa-phone-alt text-[#a0a9b8]"></i>
              <span>+225 27 30 64 00 00</span>
            </li>
            <li class="flex items-center gap-2">
              <i class="fas fa-envelope text-[#a0a9b8]"></i>
              <span>contact@mairie-brobo.ci</span>
            </li>
            <li class="flex items-center gap-2">
              <i class="fas fa-clock text-[#a0a9b8]"></i>
              <span>Lun-Ven : 8h30-17h30</span>
            </li>
          </ul>
        </div>
      </div>
      <div
        class="max-w-7xl mx-auto px-6 py-4 border-t border-[#2f3a4a] flex flex-col md:flex-row justify-between items-center text-xs text-[#6b7587]">
        <div>© 2025 La commune de BroBo. Tous droits réservés.</div>
        <div class="flex items-center space-x-6 mt-3 md:mt-0">
          <img alt="MTN CI logo" class="h-6 w-auto" height="24" src="../public/image/MTN.png" width="60" />
          <img alt="Orange CI logo" class="h-6 w-auto" height="24" src="../public/image/ORANGE.png" width="60" />
          <img alt="Moov CI logo" class="h-6 w-auto" height="24" src="../public/image/MOOV.png" width="60" />
        </div>
      </div>
      <button
        aria-label="Support chat"
        class="fixed bottom-6 right-6 bg-[#4a90e2] w-12 h-12 rounded-full flex items-center justify-center shadow-lg hover:bg-[#357abd] transition-colors">
        <i class="fas fa-headset text-white text-lg"></i>
      </button>
    </footer>

    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.getElementById('menuToggle');
        const mobileMenu = document.getElementById('mobileMenu');

        menuToggle.addEventListener('click', function() {
          mobileMenu.classList.toggle('hidden');
          mobileMenu.classList.toggle('open'); // Ajout de la classe 'open' pour la transition de hauteur

          // Changer l'icône du bouton
          const icon = menuToggle.querySelector('i');
          icon.classList.toggle('fas fa-bars');
          icon.classList.toggle('fas fa-times'); // Assurez-vous d'avoir Font Awesome pour l'icône de fermeture
        });
      });

      // Mobile menu - Optimisé pour la navigation
      document.getElementById('menuToggle')?.addEventListener('click', function() {
        const menu = document.getElementById('mobileMenu');
        menu.classList.toggle('hidden');
        document.body.classList.toggle('overflow-hidden');

        const icon = this.querySelector('i');
        icon.classList.toggle('ri-menu-line');
        icon.classList.toggle('ri-close-line');
      });
      // Tab switching logic
      const tabs = document.querySelectorAll('.tab-button');
      const contents = document.querySelectorAll('.tab-content');

      tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
          // Remove active styles and hide all contents
          tabs.forEach((t) => {
            t.classList.remove('border-blue-600', 'text-blue-600');
            t.classList.add('border-transparent', 'text-gray-600');
            t.setAttribute('aria-selected', 'false');
          });
          contents.forEach((c) => c.classList.add('hidden'));

          // Add active styles to clicked tab and show content
          tab.classList.add('border-blue-600', 'text-blue-600');
          tab.classList.remove('border-transparent', 'text-gray-600');
          tab.setAttribute('aria-selected', 'true');
          const tabId = tab.getAttribute('data-tab');
          document.getElementById(tabId).classList.remove('hidden');
        });
      });
    </script>
</body>

</html>