<?php
session_start();
require_once 'config/database.php';

// Set JSON header immediately
header('Content-Type: application/json');

// Start output buffering for clean JSON response
ob_start();
ob_clean();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Admin not logged in']);
    exit();
}

// Handle admin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $loan_id = $_POST['loan_id'] ?? null;
        $action = $_POST['action'] ?? null;
        
        if (!$loan_id || !$action) {
            echo json_encode(['success' => false, 'error' => 'Missing loan_id or action']);
            exit();
        }
        
        if ($action === 'approve') {
            $stmt = $pdo->prepare("UPDATE loan_applications SET admin_action = 'approved', status = 'approved' WHERE id = ?");
            $stmt->execute([$loan_id]);
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("UPDATE loan_applications SET admin_action = 'rejected', status = 'rejected' WHERE id = ?");
            $stmt->execute([$loan_id]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
            exit();
        }
        
        // Return success response
        echo json_encode(['success' => true, 'message' => 'Loan ' . $action . 'd successfully']);
        exit();
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit();
    }
} else {
    // Handle GET request - show API information
    echo json_encode([
        'success' => false, 
        'error' => 'Only POST method allowed',
        'info' => 'This is an API endpoint for admin actions. Use POST method with loan_id and action parameters.',
        'required_parameters' => [
            'loan_id' => 'ID of the loan to approve/reject',
            'action' => 'approve or reject'
        ],
        'session_status' => isset($_SESSION['admin_logged_in']) ? 'Admin logged in' : 'Admin not logged in'
    ]);
    exit();
}
?>