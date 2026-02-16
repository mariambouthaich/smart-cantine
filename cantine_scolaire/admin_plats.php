<?php
require_once 'php/config.php';

if (!isLoggedIn() || getUserType() !== 'admin') {
    header('Location: index.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$message = '';
$error = '';

// Ajouter un nouveau plat
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_plat'])) {
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $type_plat = $_POST['type_plat'] ?? '';
    $prix = floatval($_POST['prix'] ?? 0);
    
    if (empty($nom) || empty($type_plat) || $prix <= 0) {
        $error = 'Tous les champs sont obligatoires';
    } else {
        $stmt = $db->prepare("INSERT INTO plats (nom, description, type_plat, prix) VALUES (:nom, :description, :type_plat, :prix)");
        if ($stmt->execute([':nom' => $nom, ':description' => $description, ':type_plat' => $type_plat, ':prix' => $prix])) {
            $message = 'Plat ajouté avec succès';
        } else {
            $error = 'Erreur lors de l\'ajout';
        }
    }
}

// Modifier un plat
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier_plat'])) {
    $plat_id = $_POST['plat_id'] ?? 0;
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $type_plat = $_POST['type_plat'] ?? '';
    $prix = floatval($_POST['prix'] ?? 0);
    $disponible = isset($_POST['disponible']) ? 1 : 0;
    
    if (empty($nom) || empty($type_plat) || $prix <= 0) {
        $error = 'Tous les champs sont obligatoires';
    } else {
        $stmt = $db->prepare("
            UPDATE plats 
            SET nom = :nom, description = :description, type_plat = :type_plat, prix = :prix, disponible = :disponible
            WHERE id = :id
        ");
        if ($stmt->execute([
            ':nom' => $nom, 
            ':description' => $description, 
            ':type_plat' => $type_plat, 
            ':prix' => $prix,
            ':disponible' => $disponible,
            ':id' => $plat_id
        ])) {
            $message = 'Plat modifié avec succès';
        } else {
            $error = 'Erreur lors de la modification';
        }
    }
}

// Supprimer un plat
if (isset($_GET['supprimer']) && is_numeric($_GET['supprimer'])) {
    $plat_id = $_GET['supprimer'];
    $stmt = $db->prepare("DELETE FROM plats WHERE id = :id");
    if ($stmt->execute([':id' => $plat_id])) {
        $message = 'Plat supprimé avec succès';
    } else {
        $error = 'Erreur lors de la suppression';
    }
}

// Récupérer tous les plats
$stmt = $db->query("SELECT * FROM plats ORDER BY type_plat, nom");
$plats = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCantine - Gestion Plats</title>
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
                    <li><a href="admin_plats.php" class="active"><span class="icon">🍽️</span> Gestion Plats</a></li>
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
                <h1>Gestion des Plats</h1>
                <p>Ajouter, modifier ou supprimer des plats</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h2>Ajouter un Nouveau Plat</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nom">Nom du Plat</label>
                                <input type="text" name="nom" id="nom" required>
                            </div>

                            <div class="form-group">
                                <label for="type_plat">Type de Plat</label>
                                <select name="type_plat" id="type_plat" required>
                                    <option value="">Sélectionner...</option>
                                    <option value="entree">Entrée</option>
                                    <option value="plat_principal">Plat Principal</option>
                                    <option value="dessert">Dessert</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" rows="3"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="prix">Prix (DH)</label>
                            <input type="number" name="prix" id="prix" step="0.01" min="0" required>
                        </div>

                        <button type="submit" name="ajouter_plat" class="btn btn-primary">
                            ➕ Ajouter le Plat
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2>Liste des Plats</h2>
                </div>
                <div class="card-body">
                    <?php if (count($plats) > 0): ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom</th>
                                        <th>Type</th>
                                        <th>Prix</th>
                                        <th>Disponible</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($plats as $plat): ?>
                                        <tr>
                                            <td><?php echo $plat['id']; ?></td>
                                            <td><strong><?php echo htmlspecialchars($plat['nom']); ?></strong></td>
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
                                            <td><strong><?php echo formatPrice($plat['prix']); ?></strong></td>
                                            <td>
                                                <?php if ($plat['disponible']): ?>
                                                    <span class="status-badge status-paye">✓ Oui</span>
                                                <?php else: ?>
                                                    <span class="status-badge status-annule">✗ Non</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button onclick="modifierPlat(<?php echo htmlspecialchars(json_encode($plat)); ?>)" class="btn btn-secondary btn-icon">
                                                        ✏️ Modifier
                                                    </button>
                                                    <a href="?supprimer=<?php echo $plat['id']; ?>" 
                                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce plat ?')"
                                                       class="btn btn-danger btn-icon">
                                                        🗑️ Supprimer
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">🍽️</div>
                            <h3>Aucun plat</h3>
                            <p>Aucun plat n'a été ajouté</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Modification -->
    <div id="modifierModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Modifier le Plat</h2>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <input type="hidden" name="plat_id" id="edit_plat_id">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_nom">Nom du Plat</label>
                            <input type="text" name="nom" id="edit_nom" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_type_plat">Type de Plat</label>
                            <select name="type_plat" id="edit_type_plat" required>
                                <option value="entree">Entrée</option>
                                <option value="plat_principal">Plat Principal</option>
                                <option value="dessert">Dessert</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_description">Description</label>
                        <textarea name="description" id="edit_description" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="edit_prix">Prix (DH)</label>
                        <input type="number" name="prix" id="edit_prix" step="0.01" min="0" required>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="disponible" id="edit_disponible">
                            Plat disponible
                        </label>
                    </div>

                    <button type="submit" name="modifier_plat" class="btn btn-primary btn-block">
                        💾 Enregistrer les modifications
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function modifierPlat(plat) {
            document.getElementById('edit_plat_id').value = plat.id;
            document.getElementById('edit_nom').value = plat.nom;
            document.getElementById('edit_description').value = plat.description;
            document.getElementById('edit_type_plat').value = plat.type_plat;
            document.getElementById('edit_prix').value = plat.prix;
            document.getElementById('edit_disponible').checked = plat.disponible == 1;
            
            document.getElementById('modifierModal').classList.add('active');
        }
        
        function closeModal() {
            document.getElementById('modifierModal').classList.remove('active');
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('modifierModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
