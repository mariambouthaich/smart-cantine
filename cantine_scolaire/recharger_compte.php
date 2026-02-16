<?php
require_once 'php/config.php';

if (!isLoggedIn() || getUserType() === 'admin') {
    header('Location: index.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$message = '';
$error = '';

// Traitement du rechargement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recharger'])) {
    $montant = floatval($_POST['montant'] ?? 0);
    
    if ($montant <= 0) {
        $error = 'Veuillez entrer un montant valide';
    } elseif ($montant < 50) {
        $error = 'Le montant minimum est de 50 DH';
    } elseif ($montant > 5000) {
        $error = 'Le montant maximum est de 5000 DH';
    } else {
        try {
            $db->beginTransaction();
            
            // Créditer le compte
            $stmt = $db->prepare("UPDATE utilisateurs SET solde = solde + :montant WHERE id = :id");
            $stmt->execute([':montant' => $montant, ':id' => $_SESSION['user_id']]);
            
            // Enregistrer la recharge
            $stmt = $db->prepare("
                INSERT INTO paiements (utilisateur_id, montant, type_operation)
                VALUES (:user_id, :montant, 'recharge')
            ");
            $stmt->execute([':user_id' => $_SESSION['user_id'], ':montant' => $montant]);
            
            $db->commit();
            
            $_SESSION['user_solde'] += $montant;
            $message = 'Compte rechargé avec succès ! Nouveau solde: ' . formatPrice($_SESSION['user_solde']);
        } catch (Exception $e) {
            $db->rollBack();
            $error = 'Erreur lors du rechargement: ' . $e->getMessage();
        }
    }
}

// Historique des recharges
$stmt = $db->prepare("
    SELECT * FROM paiements 
    WHERE utilisateur_id = :id AND type_operation = 'recharge'
    ORDER BY date_paiement DESC
    LIMIT 10
");
$stmt->execute([':id' => $_SESSION['user_id']]);
$historique = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCantine - Recharger mon Compte</title>
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
                <div class="user-icon">👤</div>
                <h3><?php echo htmlspecialchars($_SESSION['user_nom']); ?></h3>
                <div class="solde"><?php echo formatPrice($_SESSION['user_solde']); ?></div>
            </div>

            <nav>
                <ul class="nav-menu">
                    <li><a href="eleve_dashboard.php"><span class="icon">📊</span> Tableau de bord</a></li>
                    <li><a href="menus.php"><span class="icon">🍽️</span> Menus & Commandes</a></li>
                    <li><a href="mes_commandes.php"><span class="icon">📋</span> Mes Commandes</a></li>
                    <li><a href="suivi_nutritionnel.php"><span class="icon">🏥</span> Suivi Nutritionnel</a></li>
                    <li><a href="recharger_compte.php" class="active"><span class="icon">💰</span> Recharger mon compte</a></li>
                    <li><a href="php/logout.php"><span class="icon">🚪</span> Déconnexion</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h1>Recharger mon Compte</h1>
                <p>Ajoutez du crédit à votre compte cantine</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="stats-grid" style="margin-bottom: 30px;">
                <div class="stat-card">
                    <h3>Solde Actuel</h3>
                    <div class="value"><?php echo formatPrice($_SESSION['user_solde']); ?></div>
                    <p class="label">Disponible sur votre compte</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2>Recharger votre Compte</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="montant">Montant à recharger (DH)</label>
                            <input type="number" name="montant" id="montant" 
                                   min="50" max="5000" step="10" 
                                   placeholder="Entrez le montant" required>
                            <small>Minimum: 50 DH | Maximum: 5000 DH</small>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px; margin: 20px 0;">
                            <button type="button" onclick="setMontant(100)" class="btn btn-secondary">100 DH</button>
                            <button type="button" onclick="setMontant(200)" class="btn btn-secondary">200 DH</button>
                            <button type="button" onclick="setMontant(300)" class="btn btn-secondary">300 DH</button>
                            <button type="button" onclick="setMontant(500)" class="btn btn-secondary">500 DH</button>
                            <button type="button" onclick="setMontant(1000)" class="btn btn-secondary">1000 DH</button>
                        </div>

                        <div class="alert alert-warning">
                            ℹ️ Note: Il s'agit d'une simulation. Dans un système réel, cette page serait intégrée avec un système de paiement sécurisé (carte bancaire, mobile money, etc.).
                        </div>

                        <button type="submit" name="recharger" class="btn btn-primary btn-block">
                            💳 Recharger maintenant
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2>Historique des Recharges</h2>
                </div>
                <div class="card-body">
                    <?php if (count($historique) > 0): ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Montant</th>
                                        <th>Type</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historique as $paiement): ?>
                                        <tr>
                                            <td><?php echo formatDate($paiement['date_paiement']); ?></td>
                                            <td><strong><?php echo formatPrice($paiement['montant']); ?></strong></td>
                                            <td><span class="status-badge status-paye">Recharge</span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">💰</div>
                            <h3>Aucun historique</h3>
                            <p>Vous n'avez pas encore effectué de recharge</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        function setMontant(montant) {
            document.getElementById('montant').value = montant;
        }
    </script>
</body>
</html>
