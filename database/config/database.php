<?php
// Database configuration
$host = 'localhost';
$dbname = 'addmuiud_bkash_loan';
$username = 'addmuiud_bkash_loana';
$password = 'addmuiud_bkash_loan';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // For API requests (admin_update.php or AJAX requests), return JSON error instead of HTML
    $isApiRequest = (
        (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
        (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) ||
        (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') ||
        (basename($_SERVER['PHP_SELF']) === 'admin_update.php')
    );
    
    if ($isApiRequest) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]);
        exit;
    }
    // For regular page requests, show HTML error
    die("Connection failed: " . $e->getMessage());
}
?>