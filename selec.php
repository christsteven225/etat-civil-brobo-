<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin/admin_login.php');
    exit();
}

if (!isset($_GET['demande_id'])) {
    die('ID de la demande manquant.');
}

$demande_id = intval($_GET['demande_id']);

// Connexion à la BDD
$conn = new mysqli('localhost', 'root', '', 'mairie_brobo');
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Vérifiez si le dossier pdfs existe
$pdfPath = __DIR__ . '/../pdfs/';
if (!is_dir($pdfPath)) {
    die("Le dossier 'pdfs' n'existe pas. Veuillez le créer.");
}

// Lister les fichiers PDF disponibles
$pdfFiles = array_filter(scandir($pdfPath), function($file) use ($pdfPath) {
    return is_file($pdfPath . $file) && strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'pdf';
});

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedActe = $_POST['selected_acte'] ?? null;
    if ($selectedActe) {
        // Enregistrez le fichier sélectionné dans la base pour la demande
        $stmt = $conn->prepare("UPDATE demandes_actes SET acte_valide = ? WHERE id = ?");
        $stmt->bind_param('si', $selectedActe, $demande_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "L'acte sélectionné a bien été enregistré.";
            $_SESSION['messageType'] = 'success';
            header('Location: admin_dashboard.php');
            exit();
        } else {
            $error = "Erreur lors de l'enregistrement : " . $conn->error;
        }
    } else {
        $error = "Veuillez sélectionner un acte.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Choix de l'acte à valider</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="mb-4">Choisissez l'acte à valider pour la demande #<?= htmlspecialchars($demande_id) ?></h1>

        <?php if (!empty($error)) : ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="selected_acte" class="form-label">Actes PDF disponibles</label>
                <select id="selected_acte" name="selected_acte" class="form-select" required>
                    <option value="">-- Sélectionnez un acte --</option>
                    <?php foreach($pdfFiles as $pdf): ?>
                        <option value="<?= htmlspecialchars($pdf) ?>"><?= htmlspecialchars($pdf) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">OK</button>
            <a href="admin_dashboard.php" class="btn btn-secondary ms-2">Annuler</a>
        </form>
    </div>
</body>
</html>
