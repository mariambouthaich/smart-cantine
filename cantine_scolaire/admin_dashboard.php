<?php
require_once 'php/config.php';

if (!isLoggedIn() || getUserType() !== 'admin') {
    header('Location: index.php');
    exit;
}

$db = Database::getInstance()->getConnection();

// Statistiques générales
$stmt = $db->query("SELECT COUNT(*) as count FROM utilisateurs WHERE type_compte IN ('eleve', 'parent')");
$total_eleves = $stmt->fetch()['count'];

$stmt = $db->query("SELECT COUNT(*) as count FROM plats WHERE disponible = TRUE");
$total_plats = $stmt->fetch()['count'];

$stmt = $db->query("SELECT SUM(montant_total) as total FROM commandes WHERE statut = 'paye'");
$revenus_totaux = $stmt->fetch()['total'] ?? 0;

$stmt = $db->query("SELECT COUNT(DISTINCT classe) as count FROM utilisateurs WHERE classe IS NOT NULL");
$classes_actives = $stmt->fetch()['count'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCantine - Dashboard Admin</title>
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
                <div class="user-icon">👨‍💼</div>
                <h3>ADMINISTRATEUR</h3>
                <p>Gestion de la cantine</p>
            </div>

            <nav>
                <ul class="nav-menu">
                    <li>
                        <a href="admin_dashboard.php" class="active">
                            <span class="icon">📊</span>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="admin_eleves.php">
                            <span class="icon">👥</span>
                            Gestion Élèves
                        </a>
                    </li>
                    <li>
                        <a href="admin_plats.php">
                            <span class="icon">🍽️</span>
                            Gestion Plats
                        </a>
                    </li>
                    <li>
                        <a href="admin_menus.php">
                            <span class="icon">📋</span>
                            Gestion Menus
                        </a>
                    </li>
                    <li>
                        <a href="admin_statistiques.php">
                            <span class="icon">📈</span>
                            Statistiques
                        </a>
                    </li>
                    <li>
                        <a href="admin_suivi_nutritionnel.php">
                            <span class="icon">🏥</span>
                            Suivi Nutritionnel
                        </a>
                    </li>
                    <li>
                        <a href="admin_paiements.php">
                            <span class="icon">💰</span>
                            Paiements
                        </a>
                    </li>
                    <li>
                        <a href="php/logout.php">
                            <span class="icon">🚪</span>
                            Déconnexion
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h1>Tableau de bord et indicateurs de performance</h1>
                <p>Vue d'ensemble de la cantine scolaire</p>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Élèves Inscrits</h3>
                    <div class="value"><?php echo $total_eleves; ?></div>
                    <p class="label">élèves inscrits</p>
                </div>

                <div class="stat-card">
                    <h3>Revenus Totaux</h3>
                    <div class="value"><?php echo formatPrice($revenus_totaux); ?></div>
                    <p class="label">Revenus Totaux</p>
                </div>

                <div class="stat-card">
                    <h3>Classes Actives</h3>
                    <div class="value"><?php echo $classes_actives; ?></div>
                    <p class="label">Classes Actives</p>
                </div>

                <div class="stat-card">
                    <h3>Plats Disponibles</h3>
                    <div class="value"><?php echo $total_plats; ?></div>
                    <p class="label">plats au menu</p>
                </div>
            </div>

            <!-- Top 10 Plats les Plus Commandés -->
            <div class="card">
                <div class="card-header">
                    <h2>Top 10 - Plats les Plus Commandés</h2>
                </div>
                <div class="card-body">
                    <?php
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

                    if (count($top_plats) > 0):
                    ?>
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
                                    foreach ($top_plats as $plat): 
                                    ?>
                                        <tr>
                                            <td><?php echo $rang++; ?></td>
                                            <td><?php echo htmlspecialchars($plat['nom']); ?></td>
                                            <td>
                                                <?php
                                                $type_badges = [
                                                    'entree' => 'badge-entree',
                                                    'plat_principal' => 'badge-plat',
                                                    'dessert' => 'badge-dessert'
                                                ];
                                                $type_labels = [
                                                    'entree' => 'Entrée',
                                                    'plat_principal' => 'Plat Principal',
                                                    'dessert' => 'Dessert'
                                                ];
                                                ?>
                                                <span class="badge <?php echo $type_badges[$plat['type_plat']]; ?>">
                                                    <?php echo $type_labels[$plat['type_plat']]; ?>
                                                </span>
                                            </td>
                                            <td><strong><?php echo $plat['nb_commandes']; ?></strong> fois</td>
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

            <!-- Commandes Récentes -->
            <div class="card">
                <div class="card-header">
                    <h2>Commandes Récentes</h2>
                    <a href="admin_commandes.php" class="btn btn-secondary">Voir tout</a>
                </div>
                <div class="card-body">
                    <?php
                    $stmt = $db->query("
                        SELECT c.*, u.nom, u.prenom, u.classe
                        FROM commandes c
                        JOIN utilisateurs u ON c.utilisateur_id = u.id
                        ORDER BY c.date_commande DESC
                        LIMIT 10
                    ");
                    $commandes = $stmt->fetchAll();

                    if (count($commandes) > 0):
                    ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Élève</th>
                                        <th>Classe</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($commandes as $cmd): ?>
                                        <tr>
                                            <td>#<?php echo $cmd['id']; ?></td>
                                            <td><?php echo htmlspecialchars($cmd['prenom'] . ' ' . $cmd['nom']); ?></td>
                                            <td><?php echo htmlspecialchars($cmd['classe']); ?></td>
                                            <td><?php echo formatPrice($cmd['montant_total']); ?></td>
                                            <td>
                                                <?php
                                                $statut_classes = [
                                                    'en_attente' => 'status-en-attente',
                                                    'paye' => 'status-paye',
                                                    'annule' => 'status-annule'
                                                ];
                                                $statut_labels = [
                                                    'en_attente' => 'En Attente',
                                                    'paye' => 'Payé',
                                                    'annule' => 'Annulé'
                                                ];
                                                ?>
                                                <span class="status-badge <?php echo $statut_classes[$cmd['statut']]; ?>">
                                                    <?php echo $statut_labels[$cmd['statut']]; ?>
                                                </span>
                                            </td>
                                            <td><?php echo formatDate($cmd['date_commande']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📋</div>
                            <h3>Aucune commande</h3>
                            <p>Aucune commande n'a été passée</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </main>
    </div>
</body>
</html>
