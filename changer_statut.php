<?php
   session_start();
   $conn = new mysqli('localhost', 'root', '', 'mairie_brobo');

   if (!isset($_SESSION['admin_id'])) {
       die('Accès refusé : vous devez être connecté.');
   }

   if (!isset($_GET['id'], $_GET['action'])) {
       die('Paramètres manquants');
   }

   $id = intval($_GET['id']);
   $action = $_GET['action'];
   $table = $_GET['table'] ?? 'demandes_actes';

   echo "ID: $id, Action: $action, Table: $table<br>";

   $validTables = ['demandes_actes', 'declarations'];
   if (!in_array($table, $validTables)) {
       die('Table invalide');
   }

   $validActions = ['accepte', 'rejete'];
   if (!in_array($action, $validActions)) {
       die('Action invalide');
   }

   // Préparer et exécuter la requête sécurisée
   $stmt = $conn->prepare("UPDATE {$table} SET statut = ? WHERE id = ?");
   if (!$stmt) {
       die('Erreur préparation requête : ' . $conn->error);
   }
   $stmt->bind_param('si', $action, $id);

   if (!$stmt->execute()) {
       die('Erreur exécution requête : ' . $stmt->error);
   }

   echo "Statut mis à jour avec succès.<br>";

   // Optionnel : générer un code unique si acceptée et qu’on est sur demandes_actes
   if ($table === 'demandes_actes' && $action === 'accepte') {
       $code = bin2hex(random_bytes(5));
       $stmtCode = $conn->prepare("UPDATE demandes_actes SET code_unique = ? WHERE id = ?");
       if ($stmtCode) {
           $stmtCode->bind_param('si', $code, $id);
           $stmtCode->execute();
       }
   }

   header('Location: ../admin/admin_dashboard.php?message=Statut mis à jour');
   exit();
   