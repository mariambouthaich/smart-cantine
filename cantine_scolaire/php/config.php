<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'cantine_scolaire');

// Connexion à la base de données
class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }
}

// Démarrer la session
session_start();

// Fonction pour vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fonction pour vérifier le type de compte
function getUserType() {
    return $_SESSION['user_type'] ?? null;
}

// Fonction pour rediriger selon le type de compte
function redirectToDashboard() {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
    
    $type = getUserType();
    if ($type === 'admin') {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: eleve_dashboard.php');
    }
    exit;
}

// Fonction pour formater le prix
function formatPrice($price) {
    return number_format($price, 2, '.', ' ') . ' DH';
}

// Fonction pour formater la date
function formatDate($date) {
    setlocale(LC_TIME, 'fr_FR.UTF-8', 'fra');
    return strftime('%d/%m/%Y', strtotime($date));
}
?>
