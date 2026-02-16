<?php
/**
 * Script de Renommage du Projet
 * Change "MiamiSchool" par un nouveau nom dans tous les fichiers
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

// NOUVEAU NOM DU PROJET - Modifiez cette ligne pour changer le nom
$nouveau_nom = "EcoSchool"; // Vous pouvez changer ce nom ici
$nouveau_nom_complet = "EcoSchool Cantine"; // Nom complet

// Ancien nom
$ancien_nom = "MiamiSchool";
$ancien_nom_lower = "miamischool";
$ancien_email_domain = "@miamischool.com";

// Nouveau domaine email
$nouveau_email_domain = "@" . strtolower($nouveau_nom) . ".com";

?>
<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Renommer le Projet</title>
    <link rel='stylesheet' href='css/style.css'>
</head>
<body class='login-page'>
    <div class='login-container' style='max-width: 900px;'>
        <div class='login-header'>
            <div class='logo'>🔄</div>
            <h1>Renommer le Projet</h1>
            <p>Changer "<?php echo $ancien_nom; ?>" en "<?php echo $nouveau_nom; ?>"</p>
        </div>

<?php
if (isset($_POST['renommer'])) {
    $nouveau_nom_personnalise = trim($_POST['nouveau_nom'] ?? '');
    
    if (empty($nouveau_nom_personnalise)) {
        echo "<div class='alert alert-error'>❌ Veuillez entrer un nom !</div>";
    } else {
        $nouveau_nom = $nouveau_nom_personnalise;
        $nouveau_email_domain = "@" . strtolower(str_replace(' ', '', $nouveau_nom)) . ".com";
        
        echo "<div class='alert alert-success'>✅ Renommage en cours...</div>";
        
        try {
            // Connexion à la base de données
            $pdo = new PDO("mysql:host=localhost;dbname=cantine_scolaire;charset=utf8mb4", "root", "");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Mise à jour des emails dans la base de données
            $stmt = $pdo->prepare("UPDATE utilisateurs SET email = REPLACE(email, ?, ?)");
            $stmt->execute([$ancien_email_domain, $nouveau_email_domain]);
            $nb_emails = $stmt->rowCount();
            
            echo "<div class='card'>
                    <div class='card-header'><h2>📊 Résultats du Renommage</h2></div>
                    <div class='card-body'>
                        <table style='width: 100%;'>
                            <tr>
                                <td style='padding: 10px;'><strong>Ancien nom :</strong></td>
                                <td style='padding: 10px;'>{$ancien_nom}</td>
                            </tr>
                            <tr>
                                <td style='padding: 10px;'><strong>Nouveau nom :</strong></td>
                                <td style='padding: 10px;'><span style='color: var(--primary-color); font-weight: bold;'>{$nouveau_nom}</span></td>
                            </tr>
                            <tr>
                                <td style='padding: 10px;'><strong>Emails modifiés :</strong></td>
                                <td style='padding: 10px;'>{$nb_emails} comptes</td>
                            </tr>
                            <tr>
                                <td style='padding: 10px;'><strong>Nouveau domaine :</strong></td>
                                <td style='padding: 10px;'>{$nouveau_email_domain}</td>
                            </tr>
                        </table>
                    </div>
                  </div>";
            
            // Afficher les nouveaux emails
            $stmt = $pdo->query("SELECT nom, prenom, email, type_compte FROM utilisateurs");
            $users = $stmt->fetchAll();
            
            echo "<div class='card'>
                    <div class='card-header'><h2>📧 Nouveaux Emails</h2></div>
                    <div class='card-body'>
                        <table style='width: 100%;'>
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Type</th>
                                    <th>Nouvel Email</th>
                                </tr>
                            </thead>
                            <tbody>";
            
            foreach ($users as $user) {
                $badge = $user['type_compte'] === 'admin' ? 'status-annule' : 
                        ($user['type_compte'] === 'eleve' ? 'status-paye' : 'status-en-attente');
                
                echo "<tr>
                        <td>{$user['prenom']} {$user['nom']}</td>
                        <td><span class='status-badge {$badge}'>{$user['type_compte']}</span></td>
                        <td><code style='background: #2a2412; padding: 5px 10px; border-radius: 5px;'>{$user['email']}</code></td>
                      </tr>";
            }
            
            echo "      </tbody>
                        </table>
                    </div>
                  </div>";
            
            echo "<div class='alert alert-success'>
                    ✅ <strong>Renommage terminé !</strong><br><br>
                    Le nom a été changé dans la base de données.<br>
                    Pour un renommage complet, vous devez aussi modifier manuellement :<br>
                    <ul style='margin-top: 10px; padding-left: 20px;'>
                        <li>Le titre des pages HTML</li>
                        <li>Les logos et en-têtes</li>
                        <li>Le nom de la base de données (optionnel)</li>
                    </ul>
                  </div>";
            
            echo "<div style='text-align: center; margin-top: 20px;'>
                    <a href='index.php' class='btn btn-primary btn-block'>
                        🔐 Tester la Connexion avec les Nouveaux Emails
                    </a>
                  </div>";
            
        } catch (PDOException $e) {
            echo "<div class='alert alert-error'>❌ Erreur : {$e->getMessage()}</div>";
        }
    }
} else {
    // Suggestions de noms
    $suggestions = [
        "EcoSchool",
        "SmartCantine",
        "SchoolFood",
        "EdukFood",
        "NutriSchool",
        "CantiPlus",
        "ScholarMeal",
        "FoodAcademy",
        "SchoolDining",
        "MySchoolFood"
    ];
    
    echo "<div class='card'>
            <div class='card-header'><h2>✏️ Choisir un Nouveau Nom</h2></div>
            <div class='card-body'>
                <form method='POST'>
                    <div class='form-group'>
                        <label for='nouveau_nom'>Nouveau nom du projet</label>
                        <input type='text' 
                               name='nouveau_nom' 
                               id='nouveau_nom' 
                               placeholder='Ex: EcoSchool' 
                               required
                               style='font-size: 18px; padding: 15px;'>
                        <small>Utilisez un seul mot, sans espaces (Ex: EcoSchool, SmartCantine, etc.)</small>
                    </div>
                    
                    <button type='submit' name='renommer' class='btn btn-primary btn-block' style='padding: 15px; font-size: 16px;'>
                        🔄 Renommer le Projet
                    </button>
                </form>
            </div>
          </div>";
    
    echo "<div class='card'>
            <div class='card-header'><h2>💡 Suggestions de Noms</h2></div>
            <div class='card-body'>
                <div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;'>";
    
    foreach ($suggestions as $nom) {
        echo "<button onclick='document.getElementById(\"nouveau_nom\").value=\"{$nom}\"' 
                      class='btn btn-secondary' 
                      style='padding: 12px;'>
                {$nom}
              </button>";
    }
    
    echo "    </div>
            </div>
          </div>";
    
    echo "<div class='alert alert-warning'>
            <strong>⚠️ Ce qui sera modifié :</strong><br>
            <ul style='margin-top: 10px; padding-left: 20px;'>
                <li>✅ Tous les emails dans la base de données</li>
                <li>✅ Le domaine email (@miamischool.com → @votrenouveau.com)</li>
                <li>⚠️ Vous devrez modifier manuellement le nom dans les pages HTML si vous le souhaitez</li>
            </ul>
          </div>";
}
?>

    </div>
</body>
</html>