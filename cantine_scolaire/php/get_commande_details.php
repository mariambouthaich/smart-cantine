<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

$commande_id = $_GET['id'] ?? 0;
$db = Database::getInstance()->getConnection();

// Vérifier que la commande appartient à l'utilisateur (ou que c'est un admin)
if (getUserType() === 'admin') {
    $stmt = $db->prepare("SELECT * FROM commandes WHERE id = :id");
    $stmt->execute([':id' => $commande_id]);
} else {
    $stmt = $db->prepare("SELECT * FROM commandes WHERE id = :id AND utilisateur_id = :user_id");
    $stmt->execute([':id' => $commande_id, ':user_id' => $_SESSION['user_id']]);
}

$commande = $stmt->fetch();

if (!$commande) {
    echo json_encode(['success' => false, 'message' => 'Commande introuvable']);
    exit;
}

// Récupérer les détails
$stmt = $db->prepare("
    SELECT cd.*, p.nom, p.type_plat
    FROM commande_details cd
    JOIN plats p ON cd.plat_id = p.id
    WHERE cd.commande_id = :id
");
$stmt->execute([':id' => $commande_id]);
$details = $stmt->fetchAll();

$type_labels = [
    'entree' => 'Entrée',
    'plat_principal' => 'Plat Principal',
    'dessert' => 'Dessert'
];

$result = [];
foreach ($details as $detail) {
    $result[] = [
        'nom' => $detail['nom'],
        'type' => $detail['type_plat'],
        'type_label' => $type_labels[$detail['type_plat']],
        'prix' => formatPrice($detail['prix_unitaire'])
    ];
}

echo json_encode([
    'success' => true,
    'details' => $result,
    'total' => formatPrice($commande['montant_total'])
]);
?>
