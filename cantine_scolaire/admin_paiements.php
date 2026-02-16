<?php
require_once 'php/config.php';

if (!isLoggedIn() || getUserType() !== 'admin') {
    header('Location: index.php');
    exit;
}

$db = Database::getInstance()->getConnection();

// Statistiques globales
$stmt = $db->query("
    SELECT 
        COUNT(*) as total_paiements,
        SUM(CASE WHEN type_operation = 'paiement' THEN montant ELSE 0 END) as total_paiements_montant,
        SUM(CASE WHEN type_operation = 'recharge' THEN montant ELSE 0 END) as total_recharges,
        SUM(CASE WHEN type_operation = 'remboursement' THEN montant ELSE 0 END) as total_remboursements
    FROM paiements
");
$stats = $stmt->fetch();

// Paiements récents
$stmt = $db->query("
    SELECT p.*, u.nom, u.prenom, u.classe
    FROM paiements p
    JOIN utilisateurs u ON p.utilisateur_id = u.id
    ORDER BY p.date_paiement DESC
    LIMIT 50
");
$paiements = $stmt->fetchAll();

// Montant total de la cantine (revenus nets)
$montant_total_cantine = $stats['total_paiements_montant'] - $stats['total_remboursements'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCantine - Paiements</title>
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
                    <li><a href="admin_statistiques.php"><span class="icon">📈</span> Statistiques</a></li>
                    <li><a href="admin_suivi_nutritionnel.php"><span class="icon">🏥</span> Suivi Nutritionnel</a></li>
                    <li><a href="admin_paiements.php" class="active"><span class="icon">💰</span> Paiements</a></li>
                    <li><a href="php/logout.php"><span class="icon">🚪</span> Déconnexion</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h1>Gestion des Paiements</h1>
                <p>Suivi des paiements et revenus de la cantine</p>
            </div>

            <!-- Statistiques -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total des Paiements</h3>
                    <div class="value"><?php echo $stats['total_paiements']; ?></div>
                    <p class="label">transactions effectuées</p>
                </div>

                <div class="stat-card">
                    <h3>Revenus Cantine</h3>
                    <div class="value"><?php echo formatPrice($stats['total_paiements_montant']); ?></div>
                    <p class="label">montant total payé</p>
                </div>

                <div class="stat-card">
                    <h3>Total Recharges</h3>
                    <div class="value"><?php echo formatPrice($stats['total_recharges']); ?></div>
                    <p class="label">crédités aux comptes</p>
                </div>

                <div class="stat-card">
                    <h3>Montant Net</h3>
                    <div class="value"><?php echo formatPrice($montant_total_cantine); ?></div>
                    <p class="label">revenus nets de la cantine</p>
                </div>
            </div>

            <!-- Liste des paiements -->
            <div class="card">
                <div class="card-header">
                    <h2>Historique des Paiements</h2>
                </div>
                <div class="card-body">
                    <?php if (count($paiements) > 0): ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Date</th>
                                        <th>Élève</th>
                                        <th>Classe</th>
                                        <th>Type</th>
                                        <th>Montant</th>
                                        <th>Commande</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($paiements as $paiement): ?>
                                        <tr>
                                            <td>#<?php echo $paiement['id']; ?></td>
                                            <td><?php echo formatDate($paiement['date_paiement']); ?></td>
                                            <td><?php echo htmlspecialchars($paiement['prenom'] . ' ' . $paiement['nom']); ?></td>
                                            <td>
                                                <span class="badge badge-plat">
                                                    <?php echo htmlspecialchars($paiement['classe']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $type_classes = [
                                                    'paiement' => 'status-paye',
                                                    'recharge' => 'status-en-attente',
                                                    'remboursement' => 'status-annule'
                                                ];
                                                $type_labels = [
                                                    'paiement' => '💳 Paiement',
                                                    'recharge' => '💰 Recharge',
                                                    'remboursement' => '↩️ Remboursement'
                                                ];
                                                ?>
                                                <span class="status-badge <?php echo $type_classes[$paiement['type_operation']]; ?>">
                                                    <?php echo $type_labels[$paiement['type_operation']]; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong style="color: <?php echo $paiement['type_operation'] === 'remboursement' ? 'var(--danger)' : 'var(--success)'; ?>; font-size: 16px;">
                                                    <?php echo $paiement['type_operation'] === 'remboursement' ? '-' : '+'; ?>
                                                    <?php echo formatPrice($paiement['montant']); ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <?php if ($paiement['commande_id']): ?>
                                                    <a href="#" onclick="voirCommande(<?php echo $paiement['commande_id']; ?>); return false;" class="btn btn-secondary btn-icon">
                                                        #<?php echo $paiement['commande_id']; ?>
                                                    </a>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">💰</div>
                            <h3>Aucun paiement</h3>
                            <p>Aucun paiement n'a encore été effectué</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        function voirCommande(commandeId) {
            alert('Détails de la commande #' + commandeId + '\nCette fonctionnalité peut être étendue pour afficher plus de détails.');
        }
    </script>
</body>
</html>
