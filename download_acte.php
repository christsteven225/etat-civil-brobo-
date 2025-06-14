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

$demande_id = $_GET['demande_id'] ?? null;

if ($demande_id) {
    // Requête pour récupérer l'acte validé
    $stmt = $conn->prepare("SELECT acte_valide FROM demandes_actes WHERE id = ?");
    $stmt->bind_param('i', $demande_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $acte = $result->fetch_assoc();

    if ($acte && !empty($acte['acte_valide'])) {
        $filePath = '../pdfs/' . $acte['acte_valide'];

        // Vérifiez si le fichier existe
        if (file_exists($filePath)) {
            // Définir les en-têtes pour le téléchargement
            header('Content-Description: File Transfer');
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            flush(); // Vider le tampon système
            readfile($filePath);
            exit();
        } else {
            echo "Le fichier n'existe pas.";
        }
    } else {
        echo "Aucun acte validé trouvé.";
    }
} else {
    echo "ID de demande invalide.";
}

$stmt->close();
$conn->close();
?>
