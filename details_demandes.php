<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'mairie_brobo');

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin/admin_login.php');
    exit();
}

if (!isset($_GET['id'])) {
    die("ID de demande non spécifié.");
}

$demande_id = (int)$_GET['id'];

// Récupérer les détails de la demande
$stmt = $conn->prepare("SELECT d.*, c.nom, c.prenom FROM demandes_actes d JOIN citoyens c ON d.citoyen_id = c.id WHERE d.id = ?");
$stmt->bind_param('i', $demande_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Demande non trouvée.");
}

$demande = $result->fetch_assoc();

// Vérifiez si la clé 'fichiers' existe et divisez les fichiers
$fichiers = isset($demande['fichiers']) ? explode(',', $demande['fichiers']) : [];

// Affichez le contenu de la demande pour le débogage
// var_dump($demande); // Décommenter pour le débogage
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <title>Détails de la Demande - Acte Civil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
    <style>
        body {
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            font-family: 'Inter', sans-serif;
            color: #202020;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            max-width: 720px;
            margin: 40px auto 60px;
            padding: 32px 36px;
        }

        h2 {
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 24px;
            text-align: center;
            letter-spacing: 0.03em;
        }

        .info-group {
            margin-bottom: 20px;
        }

        .info-label {
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
            display: block;
            font-size: 0.9rem;
        }

        .info-value {
            font-size: 1.1rem;
            color: #0f172a;
            word-wrap: break-word;
        }

        ul.file-list {
            list-style: none;
            padding-left: 0;
            margin-top: 8px;
        }

        ul.file-list li {
            margin-bottom: 12px;
            font-size: 1rem;
        }

        ul.file-list li a {
            color: #2563eb;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        ul.file-list li a:hover,
        ul.file-list li a:focus {
            color: #1e40af;
            text-decoration: underline;
        }

        ul.file-list li .not-found {
            color: #ef4444;
            font-style: italic;
        }

        .btn-group {
            text-align: center;
            margin-top: 32px;
        }

        .btn-group .btn {
            min-width: 130px;
            font-weight: 600;
            padding: 12px 24px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-group .btn-success {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            border-color: transparent;
            color: white;
        }

        .btn-group .btn-success:hover {
            background: linear-gradient(135deg, #16a34a, #15803d);
            box-shadow: 0 12px 30px rgba(22, 163, 74, 0.5);
            transform: translateY(-3px);
            color: white;
        }

        .btn-group .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
            border-color: transparent;
            color: white;
        }

        .btn-group .btn-danger:hover {
            background: linear-gradient(135deg, #b91c1c, #991b1b);
            box-shadow: 0 12px 30px rgba(185, 28, 28, 0.5);
            transform: translateY(-3px);
            color: white;
        }

        .btn-group .btn-secondary {
            background: #e2e8f0;
            border-color: transparent;
            color: #334155;
        }

        .btn-group .btn-secondary:hover {
            background: #cbd5e1;
            color: #1e293b;
            box-shadow: 0 6px 18px rgba(100, 116, 139, 0.3);
        }

        @media (max-width: 480px) {
            .container {
                margin: 20px 12px 40px;
                padding: 24px 24px;
            }

            .btn-group .btn {
                width: 100%;
                margin-bottom: 14px;
                min-width: auto;
            }

            .btn-group {
                margin-top: 24px;
            }
        }
    </style>
</head>

<body>
    <div class="container" role="main" aria-labelledby="mainTitle">
        <h2 id="mainTitle">Détails de la Demande d'Acte de Naissance</h2>

        <div class="info-group">
            <label class="info-label" for="citoyenName">Citoyen</label>
            <div class="info-value" id="citoyenName"><?= htmlspecialchars($demande['nom'] . ' ' . $demande['prenom']) ?></div>
        </div>
        <div class="info-group">
            <label class="info-label" for="typeActe">Type d'Acte</label>
            <div class="info-value" id="typeActe"><?= htmlspecialchars(ucfirst($demande['type_acte'])) ?></div>
        </div>
        <div class="info-group">
            <label class="info-label" for="dateDemande">Date de Demande</label>
            <div class="info-value" id="dateDemande"><?= date('d/m/Y', strtotime($demande['date_demande'])) ?></div>
        </div>

        <div class="info-group">
            <label class="info-label">Pièces Jointes</label>
            <ul class="file-list" aria-label="Liste des pièces jointes">
                <?php if (!empty($fichiers)) : ?>
                    <?php foreach ($fichiers as $fichier) :
                        $path = "../View/uploads/" . trim($fichier);
                        $exists = file_exists($path);
                    ?>
                        <li>
                            <?php if ($exists): ?>
                                <a href="<?= htmlspecialchars($path) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars(trim($fichier)) ?></a>
                            <?php else: ?>
                                <span class="not-found"><?= htmlspecialchars(trim($fichier)) ?> (Fichier non trouvé)</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                <?php else : ?>
                    <li>Aucune pièce jointe disponible.</li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="btn-group">

            <a href="selec.php?demande_id=<?= $demande['id'] ?>"
                class="btn btn-success"
                role="button"
                aria-label="Choisir l'acte pour le citoyen">
                Valider
            </a>
            <a href="dashboard_admin.php" class="btn btn-secondary" role="button" aria-label="Retour au tableau de bord">Retour</a>
        </div>
    </div>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>