<?php
session_start();
if (!isset($_SESSION['citoyen_id'])) {
  header('Location: ../view/connexion_decla.php');
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
      }
    }
  }
  return $uploadedFiles;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $citoyen_id = $_SESSION['citoyen_id'];
  $type_acte = $_POST['type_acte'];
  $uploadDir = __DIR__ . 'uploads/';

  $success = false;
  $message = '';

  // Récupérer tous les fichiers uploadés groupés par clé
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

  // Insertion dans la table declarations
  $stmt = $conn->prepare("INSERT INTO declarations (citoyen_id, type_acte, statut, fichiers) VALUES (?, ?, 'en_attente', ?)");
  if (!$stmt) {
    die("Erreur préparation requête declarations : " . $conn->error);
  }

  // Concaténer les fichiers uploadés en une chaîne
  $fichiersMerge = [];
  foreach ($fichiersUploades as $groupFiles) {
    if (is_array($groupFiles)) {
      $fichiersMerge = array_merge($fichiersMerge, $groupFiles);
    }
  }

  $fichiersStr = implode(',', $fichiersMerge);

  $stmt->bind_param('iss', $citoyen_id, $type_acte, $fichiersStr);
  if ($stmt->execute()) {
    $success = true;
    $declaration_id = $stmt->insert_id; // Récupérer l'ID de la déclaration insérée

    // Insertion dans la table declarations_naissance si le type d'acte est "naissance"
    if ($type_acte === 'naissance') {
      $nom_complet = $_POST['nom_complet'];
      $sex = $_POST['sex'];
      $nom_pere = $_POST['nom_pere'];
      $nom_mere = $_POST['nom_mere'];
      $lieu_naissance = $_POST['lieu_naissance'];
      $date_naissance = $_POST['date_naissance'];

      $stmt_naissance = $conn->prepare("INSERT INTO declarations_naissance (citoyen_id, nom_complet, sex, nom_pere, nom_mere, date_naissance, lieu_naissance, fichiers) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
      $stmt_naissance->bind_param('isssssss', $citoyen_id, $nom_complet, $sex, $nom_pere, $nom_mere, $date_naissance, $lieu_naissance, $fichiersStr);
      if (!$stmt_naissance->execute()) {
        $message = "Erreur lors de l'insertion dans la table déclarations de naissance : " . $stmt_naissance->error;
      }
    }

    // Insertion dans la table declarations_deces si le type d'acte est "deces"
    if ($type_acte === 'deces') {
      $nom_defunt = $_POST['nom_defunt'];
      $nom_pere_defunt = $_POST['nom_pere_defunt'];
      $nom_mere_defunt = $_POST['nom_mere_defunt'];
      $date_deces = $_POST['date_deces'];
      $lieu_deces = $_POST['lieu_deces'];
      $cause_deces = $_POST['cause_deces'];

      $stmt_deces = $conn->prepare("INSERT INTO declarations_deces (citoyen_id, nom_defunt, nom_pere_defunt, nom_mere_defunt, date_deces, lieu_deces, cause_deces, fichiers) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
      $stmt_deces->bind_param('isssssss', $citoyen_id, $nom_defunt, $nom_pere_defunt, $nom_mere_defunt, $date_deces, $lieu_deces, $cause_deces, $fichiersStr);
      if (!$stmt_deces->execute()) {
        $message = "Erreur lors de l'insertion dans la table déclarations de décès : " . $stmt_deces->error;
      }
    }

// Insertion dans la table dec_reconnaissance si le type d'acte est "reconnaissance"
if ($type_acte === 'reconnaissances') {
  $extrait_enfant = $_FILES['fichiers']['extrait_enfant'][0]['name'] ?? null; // Assurez-vous que ce champ existe
  $piece_identite_pere = $_FILES['fichiers']['pdfCNIpRecon'][0]['name'] ?? null; // Assurez-vous que ce champ existe
  $piece_identite_mere = $_FILES['fichiers']['pdfCNImRecon'][0]['name'] ?? null; // Assurez-vous que ce champ existe

  $stmt_reconnaissance = $conn->prepare("INSERT INTO dec_reconnaissance (citoyen_id, piece_identite_pere, piece_identite_mere, extrait_enfant) VALUES (?, ?, ?, ?)");
  $stmt_reconnaissance->bind_param('isss', $citoyen_id, $piece_identite_pere, $piece_identite_mere, $extrait_enfant);
  if (!$stmt_reconnaissance->execute()) {
      $message = "Erreur lors de l'insertion dans la table dec_reconnaissance : " . $stmt_reconnaissance->error;
  }
}
else {
      $message = "Erreur : Tous les fichiers requis doivent être uploadés.";
  }
}

  if ($success) {
    $_SESSION['message'] = "Déclaration envoyée avec succès. La validation de votre déclaration peut prendre jusqu'à 2 jours.";
    $_SESSION['messageType'] = "success";
  } else {
    $_SESSION['message'] = $message ?: "Une erreur est survenue.";
    $_SESSION['messageType'] = "error";
  }
  header('Location: ' . $_SERVER['PHP_SELF']);
  exit();
}

// Récupération des messages flash
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
        <a href="../View/connexion_decla.php" class="text-primary font-medium">Faire une Declaration</a>
        <a href="../View/connexion_citoyen.php" class="text-gray-600 hover:text-primary transition">Demande d'actes</a>
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
      Declaration d'acte civil
    </h1>

    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
      <div class="flex overflow-x-auto pb-2 mb-6 scrollbar-hide" style="display: flex; justify-content:center;">
        <button aria-selected="true" class="tab-button flex-shrink-0 px-4 py-2 border-b-2 border-blue-600 text-blue-600 font-semibold flex items-center"
          data-tab="naissance" type="button">
          <i class="fas fa-baby mr-2 text-sm"></i>Acte de naissance
        </button>
        <button aria-selected="false" class="tab-button flex-shrink-0 px-4 py-2 border-b-2 border-transparent text-gray-600 hover:text-blue-600 font-semibold flex items-center"
          data-tab="reconnaissances" type="button">
          <i class="fas fa-ring mr-2 text-sm"></i>Acte de reconnaissance
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
              <label class="block text-gray-700 text-sm font-medium mb-1" for="naissance-nom">Nom complet*</label>
              <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                id="naissance-nom" name="nom_complet" placeholder="Nom et prénom(s)" required type="text" />
              <p class="error-message hidden mt-1 text-sm text-red-600" id="naissance-nom-error"></p>
            </div>

            <div>
              <label class="block text-gray-700 text-sm font-medium mb-1" for="sex">Sexe*</label>
              <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                id="naissance-sexe" name="sex" placeholder="Masculin ou Feminin" required type="text" />
              <p class="error-message hidden mt-1 text-sm text-red-600" id="naissance-sexe-error"></p>
            </div>

            <div>
              <label class="block text-gray-700 text-sm font-medium mb-1" for="naissance-pere">Nom du père*</label>
              <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                id="naissance-pere" name="nom_pere" placeholder="Nom complet du père" required type="text" />
              <p class="error-message hidden mt-1 text-sm text-red-600" id="naissance-pere-error"></p>
            </div>
            <div>
              <label class="block text-gray-700 text-sm font-medium mb-1" for="naissance-mere">Nom de la mère*</label>
              <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                id="naissance-mere" name="nom_mere" placeholder="Nom complet de la mère" required type="text" />
              <p class="error-message hidden mt-1 text-sm text-red-600" id="naissance-mere-error"></p>
            </div>

            <div>
              <label class="block text-gray-700 text-sm font-medium mb-1" for="naissance-lieu">Lieu de naissance*</label>
              <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                id="naissance-lieu" name="lieu_naissance" placeholder="Ex: Brobo" required type="text" />
              <p class="error-message hidden mt-1 text-sm text-red-600" id="naissance-lieu-error"></p>
            </div>

            <div>
              <label class="block text-gray-700 text-sm font-medium mb-1" for="naissance-date">Date de naissance*</label>
              <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                id="naissance-date" name="date_naissance" required type="date" />
              <p class="error-message hidden mt-1 text-sm text-red-600" id="naissance-date-error"></p>
            </div>

            <div>
              <label class="block text-gray-700 text-sm font-medium mb-1" for="fichiers">Certificat de naissance de l'enfant (PDF ou Image)*</label>
              <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 form-control"
                id="fichiers" multiple accept=".pdf,image/*" name="fichiers[pdfCnaissance]" type="file" required />
              <p class="error-message hidden mt-1 text-sm text-red-600" id="naissance-nom-error"></p>
            </div>


            <!-- piece d'itentité de la mère-->
            <div>
              <label class="block text-gray-700 text-sm font-medium mb-1" for="fichier">
                Pièce d'identité de la mère(PDF ou Image)*
              </label>
              <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 form-control"
                id="fichiers"
                multiple
                accept=".pdf,image/*"
                name="fichiers[pdfCNImNaissance]"
                required
                type="file"
                placeholder="CNI,Extrait de Naissance,Passeport ou certificat de nationalité" />
              <p class="error-message hidden mt-1 text-sm text-red-600" id="naissance-nom-error"></p>
            </div>
            <!-- piece d'itentité père -->
            <div>
              <label class="block text-gray-700 text-sm font-medium mb-1" for="fichier">
                Pièce d'identité du père(PDF ou Image)*
              </label>
              <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 form-control"
                id="fichiers"
                multiple
                accept=".pdf,image/*"
                name="fichiers[pdfCNIpNaissance]"
                required
                type="file"
                placeholder="CNI,Extrait de Naissance,Passeport ou certificat de nationalité" />
              <p class="error-message hidden mt-1 text-sm text-red-600" id="naissance-nom-error"></p>
            </div>
            <!-- Une cpie acte de mariage -->
            <div>
              <label class="block text-gray-700 text-sm font-medium mb-1" for="fichier">
                L'acte de mariage si marie(PDF ou Image)*
              </label>
              <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 form-control"
                id="fichiers"
                multiple
                accept=".pdf,image/*"
                name="fichiers[pdfmariageN]"
                placeholder="Une copie de l'acte de mariage"
                required
                type="file" />
              <p class="error-message hidden mt-1 text-sm text-red-600" id="naissance-nom-error"></p>
            </div>

            <!-- Bouton de soumission -->
            <div class="md:col-span-2 pt-2">
              <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center"
                type="submit">
                <i class="fas fa-paper-plane mr-2"></i>Soumission
              </button>
            </div>
          </form>
        </section>

        <!-- Formulaire de declaration de reconnaissance -->
        <section class="tab-content hidden space-y-4" id="reconnaissances">
          <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input type="hidden" name="type_acte" value="reconnaissances">

            <!-- extrait de naissance de l'enfant -->
            <div>
              <label class="block text-gray-700 text-sm font-medium mb-1" for="mariage-epoux">
                L'extrait de l'enfant*
              </label>
              <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 form-control"
                id="fichiers"
                multiple
                accept=".pdf,image/*"
                name="fichiers[extrait_enfant]"
                placeholder="Une copie de l'acte de mariage"
                required
                type="file" />
              <p class="error-message hidden mt-1 text-sm text-red-600" id="mariage-epoux-error"></p>
            </div>

            <!-- piece d'itentité père -->
            <div>
              <label class="block text-gray-700 text-sm font-medium mb-1" for="fichier">
                Pièce d'identité du père(PDF ou Image)*
              </label>
              <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 form-control"
                id="fichiers"
                multiple
                accept=".pdf,image/*"
                name="fichiers[pdfCNIpRecon]"
                required
                type="file"
                placeholder="CNI,Extrait de Naissance,Passeport ou certificat de nationalité" />
              <p class="error-message hidden mt-1 text-sm text-red-600" id="naissance-nom-error"></p>
            </div>

            <!-- piece d'itentité de la mère-->




            
            <div>
              <label class="block text-gray-700 text-sm font-medium mb-1" for="fichier">
                Pièce d'identité de la mère(PDF ou Image)*
              </label>
              <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 form-control"
                id="fichiers"
                multiple
                accept=".pdf,image/*"
                name="fichiers[pdfCNImRecon]"
                required
                type="file"
                placeholder="CNI,Extrait de Naissance,Passeport ou certificat de nationalité" />
              <p class="error-message hidden mt-1 text-sm text-red-600" id="naissance-nom-error"></p>
            </div>

            <!-- Témoin épouse -->
            <div>
              <label for=""></label>
              <h1></h1>

            </div>
            <!-- Témoin épouse -->
            <div style="display: flex;justify-content:center;">
              <label for="">La présence obligatoire des deux (02) parents <br> pour finalisé
                votre declaration.</label>


            </div>

            <!-- Bouton de soumission -->
            <div class="md:col-span-2 pt-2">
              <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center"
                type="submit">
                <i class="fas fa-paper-plane mr-2"></i> Soumission et rendez vous dans votre mairie
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
            <!-- piece d'itentité du defunt -->
            <div>
              <label class="block text-gray-700 text-sm font-medium mb-1" for="fichier">
                Pièce d'identité du défunt(PDF ou Image)*
              </label>
              <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 form-control"
                id="fichiers"
                multiple
                accept=".pdf,image/*"
                name="fichiers[pdfDefunt]"
                required
                type="file"
                placeholder="CNI,Extrait de Naissance,Passeport ou certificat de nationalité" />
              <p class="error-message hidden mt-1 text-sm text-red-600" id="naissance-nom-error"></p>
            </div>
            <!-- piece d'itentité du declarant -->
            <div>
              <label class="block text-gray-700 text-sm font-medium mb-1" for="fichier">
                Pièce d'identité du declarant(PDF ou Image)*
              </label>
              <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 form-control"
                id="fichiers"
                multiple
                accept=".pdf,image/*"
                name="fichiers[pdfdeclarant]"
                required
                type="file"
                placeholder="CNI,Extrait de Naissance,Passeport ou certificat de nationalité" />
              <p class="error-message hidden mt-1 text-sm text-red-600" id="naissance-nom-error"></p>
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
                Soumission
              </button>
            </div>
          </form>
        </section>
      </div>
    </div>
  </main>


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