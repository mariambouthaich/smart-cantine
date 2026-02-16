<?php
require_once 'php/config.php';

if (!isLoggedIn() || getUserType() === 'admin') {
    header('Location: index.php');
    exit;
}

$db = Database::getInstance()->getConnection();

// Récupérer les informations de l'utilisateur
$stmt = $db->prepare("SELECT * FROM utilisateurs WHERE id = :id");
$stmt->execute([':id' => $_SESSION['user_id']]);
$user = $stmt->fetch();

// Mettre à jour la session avec le solde actuel
$_SESSION['user_solde'] = $user['solde'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCantine - Mon Espace</title>
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
                <p><?php echo htmlspecialchars(getUserType() === 'eleve' ? 'Élève' : 'Parent'); ?> - Classe: <?php echo htmlspecialchars($user['classe']); ?></p>
                <div class="solde"><?php echo formatPrice($user['solde']); ?></div>
            </div>

            <nav>
                <ul class="nav-menu">
                    <li>
                        <a href="eleve_dashboard.php" class="active">
                            <span class="icon">📊</span>
                            Tableau de bord
                        </a>
                    </li>
                    <li>
                        <a href="menus.php">
                            <span class="icon">🍽️</span>
                            Menus & Commandes
                        </a>
                    </li>
                    <li>
                        <a href="mes_commandes.php">
                            <span class="icon">📋</span>
                            Mes Commandes
                        </a>
                    </li>
                    <li>
                        <a href="suivi_nutritionnel.php">
                            <span class="icon">🏥</span>
                            Suivi Nutritionnel
                        </a>
                    </li>
                    <li>
                        <a href="recharger_compte.php">
                            <span class="icon">💰</span>
                            Recharger mon compte
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
                <h1>Bienvenue, <?php echo htmlspecialchars($user['prenom']); ?> !</h1>
                <p>Gérez vos repas et votre compte en toute simplicité</p>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <?php
                // Nombre de commandes en cours
                $stmt = $db->prepare("SELECT COUNT(*) as count FROM commandes WHERE utilisateur_id = :id AND statut = 'en_attente'");
                $stmt->execute([':id' => $_SESSION['user_id']]);
                $commandes_en_cours = $stmt->fetch()['count'];

                // Nombre total de commandes
                $stmt = $db->prepare("SELECT COUNT(*) as count FROM commandes WHERE utilisateur_id = :id");
                $stmt->execute([':id' => $_SESSION['user_id']]);
                $total_commandes = $stmt->fetch()['count'];

                // Nombre d'allergies déclarées
                $stmt = $db->prepare("SELECT COUNT(*) as count FROM utilisateur_allergies WHERE utilisateur_id = :id");
                $stmt->execute([':id' => $_SESSION['user_id']]);
                $nb_allergies = $stmt->fetch()['count'];

                // Total dépensé ce mois
                $stmt = $db->prepare("SELECT SUM(montant_total) as total FROM commandes WHERE utilisateur_id = :id AND statut = 'paye' AND MONTH(date_commande) = MONTH(CURRENT_DATE()) AND YEAR(date_commande) = YEAR(CURRENT_DATE())");
                $stmt->execute([':id' => $_SESSION['user_id']]);
                $depenses_mois = $stmt->fetch()['total'] ?? 0;
                ?>

                <div class="stat-card">
                    <h3>Solde Disponible</h3>
                    <div class="value"><?php echo formatPrice($user['solde']); ?></div>
                    <p class="label">Votre crédit actuel</p>
                </div>

                <div class="stat-card">
                    <h3>Commandes en Cours</h3>
                    <div class="value"><?php echo $commandes_en_cours; ?></div>
                    <p class="label">En attente de paiement</p>
                </div>

                <div class="stat-card">
                    <h3>Total Commandes</h3>
                    <div class="value"><?php echo $total_commandes; ?></div>
                    <p class="label">Depuis votre inscription</p>
                </div>

                <div class="stat-card">
                    <h3>Dépenses du Mois</h3>
                    <div class="value"><?php echo formatPrice($depenses_mois); ?></div>
                    <p class="label"><?php echo date('F Y'); ?></p>
                </div>
            </div>

            <!-- Dernières commandes -->
            <div class="card">
                <div class="card-header">
                    <h2>Mes Dernières Commandes</h2>
                    <a href="mes_commandes.php" class="btn btn-secondary">Voir tout</a>
                </div>
                <div class="card-body">
                    <?php
                    $stmt = $db->prepare("
                        SELECT c.*, m.date_menu 
                        FROM commandes c 
                        JOIN menus m ON c.menu_id = m.id 
                        WHERE c.utilisateur_id = :id 
                        ORDER BY c.date_commande DESC 
                        LIMIT 5
                    ");
                    $stmt->execute([':id' => $_SESSION['user_id']]);
                    $commandes = $stmt->fetchAll();

                    if (count($commandes) > 0):
                    ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Numéro</th>
                                        <th>Date Livraison</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                        <th>Date Commande</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($commandes as $cmd): ?>
                                        <tr>
                                            <td>#<?php echo $cmd['id']; ?></td>
                                            <td><?php echo formatDate($cmd['date_livraison']); ?></td>
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
                            <div class="empty-state-icon">🍽️</div>
                            <h3>Aucune commande</h3>
                            <p>Vous n'avez pas encore passé de commande</p>
                            <a href="menus.php" class="btn btn-primary" style="margin-top: 15px;">Commander maintenant</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Allergies -->
            <?php if ($nb_allergies > 0): ?>
            <div class="card">
                <div class="card-header">
                    <h2>Mes Allergies Déclarées</h2>
                    <a href="suivi_nutritionnel.php" class="btn btn-secondary">Gérer</a>
                </div>
                <div class="card-body">
                    <?php
                    $stmt = $db->prepare("
                        SELECT a.nom_allergie 
                        FROM utilisateur_allergies ua 
                        JOIN allergies a ON ua.allergie_id = a.id 
                        WHERE ua.utilisateur_id = :id
                    ");
                    $stmt->execute([':id' => $_SESSION['user_id']]);
                    $allergies = $stmt->fetchAll();
                    ?>
                    <div class="allergies-list">
                        <?php foreach ($allergies as $allergie): ?>
                            <div class="allergy-tag">
                                ⚠️ <?php echo htmlspecialchars($allergie['nom_allergie']); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </main>
    </div>
</body>
</html>
