<?php
require_once 'php/config.php';

// Si déjà connecté, rediriger vers le dashboard approprié
if (isLoggedIn()) {
    redirectToDashboard();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $type_compte = $_POST['type_compte'] ?? '';
    $classe = $_POST['classe'] ?? '';

    if (empty($email) || empty($password) || empty($type_compte)) {
        $error = 'Veuillez remplir tous les champs';
    } else {
        $db = Database::getInstance()->getConnection();
        
        $query = "SELECT * FROM utilisateurs WHERE email = :email AND type_compte = :type_compte";
        $params = [':email' => $email, ':type_compte' => $type_compte];
        
        // Si pas admin, vérifier aussi la classe
        if ($type_compte !== 'admin') {
            if (empty($classe)) {
                $error = 'Veuillez sélectionner votre classe';
            } else {
                $query .= " AND classe = :classe";
                $params[':classe'] = $classe;
            }
        }
        
        if (empty($error)) {
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['mot_de_passe'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_type'] = $user['type_compte'];
                $_SESSION['user_nom'] = $user['prenom'] . ' ' . $user['nom'];
                $_SESSION['user_classe'] = $user['classe'];
                $_SESSION['user_solde'] = $user['solde'];
                
                redirectToDashboard();
            } else {
                $error = 'Email, mot de passe, type de compte ou classe incorrect';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCantine - Connexion Cantine</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-header">
            <div class="logo"> SmartCantine</div>
            <h1>Cantine Scolaire</h1>
            <p>Connectez-vous à votre compte</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="login-form" id="loginForm">
            <div class="form-group">
                <label for="type_compte">Type de compte</label>
                <select name="type_compte" id="type_compte" required>
                    <option value="">Sélectionner...</option>
                    <option value="admin">Administrateur</option>
                    <option value="parent">Parent</option>
                    <option value="eleve">Élève</option>
                </select>
            </div>

            <div class="form-group" id="classeGroup" style="display: none;">
                <label for="classe">Classe</label>
                <select name="classe" id="classe">
                    <option value="">Sélectionner votre classe...</option>
                    <option value="1ere">1ère Année</option>
                    <option value="2eme">2ème Année</option>
                    <option value="3eme">3ème Année</option>
                    <option value="4eme">4ème Année</option>
                    <option value="5eme">5ème Année</option>
                    <option value="6eme">6ème Année</option>
                </select>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" name="password" id="password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
        </form>

        <div class="login-footer">
            <p>Vous n'avez pas de compte ? <a href="inscription.php">S'inscrire</a></p>
        </div>
    </div>

    <script src="js/login.js"></script>
</body>
</html>
