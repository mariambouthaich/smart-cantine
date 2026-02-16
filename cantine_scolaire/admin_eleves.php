<?php
require_once 'php/config.php';

if (!isLoggedIn() || getUserType() !== 'admin') {
    header('Location: index.php');
    exit;
}

$db = Database::getInstance()->getConnection();

// Récupérer tous les élèves
$stmt = $db->query("
    SELECT * FROM utilisateurs 
    WHERE type_compte IN ('eleve', 'parent')
    ORDER BY classe, nom, prenom
");
$eleves = $stmt->fetchAll();

// Statistiques par classe
$stmt = $db->query("
    SELECT classe, COUNT(*) as count 
    FROM utilisateurs 
    WHERE classe IS NOT NULL 
    GROUP BY classe 
    ORDER BY classe
");
$stats_classes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCantine - Gestion Élèves</title>
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
                    <li><a href="admin_eleves.php" class="active"><span class="icon">👥</span> Gestion Élèves</a></li>
                    <li><a href="admin_plats.php"><span class="icon">🍽️</span> Gestion Plats</a></li>
                    <li><a href="admin_menus.php"><span class="icon">📋</span> Gestion Menus</a></li>
                    <li><a href="admin_statistiques.php"><span class="icon">📈</span> Statistiques</a></li>
                    <li><a href="admin_suivi_nutritionnel.php"><span class="icon">🏥</span> Suivi Nutritionnel</a></li>
                    <li><a href="admin_paiements.php"><span class="icon">💰</span> Paiements</a></li>
                    <li><a href="php/logout.php"><span class="icon">🚪</span> Déconnexion</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h1>Gestion des Élèves</h1>
                <p>Liste complète des élèves inscrits à la cantine</p>
            </div>

            <!-- Statistiques par classe -->
            <div class="stats-grid">
                <?php foreach ($stats_classes as $stat): ?>
                    <div class="stat-card">
                        <h3>Classe <?php echo htmlspecialchars($stat['classe']); ?></h3>
                        <div class="value"><?php echo $stat['count']; ?></div>
                        <p class="label">élèves inscrits</p>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2>Tous les Élèves Inscrits</h2>
                </div>
                <div class="card-body">
                    <?php if (count($eleves) > 0): ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom</th>
                                        <th>Prénom</th>
                                        <th>Classe</th>
                                        <th>Email</th>
                                        <th>Type</th>
                                        <th>Solde</th>
                                        <th>Allergies</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($eleves as $eleve): ?>
                                        <tr>
                                            <td><strong>#<?php echo $eleve['id']; ?></strong></td>
                                            <td><?php echo htmlspecialchars($eleve['nom']); ?></td>
                                            <td><?php echo htmlspecialchars($eleve['prenom']); ?></td>
                                            <td>
                                                <span class="badge badge-plat">
                                                    <?php echo htmlspecialchars($eleve['classe']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($eleve['email']); ?></td>
                                            <td>
                                                <span class="badge <?php echo $eleve['type_compte'] === 'eleve' ? 'badge-entree' : 'badge-dessert'; ?>">
                                                    <?php echo htmlspecialchars(ucfirst($eleve['type_compte'])); ?>
                                                </span>
                                            </td>
                                            <td><strong><?php echo formatPrice($eleve['solde']); ?></strong></td>
                                            <td>
                                                <?php
                                                $stmt = $db->prepare("SELECT COUNT(*) as count FROM utilisateur_allergies WHERE utilisateur_id = :id");
                                                $stmt->execute([':id' => $eleve['id']]);
                                                $nb_allergies = $stmt->fetch()['count'];
                                                
                                                if ($nb_allergies > 0):
                                                ?>
                                                    <button onclick="voirAllergies(<?php echo $eleve['id']; ?>)" class="btn btn-secondary btn-icon">
                                                        ⚠️ <?php echo $nb_allergies; ?>
                                                    </button>
                                                <?php else: ?>
                                                    <span style="opacity: 0.5;">Aucune</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">👥</div>
                            <h3>Aucun élève inscrit</h3>
                            <p>Aucun élève n'est encore inscrit à la cantine</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Allergies -->
    <div id="allergiesModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Allergies de l'élève</h2>
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
        function voirAllergies(eleveId) {
            const modal = document.getElementById('allergiesModal');
            const modalBody = document.getElementById('modalBody');
            
            modal.classList.add('active');
            
            fetch('php/get_allergies.php?id=' + eleveId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let html = '<div><strong>Élève:</strong> ' + data.eleve + '</div><br>';
                        html += '<div class="allergies-list">';
                        
                        data.allergies.forEach(allergie => {
                            html += `<div class="allergy-tag">⚠️ ${allergie}</div>`;
                        });
                        
                        html += '</div>';
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
            document.getElementById('allergiesModal').classList.remove('active');
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('allergiesModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
