<?php
require_once 'php/config.php';

if (!isLoggedIn() || getUserType() !== 'admin') {
    header('Location: index.php');
    exit;
}

$db = Database::getInstance()->getConnection();

// Top 10 plats les plus commandés
$stmt = $db->query("
    SELECT p.nom, p.type_plat, COUNT(cd.id) as nb_commandes
    FROM plats p
    JOIN commande_details cd ON p.id = cd.plat_id
    JOIN commandes c ON cd.commande_id = c.id
    WHERE c.statut != 'annule'
    GROUP BY p.id
    ORDER BY nb_commandes DESC
    LIMIT 10
");
$top_plats = $stmt->fetchAll();

// Plats les moins commandés
$stmt = $db->query("
    SELECT p.nom, p.type_plat, COUNT(cd.id) as nb_commandes
    FROM plats p
    LEFT JOIN commande_details cd ON p.id = cd.plat_id
    LEFT JOIN commandes c ON cd.commande_id = c.id AND c.statut != 'annule'
    GROUP BY p.id
    ORDER BY nb_commandes ASC
    LIMIT 10
");
$moins_plats = $stmt->fetchAll();

// Statistiques par type de plat
$stmt = $db->query("
    SELECT p.type_plat, COUNT(cd.id) as total
    FROM plats p
    JOIN commande_details cd ON p.id = cd.plat_id
    JOIN commandes c ON cd.commande_id = c.id
    WHERE c.statut != 'annule'
    GROUP BY p.type_plat
");
$stats_par_type = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCantine - Statistiques</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="dashboard">
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo"></div>
                <h2>SmartCantine</h2>
            </div>

            <div class="user-info">
                <div class="user-icon">👨‍💼</div>
                <h3>ADMINISTRATEUR</h3>
            </div>

            <nav>
                <ul class="nav-menu">
                    <li><a href="admin_dashboard.php"><span class="icon">📊</span> Dashboard</a></li>
                    <li><a href="admin_eleves.php"><span class="icon">👥</span> Gestion Élèves</a></li>
                    <li><a href="admin_plats.php"><span class="icon">🍽️</span> Gestion Plats</a></li>
                    <li><a href="admin_menus.php"><span class="icon">📋</span> Gestion Menus</a></li>
                    <li><a href="admin_statistiques.php" class="active"><span class="icon">📈</span> Statistiques</a></li>
                    <li><a href="admin_suivi_nutritionnel.php"><span class="icon">🏥</span> Suivi Nutritionnel</a></li>
                    <li><a href="admin_paiements.php"><span class="icon">💰</span> Paiements</a></li>
                    <li><a href="php/logout.php"><span class="icon">🚪</span> Déconnexion</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h1>Statistiques des Plats</h1>
                <p>Analyse des plats commandés</p>
            </div>

            <!-- Statistiques par type -->
            <div class="card">
                <div class="card-header">
                    <h2>Distribution par Type de Plat</h2>
                </div>
                <div class="card-body">
                    <div class="stats-grid">
                        <?php 
                        $type_labels = [
                            'entree' => 'Entrées',
                            'plat_principal' => 'Plats Principaux',
                            'dessert' => 'Desserts'
                        ];
                        
                        foreach ($stats_par_type as $stat): 
                        ?>
                            <div class="stat-card">
                                <h3><?php echo $type_labels[$stat['type_plat']]; ?></h3>
                                <div class="value"><?php echo $stat['total']; ?></div>
                                <p class="label">commandes</p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Top 10 plats les plus commandés -->
            <div class="card">
                <div class="card-header">
                    <h2>Top 10 - Plats les Plus Commandés</h2>
                </div>
                <div class="card-body">
                    <?php if (count($top_plats) > 0): ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Rang</th>
                                        <th>Nom du Plat</th>
                                        <th>Type</th>
                                        <th>Nombre de Commandes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $rang = 1;
                                    $type_badges = [
                                        'entree' => 'badge-entree',
                                        'plat_principal' => 'badge-plat',
                                        'dessert' => 'badge-dessert'
                                    ];
                                    $type_labels_table = [
                                        'entree' => 'Entrée',
                                        'plat_principal' => 'Plat Principal',
                                        'dessert' => 'Dessert'
                                    ];
                                    
                                    foreach ($top_plats as $plat): 
                                    ?>
                                        <tr>
                                            <td><strong><?php echo $rang++; ?></strong></td>
                                            <td><?php echo htmlspecialchars($plat['nom']); ?></td>
                                            <td>
                                                <span class="badge <?php echo $type_badges[$plat['type_plat']]; ?>">
                                                    <?php echo $type_labels_table[$plat['type_plat']]; ?>
                                                </span>
                                            </td>
                                            <td><strong style="color: var(--primary-color); font-size: 18px;"><?php echo $plat['nb_commandes']; ?></strong> fois</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📊</div>
                            <h3>Aucune donnée</h3>
                            <p>Aucune commande n'a encore été passée</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Plats les moins commandés -->
            <div class="card">
                <div class="card-header">
                    <h2>Plats les Moins Commandés</h2>
                </div>
                <div class="card-body">
                    <?php if (count($moins_plats) > 0): ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Nom du Plat</th>
                                        <th>Type</th>
                                        <th>Nombre de Commandes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($moins_plats as $plat): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($plat['nom']); ?></td>
                                            <td>
                                                <span class="badge <?php echo $type_badges[$plat['type_plat']]; ?>">
                                                    <?php echo $type_labels_table[$plat['type_plat']]; ?>
                                                </span>
                                            </td>
                                            <td><?php echo $plat['nb_commandes']; ?> fois</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📊</div>
                            <h3>Aucune donnée</h3>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
