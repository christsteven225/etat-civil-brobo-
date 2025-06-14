<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'mairie_brobo');

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin/admin_login.php');
    exit();
}

// Statistiques pour les demandes et déclarations
$stats = $conn->query("
    SELECT
        (SELECT COUNT(*) FROM demandes_actes) AS total_demandes,
        (SELECT COUNT(*) FROM demandes_actes WHERE statut='accepte') AS accepte_demandes,
        (SELECT COUNT(*) FROM demandes_actes WHERE statut='rejete') AS rejete_demandes,
        (SELECT COUNT(*) FROM paiements WHERE demande_id IN (SELECT id FROM demandes_actes) AND statut = 'effectue') AS paye_demandes,
        (SELECT COUNT(*) FROM declarations) AS total_declarations,
        (SELECT COUNT(*) FROM declarations WHERE statut='accepte') AS accepte_declarations,
        (SELECT COUNT(*) FROM declarations WHERE statut='rejete') AS rejete_declarations,
        (SELECT COUNT(*) FROM paiements_declarations WHERE declaration_id IN (SELECT id FROM declarations) AND statut = 'effectue') AS paye_declarations
")->fetch_assoc();

if (!$stats) {
    die("Erreur dans la requête SQL : " . $conn->error);
}

// Récupération des demandes en attente
$demandes = $conn->query("
    SELECT d.id, c.nom, c.prenom, d.type_acte, d.statut, d.date_demande
    FROM demandes_actes d
    JOIN citoyens c ON d.citoyen_id = c.id
    WHERE d.statut = 'en_attente'
    ORDER BY d.date_demande DESC
");

// Récupération des déclarations en attente
$declarations = $conn->query("
    SELECT decl.id, c.nom, c.prenom, decl.type_acte, decl.statut, decl.date_declaration
    FROM declarations decl
    JOIN citoyens c ON decl.citoyen_id = c.id
    WHERE decl.statut = 'en_attente'
    ORDER BY decl.date_declaration DESC
");

// Récupération des paiements de déclarations
$paiements_declarations = $conn->query("
    SELECT pd.id, c.nom, c.prenom, pd.montant, pd.operateur, pd.statut, pd.date_paiement
    FROM paiements_declarations pd
    JOIN declarations d ON pd.declaration_id = d.id
    JOIN citoyens c ON d.citoyen_id = c.id
    ORDER BY pd.date_paiement DESC
");

// Récupération des paiements de demandes
$paiements_demandes = $conn->query("
    SELECT p.id, c.nom, c.prenom, p.montant, p.operateur, p.statut, p.date_paiement
    FROM paiements p
    JOIN demandes_actes d ON p.demande_id = d.id
    JOIN citoyens c ON d.citoyen_id = c.id
    ORDER BY p.date_paiement DESC
");
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <title>Dashboard Admin</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            padding-top: 58px;
        }

        .sidebar {
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding-top: 58px;
            background: #212529;
            transition: all 0.3s;
            z-index: 1000;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1rem;
            border-left: 4px solid transparent;
        }

        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-link.active {
            color: white;
            border-left: 4px solid #0d6efd;
            background: rgba(255, 255, 255, 0.1);
        }

        .main-content {
            margin-left: 250px;
            transition: all 0.3s;
        }

        .stat-card {
            transition: transform 0.3s;
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 80px;
                overflow: hidden;
            }

            .sidebar .nav-link span {
                display: none;
            }

            .main-content {
                margin-left: 80px;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <button class="navbar-toggler me-2" type="button" id="sidebarToggle">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand" href="../index.php">Mairie de Brobo</a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">Se déconnecter</span>
                <a href="../View/index.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="nav flex-column">
            <a href="#" class="nav-link active">
                <i class="bi bi-speedometer2"></i>
                <span>Tableau de bord </span>
            </a>
            </a>
            <a href="#declarationsTable" class="nav-link">
                <i class="bi bi-file-earmark-text"></i>
                <span>Demandes de Déclaration </span>
            </a>
            <a href="#demandesTable" class="nav-link">
                <i class="bi bi-file-earmark-text"></i>
                <span>Demandes des Demandes </span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container-fluid py-4">
            <h2 class="mb-4">Tableau de Bord Administrateur</h2>

            <!-- Stats Cards -->
            <div class="row mb-4 g-4">
                <div class="col-2">
                    <div class="card stat-card text-bg-primary">
                        <div class="card-body text-center">
                            <h5 class="card-title">Total Demandes</h5>
                            <p class="fs-3 mb-0"><?= $stats['total_demandes'] ?></p>
                            <small class="text-white-50">Voir toutes</small>
                        </div>
                    </div>
                </div>
                <div class="col-2">
                    <div class="card stat-card text-bg-success">
                        <div class="card-body text-center">
                            <h5 class="card-title">Demandes Acceptées</h5>
                            <p class="fs-3 mb-0"><?= $stats['accepte_demandes'] ?></p>
                            <small class="text-white-50">Voir acceptées</small>
                        </div>
                    </div>
                </div>
                <div class="col-2">
                    <div class="card stat-card text-bg-warning">
                        <div class="card-body text-center">
                            <h5 class="card-title">Demandes Rejetées</h5>
                            <p class="fs-3 mb-0"><?= $stats['rejete_demandes'] ?></p>
                            <small class="text-white-50">Voir rejetées</small>
                        </div>
                    </div>
                </div>
                <div class="col-2">
                    <div class="card stat-card text-bg-info">
                        <div class="card-body text-center">
                            <h5 class="card-title">Total Déclarations</h5>
                            <p class="fs-3 mb-0"><?= $stats['total_declarations'] ?></p>
                            <small class="text-white-50">Voir déclarations</small>
                        </div>
                    </div>
                </div>
                <div class="col-2">
                    <div class="card stat-card text-bg-success">
                        <div class="card-body text-center">
                            <h5 class="card-title">Déclarations Acceptées</h5>
                            <p class="fs-3 mb-0"><?= $stats['accepte_declarations'] ?></p>
                            <small class="text-white-50">Voir acceptées</small>
                        </div>
                    </div>
                </div>
                <div class="col-2">
                    <div class="card stat-card text-bg-warning">
                        <div class="card-body text-center">
                            <h5 class="card-title">Déclarations Rejetées</h5>
                            <p class="fs-3 mb-0"><?= $stats['rejete_declarations'] ?></p>
                            <small class="text-white-50">Voir rejetées</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Graphiques Circulaires -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5>Répartition des Demandes</h5>
                    <canvas id="demandesPieChart"></canvas>
                </div>
                <div class="col-md-6">
                    <h5>Répartition des Déclarations</h5>
                    <canvas id="declarationsPieChart"></canvas>
                </div>
            </div>

            <!-- Tableau des Demandes -->
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Liste des Demandes en Attente</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="demandesTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Citoyen</th>
                                    <th>Type Acte</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($d = $demandes->fetch_assoc()) : ?>
                                    <tr>
                                        <td><?= htmlspecialchars($d['id']) ?></td>
                                        <td><?= htmlspecialchars($d['nom']) . ' ' . htmlspecialchars($d['prenom']) ?></td>
                                        <td><?= htmlspecialchars(ucfirst($d['type_acte'])) ?></td>
                                        <td>
                                            <span class="badge <?= $d['statut'] === 'accepte' ? 'bg-success' : ($d['statut'] === 'rejete' ? 'bg-danger' : 'bg-warning text-dark') ?>">
                                                <?= htmlspecialchars(ucfirst($d['statut'])) ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($d['date_demande'])) ?></td>
                                        <td>
                                            <div class="d-flex">
                                                <a href="details_demandes.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-outline-primary me-1" title="Détails">
                                                    <i class="bi bi-eye"></i>
                                                </a>

                                                <?php if ($d['statut'] === 'en_attente') : ?>
                                                    <a href="changer_statut.php?id=<?= $d['id'] ?>&action=accepte&table=demandes_actes" class="btn btn-sm btn-outline-success me-1" title="Accepter">
                                                        <i class="bi bi-check-circle"></i>
                                                    </a>
                                                    <a href="changer_statut.php?id=<?= $d['id'] ?>&action=rejete&table=demandes_actes" class="btn btn-sm btn-outline-danger" title="Rejeter">
                                                        <i class="bi bi-x-circle"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tableau des Déclarations -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Liste des Déclarations en Attente</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="declarationsTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Citoyen</th>
                                    <th>Type Acte</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($decl = $declarations->fetch_assoc()) : ?>
                                    <tr>
                                        <td><?= htmlspecialchars($decl['id']) ?></td>
                                        <td><?= htmlspecialchars($decl['nom']) . ' ' . htmlspecialchars($decl['prenom']) ?></td>
                                        <td><?= htmlspecialchars(ucfirst($decl['type_acte'])) ?></td>
                                        <td>
                                            <span class="badge bg-warning text-dark"><?= htmlspecialchars(ucfirst($decl['statut'])) ?></span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($decl['date_declaration'])) ?></td>
                                        <td>
                                            <div class="d-flex">
                                                <a href="details.php?id=<?= $decl['id'] ?>" class="btn btn-sm btn-outline-primary me-1" title="Détails">
                                                    <i class="bi bi-eye"></i>
                                                </a>

                                                <?php if ($decl['statut'] === 'en_attente') : ?>
                                                    <a href="changer_statut.php?id=<?= $decl['id'] ?>&action=accepte&table=declarations" class="btn btn-sm btn-outline-success me-1" title="Accepter">
                                                        <i class="bi bi-check-circle"></i>
                                                    </a>
                                                    <a href="changer_statut.php?id=<?= $decl['id'] ?>&action=rejete&table=declarations" class="btn btn-sm btn-outline-danger" title="Rejeter">
                                                        <i class="bi bi-x-circle"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tableau des Paiements de Déclarations -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Liste des Paiements de Déclarations</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="paiementsDeclarationsTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Citoyen</th>
                                    <th>Montant</th>
                                    <th>Opérateur</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($pd = $paiements_declarations->fetch_assoc()) : ?>
                                    <tr>
                                        <td><?= htmlspecialchars($pd['id']) ?></td>
                                        <td><?= htmlspecialchars($pd['nom']) . ' ' . htmlspecialchars($pd['prenom']) ?></td>
                                        <td><?= htmlspecialchars($pd['montant']) ?> FCFA</td>
                                        <td><?= htmlspecialchars(ucfirst($pd['operateur'])) ?></td>
                                        <td>
                                            <span class="badge <?= $pd['statut'] === 'effectue' ? 'bg-success' : 'bg-warning' ?>">
                                                <?= htmlspecialchars(ucfirst($pd['statut'])) ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($pd['date_paiement'])) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tableau des Paiements de Demandes -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Liste des Paiements de Demandes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="paiementsDemandesTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Citoyen</th>
                                    <th>Montant</th>
                                    <th>Opérateur</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($pd = $paiements_demandes->fetch_assoc()) : ?>
                                    <tr>
                                        <td><?= htmlspecialchars($pd['id']) ?></td>
                                        <td><?= htmlspecialchars($pd['nom']) . ' ' . htmlspecialchars($pd['prenom']) ?></td>
                                        <td><?= htmlspecialchars($pd['montant']) ?> FCFA</td>
                                        <td><?= htmlspecialchars(ucfirst($pd['operateur'])) ?></td>
                                        <td>
                                            <span class="badge <?= $pd['statut'] === 'effectue' ? 'bg-success' : 'bg-warning' ?>">
                                                <?= htmlspecialchars(ucfirst($pd['statut'])) ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($pd['date_paiement'])) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle sidebar on mobile
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('active');
        document.querySelector('.main-content').classList.toggle('active');
    });

    // Diagramme circulaire pour les demandes
    const demandesPieChart = new Chart(document.getElementById('demandesPieChart').getContext('2d'), {
        type: 'pie',
        data: {
            labels: ['Acceptées', 'Rejetées'],
            datasets: [{
                label: 'Répartition des Demandes',
                data: [<?= $stats['accepte_demandes'] ?>, <?= $stats['rejete_demandes'] ?>],
                backgroundColor: ['#28a745', '#dc3545'],
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Répartition des Demandes'
                }
            }
        }
    });

    // Diagramme circulaire pour les déclarations
    const declarationsPieChart = new Chart(document.getElementById('declarationsPieChart').getContext('2d'), {
        type: 'pie',
        data: {
            labels: ['Acceptées', 'Rejetées'],
            datasets: [{
                label: 'Répartition des Déclarations',
                data: [<?= $stats['accepte_declarations'] ?>, <?= $stats['rejete_declarations'] ?>],
                backgroundColor: ['#007bff', '#ffc107'],
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Répartition des Déclarations'
                }
            }
        }
    });
</script>
