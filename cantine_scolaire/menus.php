<?php
require_once 'php/config.php';

if (!isLoggedIn() || getUserType() === 'admin') {
    header('Location: index.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$message = '';
$error = '';

// Récupérer le solde actuel
$stmt = $db->prepare("SELECT solde FROM utilisateurs WHERE id = :id");
$stmt->execute([':id' => $_SESSION['user_id']]);
$user = $stmt->fetch();
$_SESSION['user_solde'] = $user['solde'];

// Traitement de la commande
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['passer_commande'])) {
    $date_livraison = $_POST['date_livraison'] ?? '';
    $plats_selectionnes = $_POST['plats'] ?? [];
    
    if (empty($date_livraison)) {
        $error = 'Veuillez sélectionner une date de livraison';
    } elseif (count($plats_selectionnes) === 0) {
        $error = 'Veuillez sélectionner au moins un plat';
    } else {
        // Calculer le montant total
        $placeholders = str_repeat('?,', count($plats_selectionnes) - 1) . '?';
        $stmt = $db->prepare("SELECT SUM(prix) as total FROM plats WHERE id IN ($placeholders)");
        $stmt->execute($plats_selectionnes);
        $montant_total = $stmt->fetch()['total'];
        
        if ($user['solde'] < $montant_total) {
            $error = 'Solde insuffisant ! Veuillez recharger votre compte. Montant nécessaire: ' . formatPrice($montant_total);
        } else {
            try {
                $db->beginTransaction();
                
                // Récupérer ou créer le menu pour cette date
                $stmt = $db->prepare("SELECT id FROM menus WHERE date_menu = :date");
                $stmt->execute([':date' => $date_livraison]);
                $menu = $stmt->fetch();
                
                if (!$menu) {
                    $stmt = $db->prepare("INSERT INTO menus (date_menu) VALUES (:date)");
                    $stmt->execute([':date' => $date_livraison]);
                    $menu_id = $db->lastInsertId();
                } else {
                    $menu_id = $menu['id'];
                }
                
                // Créer la commande
                $stmt = $db->prepare("
                    INSERT INTO commandes (utilisateur_id, menu_id, montant_total, statut, date_livraison)
                    VALUES (:user_id, :menu_id, :montant, 'en_attente', :date_livraison)
                ");
                $stmt->execute([
                    ':user_id' => $_SESSION['user_id'],
                    ':menu_id' => $menu_id,
                    ':montant' => $montant_total,
                    ':date_livraison' => $date_livraison
                ]);
                $commande_id = $db->lastInsertId();
                
                // Ajouter les détails de la commande
                foreach ($plats_selectionnes as $plat_id) {
                    $stmt = $db->prepare("SELECT prix FROM plats WHERE id = :id");
                    $stmt->execute([':id' => $plat_id]);
                    $plat = $stmt->fetch();
                    
                    $stmt = $db->prepare("
                        INSERT INTO commande_details (commande_id, plat_id, prix_unitaire)
                        VALUES (:commande_id, :plat_id, :prix)
                    ");
                    $stmt->execute([
                        ':commande_id' => $commande_id,
                        ':plat_id' => $plat_id,
                        ':prix' => $plat['prix']
                    ]);
                }
                
                // Débiter le solde
                $stmt = $db->prepare("UPDATE utilisateurs SET solde = solde - :montant WHERE id = :id");
                $stmt->execute([':montant' => $montant_total, ':id' => $_SESSION['user_id']]);
                
                // Enregistrer le paiement
                $stmt = $db->prepare("
                    INSERT INTO paiements (utilisateur_id, montant, type_operation, commande_id)
                    VALUES (:user_id, :montant, 'paiement', :commande_id)
                ");
                $stmt->execute([
                    ':user_id' => $_SESSION['user_id'],
                    ':montant' => $montant_total,
                    ':commande_id' => $commande_id
                ]);
                
                // Mettre à jour le statut de la commande
                $stmt = $db->prepare("UPDATE commandes SET statut = 'paye' WHERE id = :id");
                $stmt->execute([':id' => $commande_id]);
                
                $db->commit();
                
                // Mettre à jour le solde en session
                $_SESSION['user_solde'] -= $montant_total;
                
                $message = 'Commande passée avec succès ! Numéro de commande: #' . $commande_id;
            } catch (Exception $e) {
                $db->rollBack();
                $error = 'Erreur lors de la commande: ' . $e->getMessage();
            }
        }
    }
}

// Récupérer tous les plats disponibles
$stmt = $db->query("SELECT * FROM plats WHERE disponible = TRUE ORDER BY type_plat, nom");
$plats = $stmt->fetchAll();

// Organiser les plats par type
$plats_par_type = [
    'entree' => [],
    'plat_principal' => [],
    'dessert' => []
];

foreach ($plats as $plat) {
    $plats_par_type[$plat['type_plat']][] = $plat;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCantine - Menus & Commandes</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo"></div>
                <h2>SmartCantine</h2>
            </div>

            <div class="user-info">
                <div class="user-icon">👤</div>
                <h3><?php echo htmlspecialchars($_SESSION['user_nom']); ?></h3>
                <div class="solde"><?php echo formatPrice($_SESSION['user_solde']); ?></div>
            </div>

            <nav>
                <ul class="nav-menu">
                    <li><a href="eleve_dashboard.php"><span class="icon">📊</span> Tableau de bord</a></li>
                    <li><a href="menus.php" class="active"><span class="icon">🍽️</span> Menus & Commandes</a></li>
                    <li><a href="mes_commandes.php"><span class="icon">📋</span> Mes Commandes</a></li>
                    <li><a href="suivi_nutritionnel.php"><span class="icon">🏥</span> Suivi Nutritionnel</a></li>
                    <li><a href="recharger_compte.php"><span class="icon">💰</span> Recharger mon compte</a></li>
                    <li><a href="php/logout.php"><span class="icon">🚪</span> Déconnexion</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h1>Commander votre Repas</h1>
                <p>Choisissez vos plats pour votre prochaine commande</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                    <?php if (strpos($error, 'Solde insuffisant') !== false): ?>
                        <br><a href="recharger_compte.php" class="btn btn-primary" style="margin-top: 10px;">Recharger mon compte</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="commandeForm">
                <div class="card">
                    <div class="card-header">
                        <h2>Informations de livraison</h2>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="date_livraison">Date de livraison</label>
                            <input type="date" name="date_livraison" id="date_livraison" required 
                                   min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                        </div>
                    </div>
                </div>

                <!-- Entrées -->
                <div class="card">
                    <div class="card-header">
                        <h2>Entrées</h2>
                        <span class="badge badge-entree">Choisissez une entrée</span>
                    </div>
                    <div class="card-body">
                        <div class="menu-grid">
                            <?php foreach ($plats_par_type['entree'] as $plat): ?>
                                <div class="menu-item" onclick="selectPlat(this, <?php echo $plat['id']; ?>, 'entree')">
                                    <div class="menu-item-image">🥗</div>
                                    <div class="menu-item-content">
                                        <h3><?php echo htmlspecialchars($plat['nom']); ?></h3>
                                        <p><?php echo htmlspecialchars($plat['description']); ?></p>
                                        <div class="menu-item-footer">
                                            <span class="price"><?php echo formatPrice($plat['prix']); ?></span>
                                            <input type="radio" name="plats[]" value="<?php echo $plat['id']; ?>" style="display: none;">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Plats Principaux -->
                <div class="card">
                    <div class="card-header">
                        <h2>Plats Principaux</h2>
                        <span class="badge badge-plat">Choisissez un plat principal</span>
                    </div>
                    <div class="card-body">
                        <div class="menu-grid">
                            <?php foreach ($plats_par_type['plat_principal'] as $plat): ?>
                                <div class="menu-item" onclick="selectPlat(this, <?php echo $plat['id']; ?>, 'plat')">
                                    <div class="menu-item-image">🍖</div>
                                    <div class="menu-item-content">
                                        <h3><?php echo htmlspecialchars($plat['nom']); ?></h3>
                                        <p><?php echo htmlspecialchars($plat['description']); ?></p>
                                        <div class="menu-item-footer">
                                            <span class="price"><?php echo formatPrice($plat['prix']); ?></span>
                                            <input type="radio" name="plats[]" value="<?php echo $plat['id']; ?>" style="display: none;">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Desserts -->
                <div class="card">
                    <div class="card-header">
                        <h2>Desserts</h2>
                        <span class="badge badge-dessert">Choisissez un dessert</span>
                    </div>
                    <div class="card-body">
                        <div class="menu-grid">
                            <?php foreach ($plats_par_type['dessert'] as $plat): ?>
                                <div class="menu-item" onclick="selectPlat(this, <?php echo $plat['id']; ?>, 'dessert')">
                                    <div class="menu-item-image">🍰</div>
                                    <div class="menu-item-content">
                                        <h3><?php echo htmlspecialchars($plat['nom']); ?></h3>
                                        <p><?php echo htmlspecialchars($plat['description']); ?></p>
                                        <div class="menu-item-footer">
                                            <span class="price"><?php echo formatPrice($plat['prix']); ?></span>
                                            <input type="checkbox" name="plats[]" value="<?php echo $plat['id']; ?>" style="display: none;">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="cart-summary">
                    <h3>Récapitulatif</h3>
                    <div id="cartItems"></div>
                    <div class="cart-total">
                        <span>Total:</span>
                        <span class="amount" id="totalAmount">0.00 DH</span>
                    </div>
                    <button type="submit" name="passer_commande" class="btn btn-primary btn-block">
                        Confirmer la commande
                    </button>
                </div>
            </form>

        </main>
    </div>

    <script src="js/menus.js"></script>
</body>
</html>
