<?php
require_once 'php/config.php';

if (!isLoggedIn() || getUserType() === 'admin') {
    header('Location: index.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$message = '';
$error = '';

// Gestion de l'annulation de commande
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['annuler_commande'])) {
    $commande_id = $_POST['commande_id'] ?? 0;
    
    try {
        $db->beginTransaction();
        
        // Vérifier que la commande appartient à l'utilisateur et est en attente
        $stmt = $db->prepare("SELECT * FROM commandes WHERE id = :id AND utilisateur_id = :user_id AND statut = 'en_attente'");
        $stmt->execute([':id' => $commande_id, ':user_id' => $_SESSION['user_id']]);
        $commande = $stmt->fetch();
        
        if (!$commande) {
            $error = 'Commande introuvable ou déjà traitée';
        } else {
            // Rembourser le montant
            $stmt = $db->prepare("UPDATE utilisateurs SET solde = solde + :montant WHERE id = :id");
            $stmt->execute([':montant' => $commande['montant_total'], ':id' => $_SESSION['user_id']]);
            
            // Enregistrer le remboursement
            $stmt = $db->prepare("
                INSERT INTO paiements (utilisateur_id, montant, type_operation, commande_id)
                VALUES (:user_id, :montant, 'remboursement', :commande_id)
            ");
            $stmt->execute([
                ':user_id' => $_SESSION['user_id'],
                ':montant' => $commande['montant_total'],
                ':commande_id' => $commande_id
            ]);
            
            // Mettre à jour le statut
            $stmt = $db->prepare("UPDATE commandes SET statut = 'annule' WHERE id = :id");
            $stmt->execute([':id' => $commande_id]);
            
            $db->commit();
            
            $_SESSION['user_solde'] += $commande['montant_total'];
            $message = 'Commande annulée avec succès. Montant remboursé: ' . formatPrice($commande['montant_total']);
        }
    } catch (Exception $e) {
        $db->rollBack();
        $error = 'Erreur lors de l\'annulation: ' . $e->getMessage();
    }
}

// Récupérer toutes les commandes
$stmt = $db->prepare("
    SELECT c.*, m.date_menu 
    FROM commandes c 
    LEFT JOIN menus m ON c.menu_id = m.id 
    WHERE c.utilisateur_id = :id 
    ORDER BY c.date_commande DESC
");
$stmt->execute([':id' => $_SESSION['user_id']]);
$commandes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCantine - Mes Commandes</title>
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
                    <li><a href="mes_commandes.php" class="active"><span class="icon">📋</span> Mes Commandes</a></li>
                    <li><a href="suivi_nutritionnel.php"><span class="icon">🏥</span> Suivi Nutritionnel</a></li>
                    <li><a href="recharger_compte.php"><span class="icon">💰</span> Recharger mon compte</a></li>
                    <li><a href="php/logout.php"><span class="icon">🚪</span> Déconnexion</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h1>Mes Commandes</h1>
                <p>Historique et suivi de vos commandes</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h2>Toutes mes commandes</h2>
                    <a href="menus.php" class="btn btn-primary">Nouvelle commande</a>
                </div>
                <div class="card-body">
                    <?php if (count($commandes) > 0): ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Numéro</th>
                                        <th>Date Commande</th>
                                        <th>Date Livraison</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($commandes as $cmd): ?>
                                        <tr>
                                            <td><strong>#<?php echo $cmd['id']; ?></strong></td>
                                            <td><?php echo formatDate($cmd['date_commande']); ?></td>
                                            <td><?php echo formatDate($cmd['date_livraison']); ?></td>
                                            <td><strong><?php echo formatPrice($cmd['montant_total']); ?></strong></td>
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
                                            <td>
                                                <div class="action-buttons">
                                                    <button onclick="voirDetails(<?php echo $cmd['id']; ?>)" class="btn btn-secondary btn-icon">
                                                        👁️ Détails
                                                    </button>
                                                    <?php if ($cmd['statut'] === 'en_attente'): ?>
                                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette commande ?');">
                                                            <input type="hidden" name="commande_id" value="<?php echo $cmd['id']; ?>">
                                                            <button type="submit" name="annuler_commande" class="btn btn-danger btn-icon">
                                                                ❌ Annuler
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📋</div>
                            <h3>Aucune commande</h3>
                            <p>Vous n'avez pas encore passé de commande</p>
                            <a href="menus.php" class="btn btn-primary" style="margin-top: 15px;">Commander maintenant</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Détails -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Détails de la commande</h2>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <div class="loading">
                    <div class="spinner"></div>
                    <p>Chargement...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function voirDetails(commandeId) {
            const modal = document.getElementById('detailsModal');
            const modalBody = document.getElementById('modalBody');
            
            modal.classList.add('active');
            
            fetch('php/get_commande_details.php?id=' + commandeId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let html = '<div class="table-container"><table>';
                        html += '<thead><tr><th>Plat</th><th>Type</th><th>Prix</th></tr></thead>';
                        html += '<tbody>';
                        
                        data.details.forEach(detail => {
                            html += `<tr>
                                <td>${detail.nom}</td>
                                <td><span class="badge badge-${detail.type}">${detail.type_label}</span></td>
                                <td><strong>${detail.prix}</strong></td>
                            </tr>`;
                        });
                        
                        html += '</tbody></table></div>';
                        html += `<div class="cart-total" style="margin-top: 20px;">
                            <span>Total:</span>
                            <span class="amount">${data.total}</span>
                        </div>`;
                        
                        modalBody.innerHTML = html;
                    } else {
                        modalBody.innerHTML = '<div class="alert alert-error">Erreur lors du chargement des détails</div>';
                    }
                })
                .catch(error => {
                    modalBody.innerHTML = '<div class="alert alert-error">Erreur de connexion</div>';
                });
        }
        
        function closeModal() {
            document.getElementById('detailsModal').classList.remove('active');
        }
        
        // Fermer le modal en cliquant en dehors
        window.onclick = function(event) {
            const modal = document.getElementById('detailsModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
