<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'mairie_brobo');

// Vérifier et ajouter la colonne si elle n'existe pas
$check_column = $conn->query("SHOW COLUMNS FROM paiements LIKE 'numero'");
if ($check_column->num_rows == 0) {
    $conn->query("ALTER TABLE paiements ADD COLUMN numero VARCHAR(20) AFTER operateur");
}

if (!isset($_SESSION['citoyen_id'])) {
    header('Location: citoyen_login.php');
    exit();
}

$citoyen_id = $_SESSION['citoyen_id'];
$demande_id = isset($_GET['demande_id']) ? intval($_GET['demande_id']) : 0;
$declaration_id = isset($_GET['declaration_id']) ? intval($_GET['declaration_id']) : 0;

// Vérification de la demande d'acte
if ($demande_id > 0) {
    $demande = $conn->prepare("SELECT * FROM demandes_actes WHERE id = ? AND citoyen_id = ? AND statut = 'accepte'");
    $demande->bind_param("ii", $demande_id, $citoyen_id);
    $demande->execute();
    $result = $demande->get_result();

    if ($result->num_rows == 0) {
        die("Demande invalide ou non accessible.");
    }
}

// Vérification de la déclaration
if ($declaration_id > 0) {
    $declaration = $conn->prepare("SELECT * FROM declarations WHERE id = ? AND citoyen_id = ? AND statut = 'accepte'");
    $declaration->bind_param("ii", $declaration_id, $citoyen_id);
    $declaration->execute();
    $result = $declaration->get_result();

    if ($result->num_rows == 0) {
        die("Déclaration invalide ou non accessible.");
    }
}

// Traitement du formulaire
$error = '';
$success = '';
$pdf_link = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $montant = floatval($_POST['montant']);
    $operateur = $_POST['operateur'];
    $numero = $_POST['numero'];

    $valid_operateurs = ['mtn', 'orange', 'moov', 'wave'];

    if ($montant != 1000) {
        $error = "Le montant doit être exactement 1000 XOF.";
    } elseif (!in_array($operateur, $valid_operateurs)) {
        $error = "Opérateur invalide.";
    } elseif (strlen($numero) < 8) {
        $error = "Numéro de téléphone invalide.";
    } else {
        // Enregistrement du paiement
        if ($demande_id > 0) {
            // Paiement pour une demande d'acte
            $stmt = $conn->prepare("INSERT INTO paiements (demande_id, montant, operateur, numero, statut, date_paiement) 
            VALUES (?, ?, ?, ?, 'effectue', NOW())");
            $stmt->bind_param("idss", $demande_id, $montant, $operateur, $numero);
            $pdf_link = "../Model/generate_pdf.php?demande_id=" . $demande_id;
        } elseif ($declaration_id > 0) {
            // Paiement pour une déclaration
            $stmt = $conn->prepare("INSERT INTO paiements_declarations (declaration_id, montant, operateur, numero, statut, date_paiement) 
            VALUES (?, ?, ?, ?, 'effectue', NOW())");
            $stmt->bind_param("idss", $declaration_id, $montant, $operateur, $numero);
            $pdf_link = "../Model/generate_pdf.php?declaration_id=" . $declaration_id;
        }

        if ($stmt->execute()) {
            $success = "Paiement effectué avec succès.";

            // Redirection vers le script de téléchargement si c'est une demande d'acte de naissance
            if ($demande_id > 0) {
                header("Location: download_acte.php?demande_id=" . $demande_id);
                exit();
            }
        } else {
            $error = "Erreur lors de l'enregistrement du paiement: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulation de Paiement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .payment-container {
            max-width: 600px;
            margin: 0 auto;
        }

        .payment-header {
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }

        .operator-logo {
            width: 30px;
            height: 30px;
            margin-right: 10px;
        }

        @media (max-width: 576px) {
            .payment-container {
                padding: 15px;
            }

            .btn {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="payment-container shadow-sm p-4 bg-white rounded">
            <div class="payment-header text-center">
                <h2 class="mb-3">
                    <i class="bi bi-credit-card"></i> Paiement N°<?= htmlspecialchars($demande_id > 0 ? $demande_id : $declaration_id) ?>
                </h2>
                <p class="text-muted"><?= $demande_id > 0 ? "Demande d'acte administratif" : "Déclaration" ?></p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <div class="d-grid gap-2">
                    <a href="<?= htmlspecialchars($pdf_link) ?>" class="btn btn-primary btn-lg" target="_blank">
                        <i class="bi bi-file-earmark-pdf"></i> Télécharger votre acte (PDF)
                    </a>
                    <a href="../View/index.php" class="btn btn-outline-secondary">
                        <i class="bi bi-house"></i> Retour au tableau de bord
                    </a>
                </div>
            <?php else: ?>
                <form method="post" id="paymentForm">
                    <!-- Montant fixe -->
                    <div class="mb-4 p-3 bg-light rounded">
                        <h5 class="mb-3"><i class="bi bi-cash-coin"></i> Montant à payer</h5>
                        <div class="input-group">
                            <span class="input-group-text">XOF</span>
                            <input type="text" class="form-control form-control-lg text-center fw-bold"
                                value="1000" readonly style="background-color: white;">
                        </div>
                        <small class="text-muted">Frais fixes pour le traitement de votre demande</small>
                    </div>

                    <!-- Sélection de l'opérateur -->
                    <div class="mb-4">
                        <h5 class="mb-3"><i class="bi bi-phone"></i> Opérateur mobile</h5>
                        <div class="row g-2 operator-choices">
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="operateur" value="mtn" id="mtn" required>
                                <label class="btn btn-outline-primary w-100 py-3" for="mtn">
                                    <img src="../public/image/MTN.png" alt="MTN" class="operator-logo">
                                    <span>MTN</span>
                                </label>
                            </div>
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="operateur" value="orange" id="orange">
                                <label class="btn btn-outline-warning w-100 py-3" for="orange">
                                    <img src="../public/image/ORANGE.png" alt="Orange" class="operator-logo">
                                    <span>Orange</span>
                                </label>
                            </div>
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="operateur" value="moov" id="moov">
                                <label class="btn btn-outline-danger w-100 py-3" for="moov">
                                    <img src="../public/image/MOOV.png" alt="Moov" class="operator-logo">
                                    <span>Moov</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Numéro de téléphone -->
                    <div class="mb-4">
                        <label for="numero" class="form-label">
                            <i class="bi bi-telephone"></i> Numéro mobile
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-123"></i></span>
                            <input type="tel" class="form-control" id="numero" name="numero"
                                placeholder="Ex: 0701234567" required
                                pattern="[0-9]{8,15}" title="8 à 15 chiffres">
                        </div>
                        <div class="form-text">Entrez votre numéro de mobile money</div>
                    </div>

                    <!-- Champ caché pour le montant -->
                    <input type="hidden" name="montant" value="1000">

                    <!-- Boutons de soumission -->
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-success btn-lg py-3" id="submitBtn">
                            <i class="bi bi-lock"></i> Payer maintenant
                        </button>
                        <a href="index.html" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Retour
                        </a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>