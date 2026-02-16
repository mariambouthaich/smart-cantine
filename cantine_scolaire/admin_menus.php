<?php
require_once 'php/config.php';

if (!isLoggedIn() || getUserType() !== 'admin') {
    header('Location: index.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$message = '';

// Récupérer tous les menus
$stmt = $db->query("
    SELECT m.*, COUNT(DISTINCT mp.plat_id) as nb_plats
    FROM menus m
    LEFT JOIN menu_plats mp ON m.id = mp.menu_id
    GROUP BY m.id
    ORDER BY m.date_menu DESC
");
$menus = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCantine - Gestion Menus</title>
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
                    <li><a href="admin_menus.php" class="active"><span class="icon">📋</span> Gestion Menus</a></li>
                    <li><a href="admin_statistiques.php"><span class="icon">📈</span> Statistiques</a></li>
                    <li><a href="admin_suivi_nutritionnel.php"><span class="icon">🏥</span> Suivi Nutritionnel</a></li>
                    <li><a href="admin_paiements.php"><span class="icon">💰</span> Paiements</a></li>
                    <li><a href="php/logout.php"><span class="icon">🚪</span> Déconnexion</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h1>Gestion des Menus</h1>
                <p>Consulter les menus créés automatiquement lors des commandes</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h2>Liste des Menus</h2>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        ℹ️ Note : Les menus sont créés automatiquement lorsqu'un élève passe une commande pour une date spécifique. Les plats sont associés au menu en fonction de ce que les élèves commandent.
                    </div>

                    <?php if (count($menus) > 0): ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Date du Menu</th>
                                        <th>Nombre de Plats</th>
                                        <th>Statut</th>
                                        <th>Commandes</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($menus as $menu): ?>
                                        <tr>
                                            <td>#<?php echo $menu['id']; ?></td>
                                            <td><strong><?php echo formatDate($menu['date_menu']); ?></strong></td>
                                            <td><?php echo $menu['nb_plats']; ?> plats</td>
                                            <td>
                                                <?php if ($menu['actif']): ?>
                                                    <span class="status-badge status-paye">✓ Actif</span>
                                                <?php else: ?>
                                                    <span class="status-badge status-annule">✗ Inactif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $stmt = $db->prepare("SELECT COUNT(*) as nb FROM commandes WHERE menu_id = :id");
                                                $stmt->execute([':id' => $menu['id']]);
                                                $nb_commandes = $stmt->fetch()['nb'];
                                                ?>
                                                <span class="badge badge-plat"><?php echo $nb_commandes; ?> commandes</span>
                                            </td>
                                            <td>
                                                <button onclick="voirPlats(<?php echo $menu['id']; ?>)" class="btn btn-secondary btn-icon">
                                                    👁️ Voir les plats
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📋</div>
                            <h3>Aucun menu</h3>
                            <p>Aucun menu n'a encore été créé. Les menus sont créés automatiquement lors des commandes.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Plats du Menu -->
    <div id="platsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Plats du Menu</h2>
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
        function voirPlats(menuId) {
            const modal = document.getElementById('platsModal');
            const modalBody = document.getElementById('modalBody');
            
            modal.classList.add('active');
            
            fetch('php/get_menu_plats.php?id=' + menuId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let html = '<div style="margin-bottom: 20px;"><strong>Date:</strong> ' + data.date + '</div>';
                        
                        if (data.plats.length > 0) {
                            html += '<div class="table-container"><table>';
                            html += '<thead><tr><th>Plat</th><th>Type</th><th>Prix</th></tr></thead>';
                            html += '<tbody>';
                            
                            data.plats.forEach(plat => {
                                html += `<tr>
                                    <td>${plat.nom}</td>
                                    <td><span class="badge badge-${plat.type}">${plat.type_label}</span></td>
                                    <td><strong>${plat.prix}</strong></td>
                                </tr>`;
                            });
                            
                            html += '</tbody></table></div>';
                        } else {
                            html += '<div class="empty-state"><p>Aucun plat associé à ce menu</p></div>';
                        }
                        
                        modalBody.innerHTML = html;
                    } else {
                        modalBody.innerHTML = '<div class="alert alert-error">Erreur lors du chargement</div>';
                    }
                })
                .catch(error => {
                    modalBody.innerHTML = '<div class="alert alert-error">Erreur de connexion</div>';
                });
        }
        
        function closeModal() {
            document.getElementById('platsModal').classList.remove('active');
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('platsModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
