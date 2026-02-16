<?php
/**
 * Test Spécifique pour le Compte Admin
 * Ce script teste directement la connexion admin et affiche les détails
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$dbname = 'cantine_scolaire';
$username = 'root';
$password = '';

?>
<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Test Compte Admin - MiamiSchool</title>
    <link rel='stylesheet' href='css/style.css'>
</head>
<body class='login-page'>
    <div class='login-container' style='max-width: 800px;'>
        <div class='login-header'>
            <div class='logo'>👑 MiamiSchool</div>
            <h1>Test du Compte Administrateur</h1>
        </div>

<?php
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Rechercher le compte admin
    $stmt = $pdo->query("SELECT * FROM utilisateurs WHERE type_compte = 'admin'");
    $admins = $stmt->fetchAll();
    
    if (count($admins) == 0) {
        echo "<div class='alert alert-error'>
                ❌ AUCUN compte administrateur trouvé dans la base !<br><br>
                Vous devez créer le compte admin.
              </div>";
        
        echo "<form method='POST'>
                <button type='submit' name='creer_admin' class='btn btn-primary btn-block'>
                    ➕ Créer le Compte Admin Maintenant
                </button>
              </form>";
        
        if (isset($_POST['creer_admin'])) {
            $hash = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, type_compte) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute(['Admin', 'Principal', 'admin@miamischool.com', $hash, 'admin']);
            
            echo "<div class='alert alert-success' style='margin-top:20px;'>
                    ✅ Compte admin créé avec succès !
                  </div>
                  <meta http-equiv='refresh' content='2'>";
        }
        
    } else {
        echo "<div class='card'>
                <div class='card-header'><h2>📋 Compte(s) Admin Trouvé(s)</h2></div>
                <div class='card-body'>";
        
        foreach ($admins as $admin) {
            echo "<div style='background: var(--background-dark); padding: 20px; border-radius: 10px; margin-bottom: 20px;'>";
            echo "<h3 style='color: var(--primary-color); margin-bottom: 15px;'>Compte #{$admin['id']}</h3>";
            echo "<table style='width: 100%;'>
                    <tr>
                        <td style='padding: 8px;'><strong>Nom complet :</strong></td>
                        <td style='padding: 8px;'>{$admin['prenom']} {$admin['nom']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px;'><strong>Email :</strong></td>
                        <td style='padding: 8px;'>
                            <code style='background: #2a2412; padding: 8px 15px; border-radius: 5px; font-size: 16px; color: #4caf50;'>
                                {$admin['email']}
                            </code>
                        </td>
                    </tr>
                    <tr>
                        <td style='padding: 8px;'><strong>Type :</strong></td>
                        <td style='padding: 8px;'><span class='status-badge status-annule'>{$admin['type_compte']}</span></td>
                    </tr>
                    <tr>
                        <td style='padding: 8px;'><strong>Classe :</strong></td>
                        <td style='padding: 8px;'>" . ($admin['classe'] ?? '<span style="color: #4caf50;">✓ NULL (correct pour admin)</span>') . "</td>
                    </tr>
                  </table>";
            
            // Test du mot de passe
            echo "<hr style='margin: 20px 0; border-color: var(--border-color);'>";
            echo "<h3 style='color: var(--primary-color); margin-bottom: 15px;'>🔐 Test du Mot de Passe</h3>";
            
            $test_passwords = ['admin123', 'Admin123', 'ADMIN123', 'admin', 'test123'];
            $password_ok = false;
            
            foreach ($test_passwords as $test_pwd) {
                if (password_verify($test_pwd, $admin['mot_de_passe'])) {
                    echo "<div class='alert alert-success'>
                            ✅ <strong>Mot de passe trouvé !</strong><br>
                            Le mot de passe correct est : <code style='background: #2a2412; padding: 8px 15px; border-radius: 5px; font-size: 16px; color: #4caf50;'>{$test_pwd}</code>
                          </div>";
                    $password_ok = true;
                    $correct_password = $test_pwd;
                    break;
                }
            }
            
            if (!$password_ok) {
                echo "<div class='alert alert-error'>
                        ❌ Aucun des mots de passe testés ne fonctionne !<br><br>
                        Le mot de passe dans la base est peut-être corrompu.
                      </div>";
                
                echo "<form method='POST'>
                        <input type='hidden' name='admin_id' value='{$admin['id']}'>
                        <button type='submit' name='reset_password' class='btn btn-danger'>
                            🔄 Réinitialiser le mot de passe à 'admin123'
                        </button>
                      </form>";
            }
            
            echo "</div>";
        }
        
        echo "  </div>
              </div>";
        
        // Instructions de connexion
        if (isset($correct_password)) {
            echo "<div class='card'>
                    <div class='card-header'><h2>✅ Instructions de Connexion</h2></div>
                    <div class='card-body'>
                        <div class='alert alert-success'>
                            <strong>Utilisez EXACTEMENT ces informations :</strong>
                        </div>
                        
                        <div style='background: var(--background-dark); padding: 25px; border-radius: 10px; margin-top: 20px;'>
                            <table style='width: 100%; font-size: 16px;'>
                                <tr>
                                    <td style='padding: 12px;'><strong>1. Type de compte :</strong></td>
                                    <td style='padding: 12px;'>
                                        <span class='status-badge status-annule' style='font-size: 14px;'>Administrateur</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style='padding: 12px;'><strong>2. Email :</strong></td>
                                    <td style='padding: 12px;'>
                                        <code style='background: #2a2412; padding: 10px 20px; border-radius: 5px; font-size: 16px; color: #4caf50;'>
                                            {$admin['email']}
                                        </code>
                                    </td>
                                </tr>
                                <tr>
                                    <td style='padding: 12px;'><strong>3. Mot de passe :</strong></td>
                                    <td style='padding: 12px;'>
                                        <code style='background: #2a2412; padding: 10px 20px; border-radius: 5px; font-size: 16px; color: #4caf50;'>
                                            {$correct_password}
                                        </code>
                                    </td>
                                </tr>
                                <tr>
                                    <td style='padding: 12px;'><strong>4. Classe :</strong></td>
                                    <td style='padding: 12px;'>
                                        <span style='color: #ff9800; font-weight: bold;'>⚠️ NE PAS SÉLECTIONNER</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class='alert alert-warning' style='margin-top: 20px;'>
                            <strong>⚠️ ATTENTION :</strong><br>
                            • Copiez-collez l'email pour éviter les fautes de frappe<br>
                            • Le mot de passe est sensible à la casse<br>
                            • Ne sélectionnez PAS de classe pour l'admin<br>
                            • Le champ classe ne doit même pas apparaître
                        </div>
                        
                        <div style='text-align: center; margin-top: 30px;'>
                            <a href='index.php' class='btn btn-primary btn-block' style='font-size: 18px; padding: 15px;'>
                                🔐 Aller à la Page de Connexion
                            </a>
                        </div>
                    </div>
                  </div>";
        }
        
        // Bouton copier email
        echo "<script>
                function copyEmail() {
                    const email = '{$admin['email']}';
                    navigator.clipboard.writeText(email).then(() => {
                        alert('✅ Email copié : ' + email);
                    });
                }
                
                function copyPassword() {
                    const pwd = '{$correct_password}';
                    navigator.clipboard.writeText(pwd).then(() => {
                        alert('✅ Mot de passe copié : ' + pwd);
                    });
                }
              </script>";
    }
    
    // Réinitialiser le mot de passe si demandé
    if (isset($_POST['reset_password'])) {
        $admin_id = $_POST['admin_id'];
        $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?");
        $stmt->execute([$new_hash, $admin_id]);
        
        echo "<div class='alert alert-success' style='margin-top: 20px;'>
                ✅ Mot de passe réinitialisé à 'admin123' !
              </div>
              <meta http-equiv='refresh' content='2'>";
    }
    
} catch (PDOException $e) {
    echo "<div class='alert alert-error'>
            <strong>❌ Erreur :</strong><br>
            {$e->getMessage()}
          </div>";
}
?>

        <div class='alert alert-warning' style='margin-top: 20px;'>
            💡 <strong>Email EXACT :</strong> admin@miami<strong>s</strong>chool.com (avec un 's' avant 'chool')<br>
            ❌ <strong>PAS :</strong> admin@miami<strong>sh</strong>cool.com
        </div>
    </div>
</body>
</html>