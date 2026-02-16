<?php
require_once 'php/config.php';

if (isLoggedIn()) {
    redirectToDashboard();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $type_compte = $_POST['type_compte'] ?? '';
    $classe = $_POST['classe'] ?? '';

    // Validation
    if (empty($nom) || empty($prenom) || empty($email) || empty($password) || empty($type_compte)) {
        $error = 'Tous les champs sont obligatoires';
    } elseif ($type_compte !== 'admin' && empty($classe)) {
        $error = 'Veuillez sélectionner votre classe';
    } elseif ($password !== $confirm_password) {
        $error = 'Les mots de passe ne correspondent pas';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères';
    } else {
        $db = Database::getInstance()->getConnection();
        
        // Vérifier si l'email existe déjà
        $stmt = $db->prepare("SELECT id FROM utilisateurs WHERE email = :email");
        $stmt->execute([':email' => $email]);
        
        if ($stmt->fetch()) {
            $error = 'Cet email est déjà utilisé';
        } else {
            // Insérer le nouvel utilisateur
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $query = "INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, type_compte";
            $params = [
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':email' => $email,
                ':mot_de_passe' => $hashed_password,
                ':type_compte' => $type_compte
            ];
            
            if ($type_compte !== 'admin') {
                $query .= ", classe";
                $params[':classe'] = $classe;
            }
            
            $query .= ") VALUES (:nom, :prenom, :email, :mot_de_passe, :type_compte";
            
            if ($type_compte !== 'admin') {
                $query .= ", :classe";
            }
            
            $query .= ")";
            
            $stmt = $db->prepare($query);
            
            if ($stmt->execute($params)) {
                $success = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';
            } else {
                $error = 'Erreur lors de l\'inscription';
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
    <title>SmartCantine - Inscription</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-header">
            <div class="logo"> SmartCantine</div>
            <h1>Inscription</h1>
            <p>Créez votre compte</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
                <a href="index.php">Se connecter</a>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="login-form" id="inscriptionForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" name="nom" id="nom" required value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" name="prenom" id="prenom" required value="<?php echo htmlspecialchars($_POST['prenom'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="type_compte">Type de compte</label>
                <select name="type_compte" id="type_compte" required>
                    <option value="">Sélectionner...</option>
                    <option value="parent" <?php echo ($_POST['type_compte'] ?? '') === 'parent' ? 'selected' : ''; ?>>Parent</option>
                    <option value="eleve" <?php echo ($_POST['type_compte'] ?? '') === 'eleve' ? 'selected' : ''; ?>>Élève</option>
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
                <input type="email" name="email" id="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" name="password" id="password" required>
                <small>Au moins 6 caractères</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">S'inscrire</button>
        </form>

        <div class="login-footer">
            <p>Vous avez déjà un compte ? <a href="index.php">Se connecter</a></p>
        </div>
    </div>

    <script src="js/inscription.js"></script>
</body>
</html>
