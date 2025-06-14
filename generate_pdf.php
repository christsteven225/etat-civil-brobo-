<?php
// Vérifie que la bibliothèque FPDF est bien présente
if (!file_exists('../fpdf/fpdf.php')) {
  die('Le fichier fpdf.php est introuvable.');
}
require('../fpdf/fpdf.php');

$conn = new mysqli('localhost', 'root', '', 'mairie_brobo');
if ($conn->connect_error) {
  die("Erreur de connexion : " . $conn->connect_error);
}

// Récupération des paramètres
$demande_id = isset($_GET['demande_id']) ? (int)$_GET['demande_id'] : 0;
$declaration_id = isset($_GET['declaration_id']) ? (int)$_GET['declaration_id'] : 0;

// Initialisation variables
$acte = null;
$details = '';
$numero_serie = strtoupper(uniqid('ACTE-'));

// Charger et valider la demande d'acte plus paiement validé
if ($demande_id > 0) {
  // Pour les demandes d'actes
  $stmt = $conn->prepare("SELECT d.*, c.nom AS citoyen_nom, c.prenom AS citoyen_prenom, p.statut AS paiement_statut 
        FROM demandes_actes d 
        JOIN citoyens c ON d.citoyen_id = c.id 
        JOIN paiements p ON p.demande_id = d.id 
        WHERE d.id = ? AND p.statut = 'effectue' AND d.statut = 'accepte'");
  if (!$stmt) {
    die("Erreur lors de la préparation (demande): " . $conn->error);
  }
  $stmt->bind_param('i', $demande_id);
  $stmt->execute();
  $acte = $stmt->get_result()->fetch_assoc();

  if (!$acte) {
    die("Demande non trouvée ou paiement non effectué.");
  }

  // Détails selon le type d'acte
  switch ($acte['type_acte']) {
    case 'naissance':
      $sql = $conn->prepare("SELECT nom_complet, sex, nom_pere, nom_mere, date_naissance, lieu_naissance FROM naissances WHERE demande_id = ?");
      if (!$sql) {
        die("Erreur lors de la préparation (naissance): " . $conn->error);
      }
      $sql->bind_param('i', $demande_id);
      break;
    case 'mariage':
      $sql = $conn->prepare("SELECT nom_epoux, nom_epouse, temoin_epoux, temoin_epouse, date_mariage, lieu_mariage FROM mariages WHERE demande_id = ?");
      if (!$sql) {
        die("Erreur lors de la préparation (mariage): " . $conn->error);
      }
      $sql->bind_param('i', $demande_id);
      break;
    case 'deces':
      $sql = $conn->prepare("SELECT nom_defunt, nom_pere_defunt, nom_mere_defunt, date_deces, lieu_deces, cause_deces FROM deces WHERE demande_id = ?");
      if (!$sql) {
        die("Erreur lors de la préparation (décès): " . $conn->error);
      }
      $sql->bind_param('i', $demande_id);
      break;
    default:
      die("Type d'acte inconnu.");
  }
  $sql->execute();
  $res = $sql->get_result()->fetch_assoc();

  // Construire la description selon le type
  if ($acte['type_acte'] == 'naissance') {
    $details = "Nom complet de l'enfant: {$res['nom_complet']}\nSexe: {$res['sex']}\nNom pere: {$res['nom_pere']}\nNom mere: {$res['nom_mere']}\nDate de naissance: {$res['date_naissance']}\nLieu de naissance: {$res['lieu_naissance']}";
  } elseif ($acte['type_acte'] == 'mariage') {
    $details = "Epoux: {$res['nom_epoux']}\nEpouse: {$res['nom_epouse']}\nTemoin epoux: {$res['temoin_epoux']}\nTemoin epouse: {$res['temoin_epouse']}\nDate mariage: {$res['date_mariage']}\nLieu mariage: {$res['lieu_mariage']}";
  } elseif ($acte['type_acte'] == 'deces') {
    $details = "Nom complet du défunt: {$res['nom_defunt']}\nNom pere: {$res['nom_pere_defunt']}\nNom mere: {$res['nom_mere_defunt']}\nDate décès: {$res['date_deces']}\nLieu deces: {$res['lieu_deces']}\nCause deces: {$res['cause_deces']}";
  }
} elseif ($declaration_id > 0) {
  // Pour les déclarations
  $stmt = $conn->prepare("SELECT d.*, c.nom AS citoyen_nom, c.prenom AS citoyen_prenom, pd.statut AS paiement_statut 
        FROM `declarations` AS d 
        JOIN citoyens AS c ON d.citoyen_id = c.id 
        JOIN paiements_declarations AS pd ON pd.declaration_id = d.id 
        WHERE d.id = ? AND pd.statut = 'effectue' AND d.statut = 'accepte'");
  if (!$stmt) {
    die("Erreur lors de la préparation (déclaration): " . $conn->error);
  }
  $stmt->bind_param('i', $declaration_id);
  $stmt->execute();
  $acte = $stmt->get_result()->fetch_assoc();

  if (!$acte) {
    die("Déclaration non trouvée ou paiement non effectué.");
  }

  // Vérifier le type d'acte
  if ($acte['type_acte'] == 'reconnaissance') {
    die("La génération de PDF pour les déclarations de reconnaissance n'est pas autorisée.");
  }

  if ($acte['type_acte'] == 'naissance') {
    $stmt_details = $conn->prepare("SELECT nom_complet, sex, nom_pere, nom_mere, date_naissance, lieu_naissance FROM declarations_naissance WHERE citoyen_id = ?");
    if (!$stmt_details) {
      die("Erreur de préparation pour les détails de naissance: " . $conn->error);
    }
    $stmt_details->bind_param('i', $acte['citoyen_id']);
    $stmt_details->execute();
    $details_res = $stmt_details->get_result()->fetch_assoc();

    if ($details_res) {
      $details = "Nom complet de l'enfant: {$details_res['nom_complet']}\n" .
        "Sexe: {$details_res['sex']}\n" .
        "Nom pere: {$details_res['nom_pere']}\n" .
        "Nom mere: {$details_res['nom_mere']}\n" .
        "Date de naissance: {$details_res['date_naissance']}\n" .
        "Lieu de naissance: {$details_res['lieu_naissance']}";
    } else {
      die("Détails de la déclaration de naissance non trouvés.");
    }
  } elseif ($acte['type_acte'] == 'deces') {
    $stmt_details = $conn->prepare("SELECT nom_defunt, date_deces, lieu_deces, cause_deces FROM declarations_deces WHERE citoyen_id = ?");
    if (!$stmt_details) {
      die("Erreur de préparation pour les détails de décès: " . $conn->error);
    }
    $stmt_details->bind_param('i', $acte['citoyen_id']);
    $stmt_details->execute();
    $details_res = $stmt_details->get_result()->fetch_assoc();

    if ($details_res) {
      $details = "Nom complet du défunt: {$details_res['nom_defunt']}\n" .
        "Lieu de deces: {$details_res['lieu_deces']}\n" .
        "Date de deces: {$details_res['date_deces']}\n" .
        "Cause de deces: {$details_res['cause_deces']}";
    } else {
      die("Détails de la déclaration de décès non trouvés.");
    }
  }
} else {
  die("ID de demande ou de déclaration invalide.");
}

// Générer le PDF
$pdf = new FPDF();
$pdf->AddPage();

// Logo en haut
$pdf->Image('../public/image/logo.jpg', 10, 6, 30);
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, "REPUBLIQUE DE COTE D IVOIRE", 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, "Mairie de Brobo", 0, 1, 'C');

$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, "Acte Civil - " . ucfirst($acte['type_acte'] ?? 'Demande d\'acte'), 0, 1, 'C');

