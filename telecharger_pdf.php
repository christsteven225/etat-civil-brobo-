<?php
session_start();

if (!isset($_SESSION['citoyen_id'])) {
    header('Location: ../view/citoyen_login.php');
    exit();
}

$id = $_GET['id'];

// Simuler un fichier PDF déjà généré
$chemin = "../pdfs/acte_$id.pdf";

// Vérifier si le fichier existe
if (file_exists($chemin)) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="acte_' . $id . '.pdf"');
    readfile($chemin);
    exit();
} else {
    echo "Fichier non trouvé.";
}
