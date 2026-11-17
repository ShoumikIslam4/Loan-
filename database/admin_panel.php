<?php
session_start();
require_once 'config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

// Handle POST requests for approve/reject actions (like debug_admin_actions.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loan_id = $_POST['loan_id'] ?? null;
    $action = $_POST['action'] ?? null;
    
    if ($loan_id && $action) {
        try {
            if ($action === 'approve') {
                $stmt = $pdo->prepare("UPDATE loan_applications SET admin_action = 'approved', status = 'approved' WHERE id = ?");
            } else {
                $stmt = $pdo->prepare("UPDATE loan_applications SET admin_action = 'rejected', status = 'rejected' WHERE id = ?");
            }
            
            $result = $stmt->execute([$loan_id]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Update Successful']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Update Failed']);
            }
            exit();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            exit();
        }
    }
}

// Get all loan applications
$stmt = $pdo->query("SELECT * FROM loan_applications ORDER BY created_at DESC");
$loans = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - bKash Loan Management</title>
    <link rel="icon" type="image/png" href="img/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'bkash': '#E2136E',
                        'bkash-light': '#F8BBD9',
                        'bkash-dark': '#B8104E'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-lg border-b-4 border-bkash">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-bkash rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">bKash Admin Panel</h1>
                        <p class="text-sm text-gray-600">Loan Management System</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="hidden md:flex items-center space-x-2">
                        <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-sm text-gray-600">Live Updates</span>
                    </div>
                    <button onclick="logout()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors">
                        Logout
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Debug Panel -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <h3 class="text-sm font-medium text-yellow-800 mb-2">Debug Information</h3>
            <div class="text-xs text-yellow-700">
                <p><strong>Admin Session:</strong> <?php echo isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] ? 'Active' : 'Not Active'; ?></p>
                <p><strong>Admin ID:</strong> <?php echo $_SESSION['admin_id'] ?? 'Not Set'; ?></p>
                <p><strong>Admin Username:</strong> <?php echo $_SESSION['admin_username'] ?? 'Not Set'; ?></p>
                <?php if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']): ?>
                <p class="mt-2"><a href="set_admin_session.php" class="text-blue-600 hover:text-blue-800 underline">Set Admin Session</a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Applications</p>
                        <p class="text-3xl font-bold text-gray-900" id="total-count">0</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Pending</p>
                        <p class="text-3xl font-bold text-yellow-600" id="pending-count">0</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Approved</p>
                        <p class="text-3xl font-bold text-green-600" id="approved-count">0</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Rejected</p>
                        <p class="text-3xl font-bold text-red-600" id="rejected-count">0</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                <h2 class="text-xl font-bold text-gray-900">Loan Applications</h2>
                <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
                    <select id="status-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bkash focus:border-transparent">
                        <option value="all">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <button onclick="refreshData()" class="bg-bkash hover:bg-bkash-dark text-white px-6 py-2 rounded-lg transition-colors flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span>Refresh</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Loan Applications Table -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mobile</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">OTP</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PIN</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="loans-table-body" class="bg-white divide-y divide-gray-200">
                        <!-- Dynamic content will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        // Debug mode - log all important information
        console.log('Admin Panel Debug Info:');
        console.log('Current URL:', window.location.href);
        console.log('Base URL:', window.location.origin);
        console.log('Admin Update URL:', window.location.origin + '/admin_update.php?action=get_all');
        
        // Test if admin_update.php is accessible
        fetch('admin_update.php?action=get_all')
            .then(response => {
                console.log('Initial API Test - Status:', response.status);
                console.log('Initial API Test - OK:', response.ok);
                return response.text();
            })
            .then(text => {
                console.log('Initial API Test - Response:', text.substring(0, 200) + (text.length > 200 ? '...' : ''));
                try {
                    const data = JSON.parse(text);
                    console.log('Initial API Test - Parsed:', data);
                } catch (e) {
                    console.error('Initial API Test - Parse Error:', e.message);
                }
            })
            .catch(error => {
                console.error('Initial API Test - Error:', error);
            });
    </script>
    <script src="script.js"></script>
    <!-- Notification Container -->
    <div id="notification-container" class="fixed top-4 right-4 z-50"></div>

    <style>
        .notification {
            padding: 12px 16px;
            margin-bottom: 8px;
            border-radius: 6px;
            color: white;
            font-weight: 500;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out;
            max-width: 300px;
        }
        
        .notification.show {
            transform: translateX(0);
        }
        
        .notification.success {
            background-color: #10b981;
        }
        
        .notification.error {
            background-color: #ef4444;
        }
        
        .notification.info {
            background-color: #3b82f6;
        }
    </style>

</body>
</html>