$pdf->Ln(10);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, "Numero Acte: " . $numero_serie, 0, 1);
$pdf->MultiCell(0, 10, $details);

$pdf->Ln(20);
// Cachet ou signature en bas à droite
$pdf->Image('../public/image/sign.jpg', 150, 220, 40);

$pdf->SetY(-30);
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 10, "Document genere automatiquement - Avec signature numerique", 0, 1, 'C');

// Sauvegarder le PDF dans un dossier
$chemin = "../pdfs/declaration_$declaration_id.pdf";
$pdf->Output('F', $chemin);

// Afficher la page de succès
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>PDF généré avec succès - Téléchargement</title>
  <style>
    /* Reset minimal */
    *,
    *::before,
    *::after {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, rgb(108, 108, 126) 0%, #4f46e5 100%);
      color: #f9fafb;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 32px 16px;
      text-align: center;
    }

    .container {
      background: rgba(255 255 255 / 0.1);
      backdrop-filter: saturate(180%) blur(12px);
      padding: 40px 48px;
      border-radius: 20px;
      box-shadow: 0 24px 48px rgb(37 99 235 / 0.3);
      max-width: 480px;
      width: 100%;
    }

    h1 {
      font-size: 2.5rem;
      margin-bottom: 16px;
      font-weight: 700;
      color: #e0e7ff;
      text-shadow: 0 2px 6px rgb(0 0 0 / 0.3);
    }

    p.message {
      font-size: 1.125rem;
      margin-bottom: 32px;
      line-height: 1.6;
      color: #c7d2fe;
    }

    a.btn-download {
      display: inline-block;
      background: linear-gradient(135deg, #4f46e5, #3b82f6);
      padding: 14px 36px;
      font-size: 1.125rem;
      font-weight: 600;
      color: #f9fafb;
      text-decoration: none;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgb(59 130 246 / 0.5);
      transition:
        background 0.3s ease,
        box-shadow 0.3s ease,
        transform 0.3s ease;
      user-select: none;
    }

    a.btn-download:hover,
    a.btn-download:focus-visible {
      background: linear-gradient(135deg, #4338ca, #2563eb);
      box-shadow: 0 14px 40px rgb(37 99 235 / 0.75);
      transform: translateY(-4px);
      outline: none;
    }

    a.btn-download:active {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgb(37 99 235 / 0.6);
    }
  </style>
</head>

<body>
  <main class="container" role="main" aria-labelledby="pageTitle">
    <h1 id="pageTitle">PDF généré avec succès</h1>
    <p class="message">
      Votre document PDF a été créé et est prêt à être téléchargé.<br />
      Cliquez sur le bouton ci-dessous pour récupérer votre fichier en toute sécurité.
    </p>
    <a href="<?php echo htmlspecialchars($chemin); ?>" class="btn-download" download aria-label="Télécharger le PDF généré">
      Télécharger le PDF
    </a>
  </main>
</body>

</html>