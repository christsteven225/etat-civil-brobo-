<?php
session_start();
if (!isset($_SESSION['citoyen_id'])) {
    header('Location: ../View/connexion_citoyen.php');
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'mairie_brobo');
if ($conn->connect_error) {
    die("Erreur : " . $conn->connect_error);
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $citoyen_id = $_SESSION['citoyen_id'];
    $type_acte = $_POST['type_acte'];
  
    $success = false;

    if ($type_acte == 'naissance') {
      $nom_complet = htmlspecialchars($_POST['nom_complet']);
      $sex = htmlspecialchars($_POST['sex']);
      $nom_pere = htmlspecialchars($_POST['nom_pere']);
      $nom_mere = htmlspecialchars($_POST['nom_mere']);
      $lieu_naissance = htmlspecialchars($_POST['lieu_naissance']);
      $date_naissance = htmlspecialchars($_POST['date_naissance']);

      if (empty($nom_complet) || empty($nom_pere) || empty($nom_mere) || empty($sex)) {
          $errors[] = "Tous les champs obligatoires doivent être remplis";
      }

        $stmt = $conn->prepare("INSERT INTO demandes_actes (citoyen_id, type_acte) VALUES (?, 'naissance')");
        $stmt->bind_param('i', $citoyen_id);
        if ($stmt->execute()) {
            $demande_id = $stmt->insert_id;

            $stmt2 = $conn->prepare("INSERT INTO naissances (demande_id, nom_complet, sex, nom_pere, nom_mere, lieu_naissance, date_naissance) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt2->bind_param('issssss', $demande_id, $nom_complet, $sex, $nom_pere, $nom_mere, $lieu_naissance, $date_naissance);
            if($stmt2->execute()) {
                $success = true;
            } else {
                $message = "Erreur : " . $stmt2->error;
            }
        } else {
            $message = "Erreur : " . $stmt->error;
        }
    } else if ($type_acte == 'mariage') {
        $nom_epoux = $_POST['nom_epoux'];
        $nom_epouse = $_POST['nom_epouse'];
        $date_mariage = $_POST['date_mariage'];
        $lieu_mariage = $_POST['lieu_mariage'];
        $temoin_epoux = $_POST['temoin_epoux'];
        $temoin_epouse = $_POST['temoin_epouse'];
        
        $stmt = $conn->prepare("INSERT INTO demandes_actes (citoyen_id, type_acte) VALUES (?, 'mariage')");
        $stmt->bind_param('i', $citoyen_id);
        if ($stmt->execute()) {
            $demande_id = $stmt->insert_id;
           
            $stmt2 = $conn->prepare("INSERT INTO mariages (demande_id, nom_epoux, nom_epouse, date_mariage, lieu_mariage, temoin_epoux, temoin_epouse) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt2->bind_param('issssss', $demande_id, $nom_epoux, $nom_epouse, $date_mariage, $lieu_mariage, $temoin_epoux, $temoin_epouse);
            if($stmt2->execute()) {
                $success = true;
            } else {
                $message = "Erreur : " . $stmt2->error;
            }
        } else {
            $message = "Erreur : " . $stmt->error;
        }
    } else if ($type_acte == 'deces'){
        $nom_defunt = $_POST['nom_defunt'];
        $pere_def = $_POST['pere_def'];
        $mere_def = $_POST['mere_def'];
        $date_deces = $_POST['date_deces'];
        $lieu_deces = $_POST['lieu_deces'];
        $cause_deces = $_POST['cause_deces'];

        $stmt = $conn->prepare("INSERT INTO demandes_actes (citoyen_id, type_acte) VALUES (?, 'deces')");
        $stmt->bind_param('i', $citoyen_id);
        if ($stmt->execute()) {
            $demande_id = $stmt->insert_id;

            $stmt2 = $conn->prepare("INSERT INTO deces (demande_id, nom_defunt, pere_def, mere_def, date_deces, lieu_deces, cause_deces) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt2->bind_param('issssss', $demande_id, $nom_defunt, $pere_def, $mere_def, $date_deces, $lieu_deces, $cause_deces);
            if($stmt2->execute()) {
                $success = true;
            } else {
                $message = "Erreur : " . $stmt2->error;
            }
        } else {
            $message = "Erreur : " . $stmt->error;
        }
    }

    if($success) {
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
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Tableau de Bord - Gestion Des Actes D'Etat Civil</title>
    <!-- Ajout de Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Ajout de Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script>tailwind.config={theme:{extend:{colors:{primary:'#3b82f6',secondary:'#64748b'},borderRadius:{'none':'0px','sm':'4px',DEFAULT:'8px','md':'12px','lg':'16px','xl':'20px','2xl':'24px','3xl':'32px','full':'9999px','button':'8px'}}}}</script>
    
    <!-- Police Pacifico pour le logo -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet" />
    <!-- Police Inter pour le texte -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.5.0/echarts.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter&amp;display=swap" rel="stylesheet" />
    <style>
        :where([class^="ri-"])::before {
            content: "\f3c2";
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
        }
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type="number"] {
            -moz-appearance: textfield;
        }
        .custom-checkbox {
            position: relative;
            display: inline-block;
            width: 20px;
            height: 20px;
            background-color: white;
            border: 2px solid #d1d5db;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .custom-checkbox.checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }
        .custom-checkbox.checked::after {
            content: '';
            position: absolute;
            top: 3px;
            left: 6px;
            width: 6px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }
        .custom-switch {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 24px;
        }
        .custom-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .switch-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #e5e7eb;
            transition: .4s;
            border-radius: 24px;
        }
        .switch-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .switch-slider {
            background-color: #3b82f6;
        }
        input:checked + .switch-slider:before {
            transform: translateX(24px);
        }
         /* Styles pour les messages d'erreur */
         .error-message {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        /* Styles pour le message centré */
        #messageOverlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
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
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            max-width: 90%;
            text-align: center;
            font-size: 1.25rem;
            font-weight: 600;
        }
        #messageBox.success {
            color: #16a34a; /* vert */
        }
        #messageBox.error {
            color: #dc2626; /* rouge */
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
         /* Animation pour les onglets */
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

      /* Style pour le menu mobile */
      .mobile-menu {
            transition: all 0.3s ease-in-out;
            max-height: 0;
            overflow: hidden;
        }

        .mobile-menu.open {
            max-height: 500px;
        }
    </style>
</head>
<body>
    <?php if ($message): ?>
    <div id="messageOverlay">
        <div id="messageBox" class="<?= htmlspecialchars($messageType) ?>">
            <button id="messageCloseBtn" aria-label="Fermer le message">&times;</button>
            <?= htmlspecialchars($message) ?>
        </div>
    </div>
    <script>
        document.getElementById('messageCloseBtn').addEventListener('click', function () {
            document.getElementById('messageOverlay').style.display = 'none';
        });
    </script>
    <?php endif; ?>

    <!-- En-tête avec navigation responsive -->
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
            <!-- Bouton menu mobile -->
            <button id="mobileMenuButton" class="md:hidden text-gray-600 focus:outline-none">
                <i class="fas fa-bars text-2xl"></i>
            </button>
            <!-- Navigation desktop -->
            <nav class="hidden md:flex space-x-6">
                <a href="..//View/index.php" class="text-gray-600 hover:text-primary transition">Accueil</a>
                <a href="../View/connexion_citoyen.php" class="text-primary font-medium">Demande d'actes</a>
                <a href="../View/citoyen_login.php" class="text-gray-600 hover:text-primary transition">Mes demandes</a>
                <a href="../admin/admin_notice.html" class="text-gray-600 hover:text-primary transition">Tableau de bord</a>
                <a href="../View/contact.html" class="text-gray-600 hover:text-primary transition">Contact</a>
            </nav>
        </div>
        <!-- Navigation mobile -->
        <div id="mobileMenu" class="mobile-menu md:hidden bg-white border-t">
            <div class="container mx-auto px-4 py-2 flex flex-col space-y-3">
                <a href="../View/index.php" class="py-2 text-gray-600 hover:text-primary">Accueil</a>
                
                <a href="../View/connexion_citoyen.php" class="py-2 text-primary font-medium">Demande d'actes</a>
                <a href="../View/citoyen_login.php" class="py-2 text-gray-600 hover:text-primary">Mes demandes</a>
                <a href="../admin/admin_notice.html" class="py-2 text-gray-600 hover:text-primary">Tableau de bord</a>
                <a href="../View/contact.html" class="py-2 text-gray-600 hover:text-primary">Contact</a>
            </div>
        </div>
    </header>

    <!-- Main content with tabs and forms -->
    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-10">
        <h1 class="text-2xl font-semibold text-gray-800 mb-8 select-none">
         Demande d'acte civil
        </h1>
        <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
         <!-- Tabs améliorés -->
         <div class="flex overflow-x-auto pb-2 mb-6 scrollbar-hide">
                <button class="tab-button flex-shrink-0 px-4 py-2 border-b-2 border-blue-600 text-blue-600 font-semibold flex items-center"
                    data-tab="naissance">
                    <i class="fas fa-baby mr-2 text-sm"></i>Acte de naissance
                </button>
                <button class="tab-button flex-shrink-0 px-4 py-2 border-b-2 border-transparent text-gray-600 hover:text-blue-600 font-semibold flex items-center"
                    data-tab="mariage">
                    <i class="fas fa-ring mr-2 text-sm"></i>Acte de mariage
                </button>
                <button class="tab-button flex-shrink-0 px-4 py-2 border-b-2 border-transparent text-gray-600 hover:text-blue-600 font-semibold flex items-center"
                    data-tab="deces">
                    <i class="fas fa-cross mr-2 text-sm"></i>Acte de décès
                </button>
            </div>

         <!-- Forms container -->
         <div class="relative">

          <!-- Loading overlay -->
          <div id="formLoading" class="hidden absolute inset-0 bg-white bg-opacity-70 z-10 flex items-center justify-center rounded-lg">
                    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
                </div>

          <!-- Acte de naissance formulaire -->
          <section class="tab-content spaace-y-4" id="naissance">
           <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
           <input type="hidden" name="type_acte" value="naissance"> <!-- Champ caché pour le type d'acte -->
            <div class="mb-4">
             <label class="block text-gray-700 text-sm font-medium mb-1" for="naissance-nom">
              Nom complet
             </label>
             <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none" 
                    id="naissance-nom"
                     name="nom_complet" 
                     placeholder="Nom et prenom(s)" 
                     required
                     type="text" />
                     <p class="error-message hidden mt-1 text-sm text-red-600" id="naissance-nom-error"></p>
            </div>
            <div class="mb-4">
             <label class="block text-gray-700 text-sm font-medium mb-1" for="naissance-nom">
              Sexe
             </label>
             <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none" 
                    id="naissance-sexe"
                     name="sex" 
                     placeholder="Masculin ou Feminin" 
                     required
                     type="text" />
                     <p class="error-message hidden mt-1 text-sm text-red-600" id="naissance-sex-error"></p>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-1" for="naissance-nom">
                 Nom du père
                </label>
                <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none"  
                id="naissance-nom" name="nom_pere" placeholder="Nom Pere" required type="text"/>
                <p class="error-message hidden mt-1 text-sm text-red-600" id="naissance-nom-pere-error"></p>
              </div>
               <div class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-1" for="naissance-nom">
                 Nom de la mère
                </label>
                <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none" 
                id="naissance-nom" name="nom_mere" placeholder="Nom Mere" required type="text" />
                <p class="error-message hidden mt-1 text-sm text-red-600" id="naissance-nom-mere-error"></p>
              </div>
            <div class="mb-4">
             <label class="block text-gray-700 text-sm font-medium mb-1" for="naissance-lieu">
              Lieu de naissance
             </label>
             <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none"
              id="naissance-lieu" name="lieu_naissance" placeholder="Lieu de naissance" required="" type="text" />
             <p class="error-message hidden mt-1 text-sm text-red-600" id="naissance-lieu-naissance-error"></p>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-1" for="naissance-date">
                 Date de naissance
                </label>
                <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none"
                 id="naissance-date" name="date_naissance" required="" type="date" />
                <p class="error-message hidden mt-1 text-sm text-red-600" id="naissance-date-naissance-error"></p>
              </div>
            <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded" type="submit">
            <i class="fas fa-paper-plane mr-2"></i>
             Demander l'acte
            </button>
           </form>
          </section>
          <!-- Acte de mariage form -->
          <section class="tab-content hidden space-y-4" id="mariage">
           <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
           <input type="hidden" name="type_acte" value="mariage"> <!-- Champ caché pour le type d'acte -->
            <div class="mb-4">
             <label class="block text-gray-700 text-sm font-medium mb-1" for="mariage-numero">
              Numéro d'acte de mariage
             </label>
             <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none" 
             id="mariage-numero" name="numero_acte" placeholder="Ex: 654321" required="" type="text" />
            </div>
            <div class="mb-4">
             <label class="block text-gray-700 text-sm font-medium mb-1" for="mariage-nom1">
              Nom complet de l'époux
             </label>
             <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none"
              id="mariage-nom1" name="nom_epoux" placeholder="Nom complet " required="" type="text" />
            </div>
            <div class="mb-4">
             <label class="block text-gray-700 text-sm font-medium mb-1" for="mariage-nom2">
              Nom complet de l'épouse
             </label>
             <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none" 
             id="mariage-nom2" name="nom_epouse" placeholder="Nom complet" required="" type="text" />
            </div>
            <div class="mb-4">
             <label class="block text-gray-700 text-sm font-medium mb-1" for="mariage-date">
              Date du mariage
             </label>
             <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none"
             id="mariage-date" name="date_mariage" required="" type="date" />
            </div>
            <div class="mb-4">
             <label class="block text-gray-700 text-sm font-medium mb-1" for="mariage-lieu">
              Lieu du mariage
             </label>
             <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none"
              id="mariage-lieu" name="lieu_mariage" placeholder="Lieu du mariage" required="" type="text" />
            </div>
            <!-- Bouton de soumission -->
            <div class="md:col-span-2 pt-2">
                            <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center"
                                type="submit">
                                <i class="fas fa-paper-plane mr-2"></i> Demander l'acte
                            </button>
                        </div>
           </form>
          </section>
          <!-- Acte de décès form -->
          <section class="tab-content hidden space-y-4" id="deces">
           <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
           <input type="hidden" name="type_acte" value="deces"> <!-- Champ caché pour le type d'acte -->
            <div class="mb-4">
             <label class="block text-gray-700 text-sm font-medium mb-1" for="deces-numero">
              Numéro d'acte de décès
             </label>
             <input class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" id="deces-numero" name="numero_acte" placeholder="Ex: 789012" required="" type="text" />
            </div>
            <div class="mb-4">
             <label class="block text-gray-700 text-sm font-medium mb-1" for="deces-nom">
              Nom complet du défunt
             </label>
             <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none"
             id="deces-nom" name="nom_defunt" placeholder="Nom complet du défunt" required="" type="text" />
            </div>
            <div class="mb-4">
             <label class="block text-gray-700 text-sm font-medium mb-1" for="deces-date">
              Date du décès
             </label>
             <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none"
              id="deces-date" name="date_deces" required="" type="date" />
            </div>
            <div class="mb-4">
             <label class="block text-gray-700 text-sm font-medium mb-1" for="deces-lieu">
              Lieu du décès
             </label>
             <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none"
             id="deces-lieu" name="lieu_deces" placeholder="Lieu du décès" required="" type="text" />
            </div>
            <div class="mb-4">
             <label class="block text-gray-700 text-sm font-medium mb-1" for="deces-demandeur">
              Cause du décès
             </label>
             <input class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none" 
             id="deces-demandeur" name="cause_deces" placeholder="La cause" required="" type="text" />
            </div>
            <!-- Bouton de soumission -->
            <div class="md:col-span-2 pt-2">
                            <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center"
                                type="submit">
                                <i class="fas fa-paper-plane mr-2"></i> Demander l'acte
                            </button>
                        </div>
           </form>
          </section>
         </div>
        </div>
       </main>

       <footer class="bg-[#1c2735] text-[#a0a9b8]">
        <div
          class="max-w-7xl mx-auto px-6 py-12 flex flex-col md:flex-row md:justify-between md:items-start gap-10 md:gap-0"
        >
          <div class="md:w-1/4 space-y-4">
            <div
              class="text-white font-script text-2xl select-none"
              style="font-family: 'Pacifico', cursive"
            >
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
                href="#"
              >
                <i class="fab fa-facebook-f text-[#a0a9b8] text-sm"></i>
              </a>
              <a
                aria-label="Twitter"
                class="w-8 h-8 rounded-full bg-[#2f3a4a] flex items-center justify-center hover:bg-[#3b4a6b] transition-colors"
                href="#"
              >
                <i class="fab fa-twitter text-[#a0a9b8] text-sm"></i>
              </a>
              <a
                aria-label="Instagram"
                class="w-8 h-8 rounded-full bg-[#2f3a4a] flex items-center justify-center hover:bg-[#3b4a6b] transition-colors"
                href="#"
              >
                <i class="fab fa-instagram text-[#a0a9b8] text-sm"></i>
              </a>
            </div>
          </div>
          <div class="md:w-1/5 space-y-2">
            <h3 class="text-white font-semibold text-sm mb-3">Liens rapides</h3>
            <ul class="space-y-2 text-sm">
              <li><a class="hover:text-white transition-colors" href="index.html">Accueil</a></li>
              <li>
                <a
                  class="hover:text-white transition-colors"
                  href="connexion_citoyen.php"
                  >Demande d'actes</a
                >
              </li>
              <li>
                <a
                  class="hover:text-white transition-colors"
                  href="citoyen_login.php"
                  >Mes demandes</a
                >
              </li>
              <li>
                <a
                  class="hover:text-white transition-colors"
                  href="../admin/admin_notice.html"
                  >Tableau de bord</a
                >
              </li>
              <li><a class="hover:text-white transition-colors" href="contact.html">Contact</a></li>
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
          class="max-w-7xl mx-auto px-6 py-4 border-t border-[#2f3a4a] flex flex-col md:flex-row justify-between items-center text-xs text-[#6b7587]"
        >
          <div>© 2025 La commune de BroBo. Tous droits réservés.</div>
          <div class="flex items-center space-x-6 mt-3 md:mt-0">
            <img alt="MTN CI logo" class="h-6 w-auto" height="24" src="../public/image/MTN.png" width="60" />
            <img
              alt="Orange CI logo"
              class="h-6 w-auto"
              height="24"
              src="../public/image/ORANGE.png"
              width="60"
            />
            <img alt="Moov CI logo" class="h-6 w-auto" height="24" src="../public/image/MOOV.png" width="60" />
          </div>
        </div>
        <button
          aria-label="Support chat"
          class="fixed bottom-6 right-6 bg-[#4a90e2] w-12 h-12 rounded-full flex items-center justify-center shadow-lg hover:bg-[#357abd] transition-colors"
        >
          <i class="fas fa-headset text-white text-lg"></i>
        </button>
      </footer>

      <script>

         // Fonctionnalité des onglets
         const tabs = document.querySelectorAll('.tab-button');
        const contents = document.querySelectorAll('.tab-content');

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                // Mise à jour des styles des onglets
                tabs.forEach((t) => {
                    t.classList.remove('border-blue-600', 'text-blue-600');
                    t.classList.add('border-transparent', 'text-gray-600');
                });
                tab.classList.add('border-blue-600', 'text-blue-600');
                tab.classList.remove('border-transparent', 'text-gray-600');

                // Affichage du contenu correspondant
                const tabId = tab.getAttribute('data-tab');
                contents.forEach((c) => c.classList.add('hidden'));
                document.getElementById(tabId).classList.remove('hidden');
            });
        });


            // Validation des champs
            function validateField(e) {
            const field = e.target;
            const errorElement = document.getElementById(`${field.id}-error`);

            if (!field.value.trim()) {
                showError(field, errorElement, 'Ce champ est obligatoire');
                return false;
            }

            // Validation spécifique pour les dates
            if (field.type === 'date' && new Date(field.value) > new Date()) {
                showError(field, errorElement, 'La date ne peut pas être dans le futur');
                return false;
            }

            clearError(field, errorElement);
            return true;
        }

        function showError(field, errorElement, message) {
            field.classList.add('border-red-500');
            field.classList.remove('border-gray-300');
            if (errorElement) {
                errorElement.textContent = message;
                errorElement.classList.remove('hidden');
            }
        }

        function clearError(e, errorElement = null) {
            const field = e.target ? e.target : e;
            const element = errorElement || document.getElementById(`${field.id}-error`);

            field.classList.remove('border-red-500');
            field.classList.add('border-gray-300');
            if (element) {
                element.classList.add('hidden');
            }
        }

        // Écouteurs d'événements pour la validation
        document.querySelectorAll('input[required], select[required]').forEach(input => {
            input.addEventListener('blur', validateField);
            input.addEventListener('input', function() {
                clearError(this);
            });
        });

        // Gestion de la soumission des formulaires
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                let isValid = true;
                const requiredInputs = this.querySelectorAll('input[required], select[required]');

                requiredInputs.forEach(input => {
                    const event = {
                        target: input
                    };
                    if (!validateField(event)) {
                        isValid = false;
                    }
                });

                if (isValid) {
                    const loadingElement = document.getElementById('formLoading');
                    loadingElement.classList.remove('hidden');

                    // Simulation d'envoi (remplacer par un vrai fetch/ajax si nécessaire)
                    setTimeout(() => {
                        loadingElement.classList.add('hidden');
                        alert('Formulaire validé! Envoi des données...');
                        // this.submit(); // Décommentez pour une vraie soumission
                    }, 1500);
                } else {
                    // Scroll vers la première erreur
                    const firstError = this.querySelector('.border-red-500');
                    if (firstError) {
                        firstError.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                }
            });
        });

        // Activer le premier onglet au chargement
        if (tabs.length > 0) {
            tabs[0].click();
        }
      </script>
</body>
</html>

