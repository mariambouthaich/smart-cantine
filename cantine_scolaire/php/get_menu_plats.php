<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isLoggedIn() || getUserType() !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

$menu_id = $_GET['id'] ?? 0;
$db = Database::getInstance()->getConnection();

// Récupérer les infos du menu
$stmt = $db->prepare("SELECT * FROM menus WHERE id = :id");
$stmt->execute([':id' => $menu_id]);
$menu = $stmt->fetch();

if (!$menu) {
    echo json_encode(['success' => false, 'message' => 'Menu introuvable']);
    exit;
}

// Récupérer les plats du menu
$stmt = $db->prepare("
    SELECT p.nom, p.type_plat, p.prix
    FROM menu_plats mp
    JOIN plats p ON mp.plat_id = p.id
    WHERE mp.menu_id = :id
    ORDER BY p.type_plat, p.nom
");
$stmt->execute([':id' => $menu_id]);
$plats = $stmt->fetchAll();

$type_labels = [
    'entree' => 'Entrée',
    'plat_principal' => 'Plat Principal',
    'dessert' => 'Dessert'
];

$result = [];
foreach ($plats as $plat) {
    $result[] = [
        'nom' => $plat['nom'],
        'type' => $plat['type_plat'],
        'type_label' => $type_labels[$plat['type_plat']],
        'prix' => formatPrice($plat['prix'])
    ];
}

echo json_encode([
    'success' => true,
    'date' => formatDate($menu['date_menu']),
    'plats' => $result
]);
?>
