<?php
require_once 'php/config.php';

if (!isLoggedIn() || getUserType() === 'admin') {
    header('Location: index.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$message = '';
$error = '';

// Traitement de l'ajout/suppression d'allergies
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ajouter_allergies'])) {
        $allergies = $_POST['allergies'] ?? [];
        
        if (count($allergies) > 0) {
            try {
                $db->beginTransaction();
                
                // Supprimer les anciennes allergies
                $stmt = $db->prepare("DELETE FROM utilisateur_allergies WHERE utilisateur_id = :id");
                $stmt->execute([':id' => $_SESSION['user_id']]);
                
                // Ajouter les nouvelles
                $stmt = $db->prepare("INSERT INTO utilisateur_allergies (utilisateur_id, allergie_id) VALUES (:user_id, :allergie_id)");
                foreach ($allergies as $allergie_id) {
                    $stmt->execute([':user_id' => $_SESSION['user_id'], ':allergie_id' => $allergie_id]);
                }
                
                $db->commit();
                $message = 'Allergies mises à jour avec succès';
            } catch (Exception $e) {
                $db->rollBack();
                $error = 'Erreur lors de la mise à jour: ' . $e->getMessage();
            }
        }
    }
}

// Récupérer toutes les allergies disponibles
$stmt = $db->query("SELECT * FROM allergies ORDER BY nom_allergie");
$toutes_allergies = $stmt->fetchAll();

// Récupérer les allergies de l'utilisateur
$stmt = $db->prepare("SELECT allergie_id FROM utilisateur_allergies WHERE utilisateur_id = :id");
$stmt->execute([':id' => $_SESSION['user_id']]);
$mes_allergies = array_column($stmt->fetchAll(), 'allergie_id');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCantine - Suivi Nutritionnel</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="dashboard">
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo"</div>
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
                    <li><a href="suivi_nutritionnel.php" class="active"><span class="icon">🏥</span> Suivi Nutritionnel</a></li>
                    <li><a href="recharger_compte.php"><span class="icon">💰</span> Recharger mon compte</a></li>
                    <li><a href="php/logout.php"><span class="icon">🚪</span> Déconnexion</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h1>Suivi Nutritionnel</h1>
                <p>Déclarez vos allergies alimentaires</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h2>Mes Allergies Alimentaires</h2>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        ⚠️ Important: Il est essentiel de déclarer toutes vos allergies alimentaires pour assurer votre sécurité. Cette information sera prise en compte lors de la préparation de vos repas.
                    </div>

                    <form method="POST" action="">
                        <div class="checkbox-group">
                            <?php foreach ($toutes_allergies as $allergie): ?>
                                <label class="checkbox-label">
                                    <input type="checkbox" 
                                           name="allergies[]" 
                                           value="<?php echo $allergie['id']; ?>"
                                           <?php echo in_array($allergie['id'], $mes_allergies) ? 'checked' : ''; ?>>
                                    <?php echo htmlspecialchars($allergie['nom_allergie']); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <button type="submit" name="ajouter_allergies" class="btn btn-primary" style="margin-top: 20px;">
                            Enregistrer mes allergies
                        </button>
                    </form>
                </div>
            </div>

            <?php if (count($mes_allergies) > 0): ?>
            <div class="card">
                <div class="card-header">
                    <h2>Mes Allergies Déclarées</h2>
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
                    $allergies_declarees = $stmt->fetchAll();
                    ?>
                    <div class="allergies-list">
                        <?php foreach ($allergies_declarees as $allergie): ?>
                            <div class="allergy-tag">
                                ⚠️ <?php echo htmlspecialchars($allergie['nom_allergie']); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h2>Conseils Nutritionnels</h2>
                </div>
                <div class="card-body">
                    <h3 style="color: var(--primary-color); margin-bottom: 15px;">🥗 Pour une alimentation équilibrée</h3>
                    <ul style="line-height: 2; color: var(--text-light);">
                        <li>Variez vos repas en choisissant différents types de plats chaque jour</li>
                        <li>Assurez-vous de consommer des fruits et légumes quotidiennement</li>
                        <li>Buvez suffisamment d'eau tout au long de la journée</li>
                        <li>Évitez de sauter des repas, particulièrement le déjeuner</li>
                        <li>En cas de doute sur un plat, n'hésitez pas à contacter l'administration</li>
                    </ul>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
