<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isLoggedIn() || getUserType() !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

$eleve_id = $_GET['id'] ?? 0;
$db = Database::getInstance()->getConnection();

// Récupérer les infos de l'élève
$stmt = $db->prepare("SELECT nom, prenom FROM utilisateurs WHERE id = :id");
$stmt->execute([':id' => $eleve_id]);
$eleve = $stmt->fetch();

if (!$eleve) {
    echo json_encode(['success' => false, 'message' => 'Élève introuvable']);
    exit;
}

// Récupérer les allergies
$stmt = $db->prepare("
    SELECT a.nom_allergie
    FROM utilisateur_allergies ua
    JOIN allergies a ON ua.allergie_id = a.id
    WHERE ua.utilisateur_id = :id
");
$stmt->execute([':id' => $eleve_id]);
$allergies = array_column($stmt->fetchAll(), 'nom_allergie');

echo json_encode([
    'success' => true,
    'eleve' => $eleve['prenom'] . ' ' . $eleve['nom'],
    'allergies' => $allergies
]);
?>
