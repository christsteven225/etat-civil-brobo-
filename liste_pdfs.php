<?php
$directory = '../pdfs/'; // Chemin vers le dossier des PDF
$files = array_diff(scandir($directory), array('..', '.')); // Récupérer tous les fichiers sauf '.' et '..'

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Liste des Actes PDF</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container py-5">
        <h2>Liste des Actes PDF</h2>
        <ul class="list-group">
            <?php foreach ($files as $file) : ?>
                <li class="list-group-item">
                    <a href="<?= $directory . htmlspecialchars($file) ?>" target="_blank"><?= htmlspecialchars($file) ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
        <a href="dashboard_admin.php" class="btn btn-secondary mt-3">Retour</a>
    </div>
</body>
</html>
