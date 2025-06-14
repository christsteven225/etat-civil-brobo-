<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'mairie_brobo');

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin/admin_login.php');
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID de déclaration non spécifié.");
}

$declaration_id = intval($_GET['id']);

$declarations_query = $conn->query("
    SELECT decl.*, c.nom, c.prenom
    FROM declarations decl
    JOIN citoyens c ON decl.citoyen_id = c.id
    WHERE decl.id = $declaration_id
");

if (!$declarations_query || $declarations_query->num_rows === 0) {
    die("Déclaration non trouvée.");
}

$declaration = $declarations_query->fetch_assoc();

$fichiersDeclaration = isset($declaration['fichiers']) && $declaration['fichiers'] !== '' ? explode(',', $declaration['fichiers']) : [];

if (!isset($_GET['id'])) {
    die("ID de demande non spécifié.");
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Détails de la Déclaration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
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
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            max-width: 720px;
            margin: 40px auto 60px;
            padding: 32px 36px;
        }
        h2 {
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 24px;
            text-align: center;
        }
        .info-label {
            font-weight: 600;
            color: #475569;
            margin-top: 1rem;
            font-size: 0.95rem;
            display: block;
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
            column-count: 2;
            column-gap: 20px;
        }
        ul.file-list li {
            margin-bottom: 12px;
            font-size: 1rem;
            break-inside: avoid;
            page-break-inside: avoid;
            -webkit-column-break-inside: avoid;
        }
        ul.file-list li a {
            color: #2563eb;
            text-decoration: none;
            transition: color 0.3s ease;
            cursor: pointer;
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
        #fileViewer {
            margin-top: 24px;
            border: 1px solid #ccc;
            border-radius: 8px;
            overflow: hidden;
            display: none;
            max-height: 600px;
        }
        #fileViewer iframe {
            width: 100%;
            height: 600px;
            border: none;
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
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .btn-group .btn-primary {
            background: #3b82f6;
            border-color: transparent;
            color: white;
        }
        .btn-group .btn-primary:hover {
            background: #2563eb;
            box-shadow: 0 12px 30px rgba(37,99,235,0.5);
            transform: translateY(-3px);
        }
        .btn-group .btn-secondary {
            background: #e2e8f0;
            border-color: transparent;
            color: #334155;
        }
        .btn-group .btn-secondary:hover {
            background: #cbd5e1;
            color: #1e293b;
            box-shadow: 0 6px 18px rgba(100,116,139,0.3);
        }
        @media (max-width: 480px) {
            ul.file-list {
                column-count: 1;
            }
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
        <h2 id="mainTitle">Détails de la Déclaration</h2>

        <div class="info-group">
            <label class="info-label" for="citoyenName">Citoyen</label>
            <div class="info-value" id="citoyenName"><?= htmlspecialchars($declaration['nom'] . ' ' . $declaration['prenom']) ?></div>
        </div>
        <div class="info-group">
            <label class="info-label" for="typeActe">Type d'Acte</label>
            <div class="info-value" id="typeActe"><?= htmlspecialchars(ucfirst($declaration['type_acte'])) ?></div>
        </div>
        <div class="info-group">
            <label class="info-label" for="dateDeclaration">Date de Déclaration</label>
            <div class="info-value" id="dateDeclaration"><?= date('d/m/Y', strtotime($declaration['date_declaration'])) ?></div>
        </div>

        <div class="info-group">
            <label class="info-label">Pièces Jointes</label>
            <ul class="file-list" aria-label="Liste des pièces jointes">
                <?php if (!empty($fichiersDeclaration)) : ?>
                    <?php foreach ($fichiersDeclaration as $fichier) : 
                        $path = "../viewuploads/" . trim($fichier);
                        $exists = file_exists($path);
                    ?>
                        <li>
                            <?php if($exists): ?>
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
            <a href="admin_dashboard.php" class="btn btn-secondary" role="button" aria-label="Retour au tableau de bord">Retour</a>
        </div>
    </div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
