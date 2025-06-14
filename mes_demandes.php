<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'mairie_brobo');

// Vérification de la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

if (!isset($_SESSION['citoyen_id'])) {
    header('Location: ../View/connexion_citoyen.php');
    exit();
}

$citoyen_id = $_SESSION['citoyen_id'];

// Requête préparée pour récupérer les demandes d'actes
$stmtDemandes = $conn->prepare("
    SELECT d.*, p.statut AS paiement_statut
    FROM demandes_actes d
    LEFT JOIN paiements p ON d.id = p.demande_id AND p.statut = 'effectue'
    WHERE d.citoyen_id = ?
    ORDER BY d.date_demande DESC
");

if (!$stmtDemandes) {
    die("Erreur de préparation de la requête des demandes : " . $conn->error);
}

$stmtDemandes->bind_param('i', $citoyen_id);
$stmtDemandes->execute();
$demandes = $stmtDemandes->get_result();

// Requête préparée pour récupérer les déclarations
$stmtDeclarations = $conn->prepare("
    SELECT declarations.*, paiements_declarations.statut AS paiement_statut
    FROM declarations
    LEFT JOIN paiements_declarations ON declarations.id = paiements_declarations.declaration_id AND paiements_declarations.statut = 'effectue'
    WHERE declarations.citoyen_id = ? AND declarations.statut IN ('accepte', 'rejete')
    ORDER BY declarations.date_declaration DESC
");

if (!$stmtDeclarations) {
    die("Erreur de préparation de la requête des déclarations : " . $conn->error);
}

$stmtDeclarations->bind_param('i', $citoyen_id);
$stmtDeclarations->execute();
$declarations = $stmtDeclarations->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Mes Demandes</title>
    <meta charset="UTF-8">
    <link rel="shortcut icon" type="x-icon" href="../public/image/icone2.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .card-demand {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .card-demand:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .status-badge {
            font-size: 0.9rem;
            padding: 0.35em 0.65em;
        }
        .btn-custom {
            background-color: #007bff;
            color: white;
        }
        .btn-custom:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row mb-4">
            <div class="col">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Mes Demandes d'Acte</h2>
                    <div>
                        <a href="demandeacte.php" class="btn btn-primary me-2">
                            <i class="bi bi-plus-circle"></i> Nouvelle demande
                        </a>
                        <a href="../View/index.php" class="btn btn-outline-danger">
                            <i class="bi bi-box-arrow-right"></i> Déconnexion
                        </a>
                    </div>
                </div>
                <hr>
            </div>
        </div>

        <!-- Section des Demandes d'Actes -->
        <h4>Demandes d'Actes</h4>
        <div class="row g-3 mb-4">
            <?php while ($d = $demandes->fetch_assoc()) : ?>
                <div class="col-12">
                    <div class="card card-demand mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title">Demande #<?= htmlspecialchars($d['id']) ?></h5>
                                <span class="badge <?= isset($d['paiement_statut']) && $d['paiement_statut'] == 'effectue' ? 'bg-success' : 'bg-secondary' ?> status-badge">
                                    <?= htmlspecialchars(ucfirst($d['statut'])) ?>
                                </span>
                            </div>
                            <p class="card-text">
                                <strong>Type:</strong> <?= htmlspecialchars(ucfirst($d['type_acte'])) ?>
                            </p>
                            <p class="card-text">
                                <strong>Date:</strong> <?= date('d/m/Y', strtotime($d['date_demande'])) ?>
                            </p>
                            <div class="d-flex justify-content-end">
                                <?php if ($d['statut'] == 'accepte' && !$d['paiement_statut']) : ?>
                                    <a href="simulate_payment.php?demande_id=<?= $d['id'] ?>" 
                                       class="btn btn-success btn-sm me-2">
                                        <i class="bi bi-credit-card"></i> Payer
                                    </a>
                                <?php elseif (isset($d['paiement_statut']) && $d['paiement_statut'] == 'effectue' && $d['type_acte'] === 'naissance' && !empty($d['acte_valide'])) : ?>
                                    <a href="../pdfs/<?= htmlspecialchars($d['acte_valide']) ?>" 
                                       class="btn btn-primary btn-sm" target="_blank" download>
                                        <i class="bi bi-download"></i> Télécharger l'acte validé
                                    </a>
                                <?php else: ?>
                                    <span>Aucun acte validé disponible</span>
                                <?php endif; ?>
                                <button class="btn btn-outline-secondary btn-sm ms-2" 
                                        onclick="showDetails(<?= $d['id'] ?>)">
                                    <i class="bi bi-info-circle"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Section des Déclarations -->
        <h4>Déclarations</h4>
        <div class="row g-3 mb-4">
            <?php while ($dec = $declarations->fetch_assoc()) : ?>
                <div class="col-12">
                    <div class="card card-demand mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title">Déclaration #<?= htmlspecialchars($dec['id']) ?></h5>
                                <span class="badge <?= isset($dec['statut']) && $dec['statut'] == 'accepte' ? 'bg-success' : 'bg-danger' ?> status-badge">
                                    <?= htmlspecialchars(ucfirst($dec['statut'])) ?>
                                </span>
                            </div>
                            <p class="card-text">
                                <strong>Type:</strong> <?= htmlspecialchars(ucfirst($dec['type_acte'])) ?>
                            </p>
                            <p class="card-text">
                                <strong>Date:</strong> <?= date('d/m/Y', strtotime($dec['date_declaration'])) ?>
                            </p>
                            <div class="d-flex justify-content-end">
                                <?php if ($dec['statut'] == 'accepte' && !$dec['paiement_statut']) : ?>
                                    <a href="simulate_payment.php?declaration_id=<?= $dec['id'] ?>" 
                                       class="btn btn-success btn-sm me-2">
                                        <i class="bi bi-credit-card"></i> Payer
                                    </a>
                                <?php elseif (isset($dec['paiement_statut']) && $dec['paiement_statut']) : ?>
                                    <a href="../Model/generate_pdf.php?declaration_id=<?= $dec['id'] ?>" 
                                       class="btn btn-primary btn-sm">
                                        <i class="bi bi-download"></i> PDF
                                    </a>
                                <?php endif; ?>
                                <button class="btn btn-outline-secondary btn-sm" 
                                        onclick="showDetails(<?= $dec['id'] ?>)">
                                    <i class="bi bi-info-circle"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fonction pour afficher les détails
        function showDetails(id) {
            fetch(`../Controler/get_demande_details.php?id=${id}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('modalDetailsContent').innerHTML = data;
                })
                .catch(error => {
                    document.getElementById('modalDetailsContent').innerHTML = `
                        <div class="alert alert-danger">
                            Erreur lors du chargement des détails: ${error}
                        </div>
                    `;
                });
            
            const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
            modal.show();
        }
    </script>
</body>
</html>

<?php
$stmtDemandes->close();
$stmtDeclarations->close();
$conn->close();
?>
