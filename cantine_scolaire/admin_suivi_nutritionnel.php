<?php
require_once 'php/config.php';

if (!isLoggedIn() || getUserType() !== 'admin') {
    header('Location: index.php');
    exit;
}

$db = Database::getInstance()->getConnection();

// Statistiques des allergies par classe
$stmt = $db->query("
    SELECT u.classe, COUNT(DISTINCT ua.utilisateur_id) as nb_eleves_allergiques
    FROM utilisateurs u
    JOIN utilisateur_allergies ua ON u.id = ua.utilisateur_id
    WHERE u.classe IS NOT NULL
    GROUP BY u.classe
    ORDER BY u.classe
");
$stats_allergies_classe = $stmt->fetchAll();

// Liste des élèves avec allergies par classe
$stmt = $db->query("
    SELECT DISTINCT u.id, u.nom, u.prenom, u.classe
    FROM utilisateurs u
    JOIN utilisateur_allergies ua ON u.id = ua.utilisateur_id
    WHERE u.classe IS NOT NULL
    ORDER BY u.classe, u.nom, u.prenom
");
$eleves_avec_allergies = $stmt->fetchAll();

// Statistiques des allergies les plus courantes
$stmt = $db->query("
    SELECT a.nom_allergie, COUNT(ua.id) as nb_eleves
    FROM allergies a
    JOIN utilisateur_allergies ua ON a.id = ua.allergie_id
    GROUP BY a.id
    ORDER BY nb_eleves DESC
");
$allergies_courantes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCantine - Suivi Nutritionnel Admin</title>
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
                    <li><a href="admin_suivi_nutritionnel.php" class="active"><span class="icon">🏥</span> Suivi Nutritionnel</a></li>
                    <li><a href="admin_paiements.php"><span class="icon">💰</span> Paiements</a></li>
                    <li><a href="php/logout.php"><span class="icon">🚪</span> Déconnexion</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h1>Suivi Nutritionnel</h1>
                <p>Statistiques des allergies par classe</p>
            </div>

            <!-- Statistiques par classe -->
            <div class="card">
                <div class="card-header">
                    <h2>Nombre d'Élèves Allergiques par Classe</h2>
                </div>
                <div class="card-body">
                    <div class="stats-grid">
                        <?php foreach ($stats_allergies_classe as $stat): ?>
                            <div class="stat-card">
                                <h3>Classe <?php echo htmlspecialchars($stat['classe']); ?></h3>
                                <div class="value"><?php echo $stat['nb_eleves_allergiques']; ?></div>
                                <p class="label">élèves avec allergies</p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Allergies les plus courantes -->
            <div class="card">
                <div class="card-header">
                    <h2>Allergies les Plus Courantes</h2>
                </div>
                <div class="card-body">
                    <?php if (count($allergies_courantes) > 0): ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Allergie</th>
                                        <th>Nombre d'Élèves</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($allergies_courantes as $allergie): ?>
                                        <tr>
                                            <td>
                                                <div class="allergy-tag">
                                                    ⚠️ <?php echo htmlspecialchars($allergie['nom_allergie']); ?>
                                                </div>
                                            </td>
                                            <td><strong style="color: var(--primary-color); font-size: 18px;"><?php echo $allergie['nb_eleves']; ?></strong> élèves</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">🏥</div>
                            <h3>Aucune allergie déclarée</h3>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Liste des élèves avec allergies -->
            <div class="card">
                <div class="card-header">
                    <h2>Élèves avec Allergies Déclarées</h2>
                </div>
                <div class="card-body">
                    <?php if (count($eleves_avec_allergies) > 0): ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom</th>
                                        <th>Prénom</th>
                                        <th>Classe</th>
                                        <th>Allergies</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($eleves_avec_allergies as $eleve): ?>
                                        <tr>
                                            <td>#<?php echo $eleve['id']; ?></td>
                                            <td><?php echo htmlspecialchars($eleve['nom']); ?></td>
                                            <td><?php echo htmlspecialchars($eleve['prenom']); ?></td>
                                            <td>
                                                <span class="badge badge-plat">
                                                    <?php echo htmlspecialchars($eleve['classe']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button onclick="voirAllergies(<?php echo $eleve['id']; ?>)" class="btn btn-secondary btn-icon">
                                                    👁️ Voir les allergies
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">🏥</div>
                            <h3>Aucune allergie déclarée</h3>
                            <p>Aucun élève n'a déclaré d'allergie</p>
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
                        let html = '<div style="margin-bottom: 20px;"><strong>Élève:</strong> ' + data.eleve + '</div>';
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
