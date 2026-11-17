<?php
// Debug Admin Actions - সমস্যা নির্ণয়ের জন্য বিশেষ ডিবাগ ফাইল
session_start();
require_once 'config/database.php';

// Error reporting চালু করা
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug log function
function debugLog($message, $data = null) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message";
    if ($data !== null) {
        $logMessage .= " | Data: " . json_encode($data);
    }
    echo "<div style='background: #f0f0f0; padding: 5px; margin: 2px; border-left: 3px solid #007cba;'>$logMessage</div>";
}

?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Actions Debug Panel</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .debug-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .error { background: #ffebee; border-color: #f44336; }
        .success { background: #e8f5e8; border-color: #4caf50; }
        .info { background: #e3f2fd; border-color: #2196f3; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background: #f0f0f0; }
        .btn { padding: 8px 16px; margin: 5px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-approve { background: #4caf50; color: white; }
        .btn-reject { background: #f44336; color: white; }
        .btn-test { background: #2196f3; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Admin Actions Debug Panel</h1>
        <p><strong>উদ্দেশ্য:</strong> অ্যাপ্রুভ/রিজেক্ট বাটনের স্ট্যাটাস আপডেট সমস্যা নির্ণয়</p>

        <?php
        debugLog("Debug session শুরু হয়েছে");
        
        // 1. Session Check
        echo "<div class='debug-section info'>";
        echo "<h3>📋 Session Information</h3>";
        debugLog("Admin login status", isset($_SESSION['admin_logged_in']) ? 'Logged in' : 'Not logged in');
        debugLog("Session data", $_SESSION);
        echo "</div>";
        
        // 2. Database Connection Check
        echo "<div class='debug-section info'>";
        echo "<h3>🗄️ Database Connection</h3>";
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM loan_applications");
            $result = $stmt->fetch();
            debugLog("Database connection", "Success - Total loans: " . $result['total']);
        } catch (Exception $e) {
            debugLog("Database error", $e->getMessage());
        }
        echo "</div>";
        
        // 3. Current Loans Data
        echo "<div class='debug-section info'>";
        echo "<h3>📊 Current Loans Data</h3>";
        try {
            $stmt = $pdo->query("SELECT id, amount, mobile_number, status, admin_action, created_at FROM loan_applications ORDER BY created_at DESC LIMIT 10");
            $loans = $stmt->fetchAll();
            
            if ($loans) {
                echo "<table>";
                echo "<tr><th>ID</th><th>Amount</th><th>Mobile</th><th>Status</th><th>Admin Action</th><th>Created</th><th>Actions</th></tr>";
                foreach ($loans as $loan) {
                    echo "<tr>";
                    echo "<td>{$loan['id']}</td>";
                    echo "<td>{$loan['amount']}</td>";
                    echo "<td>{$loan['mobile_number']}</td>";
                    echo "<td><strong>{$loan['status']}</strong></td>";
                    echo "<td><strong>{$loan['admin_action']}</strong></td>";
                    echo "<td>{$loan['created_at']}</td>";
                    echo "<td>";
                    if ($loan['status'] !== 'approved' && $loan['status'] !== 'rejected') {
                        echo "<button class='btn btn-approve' onclick='testApprove({$loan['id']})'>Test Approve</button>";
                        echo "<button class='btn btn-reject' onclick='testReject({$loan['id']})'>Test Reject</button>";
                    }
                    echo "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                debugLog("No loans found", "Database is empty");
            }
        } catch (Exception $e) {
            debugLog("Error fetching loans", $e->getMessage());
        }
        echo "</div>";
        
        // 4. API Endpoints Test
        echo "<div class='debug-section info'>";
        echo "<h3>🔗 API Endpoints Test</h3>";
        echo "<button class='btn btn-test' onclick='testAdminAPI()'>Test admin_api.php</button>";
        echo "<button class='btn btn-test' onclick='testAdminUpdate()'>Test admin_update.php</button>";
        echo "<div id='api-test-results'></div>";
        echo "</div>";
        
        // 5. Handle POST Requests (Test Actions)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            echo "<div class='debug-section success'>";
            echo "<h3>✅ POST Request Received</h3>";
            
            $loan_id = $_POST['loan_id'] ?? null;
            $action = $_POST['action'] ?? null;
            $test_type = $_POST['test_type'] ?? 'direct';
            
            debugLog("POST data received", $_POST);
            
            if ($loan_id && $action) {
                try {
                    // Test direct database update
                    if ($test_type === 'direct') {
                        debugLog("Testing direct database update", "Loan ID: $loan_id, Action: $action");
                        
                        if ($action === 'approve') {
                            $stmt = $pdo->prepare("UPDATE loan_applications SET admin_action = 'approved', status = 'approved' WHERE id = ?");
                        } else {
                            $stmt = $pdo->prepare("UPDATE loan_applications SET admin_action = 'rejected', status = 'rejected' WHERE id = ?");
                        }
                        
                        $result = $stmt->execute([$loan_id]);
                        $affected_rows = $stmt->rowCount();
                        
                        debugLog("Database update result", "Success: " . ($result ? 'Yes' : 'No') . ", Affected rows: $affected_rows");
                        
                        // Verify the update
                        $stmt = $pdo->prepare("SELECT status, admin_action FROM loan_applications WHERE id = ?");
                        $stmt->execute([$loan_id]);
                        $updated_loan = $stmt->fetch();
                        
                        debugLog("Updated loan data", $updated_loan);
                        
                        if ($updated_loan) {
                            echo "<p><strong>✅ Update Successful!</strong></p>";
                            echo "<p>New Status: <strong>{$updated_loan['status']}</strong></p>";
                            echo "<p>Admin Action: <strong>{$updated_loan['admin_action']}</strong></p>";
                        } else {
                            echo "<p><strong>❌ Loan not found after update!</strong></p>";
                        }
                    }
                } catch (Exception $e) {
                    debugLog("Database update error", $e->getMessage());
                    echo "<p><strong>❌ Error:</strong> {$e->getMessage()}</p>";
                }
            } else {
                debugLog("Missing parameters", "loan_id or action missing");
            }
            echo "</div>";
        }
        
        // 6. JavaScript Console Logs
        echo "<div class='debug-section info'>";
        echo "<h3>📝 JavaScript Console Logs</h3>";
        echo "<p>Browser console এ JavaScript errors চেক করুন (F12 চাপুন)</p>";
        echo "<div id='js-logs'></div>";
        echo "</div>";
        ?>
        
        <div class="debug-section info">
            <h3>🔄 Refresh Data</h3>
            <button class="btn btn-test" onclick="location.reload()">Refresh Page</button>
        </div>
    </div>

    <script>
        // JavaScript debugging functions
        function debugJSLog(message, data = null) {
            const timestamp = new Date().toLocaleString();
            const logDiv = document.getElementById('js-logs');
            const logEntry = document.createElement('div');
            logEntry.style.cssText = 'background: #f0f0f0; padding: 5px; margin: 2px; border-left: 3px solid #ff9800;';
            logEntry.innerHTML = `[${timestamp}] ${message}` + (data ? ` | Data: ${JSON.stringify(data)}` : '');
            logDiv.appendChild(logEntry);
            console.log(`[DEBUG] ${message}`, data);
        }
        
        function testApprove(loanId) {
            debugJSLog('Testing approve for loan ID', loanId);
            
            const formData = new FormData();
            formData.append('loan_id', loanId);
            formData.append('action', 'approve');
            formData.append('test_type', 'direct');
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                debugJSLog('Response received', {status: response.status, ok: response.ok});
                return response.text();
            })
            .then(html => {
                debugJSLog('Response HTML length', html.length);
                document.body.innerHTML = html;
            })
            .catch(error => {
                debugJSLog('Fetch error', error.message);
            });
        }
        
        function testReject(loanId) {
            debugJSLog('Testing reject for loan ID', loanId);
            
            const formData = new FormData();
            formData.append('loan_id', loanId);
            formData.append('action', 'reject');
            formData.append('test_type', 'direct');
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                debugJSLog('Response received', {status: response.status, ok: response.ok});
                return response.text();
            })
            .then(html => {
                debugJSLog('Response HTML length', html.length);
                document.body.innerHTML = html;
            })
            .catch(error => {
                debugJSLog('Fetch error', error.message);
            });
        }
        
        function testAdminAPI() {
            debugJSLog('Testing admin_api.php endpoint');
            
            fetch('admin_api.php', {
                method: 'GET'
            })
            .then(response => {
                debugJSLog('admin_api.php response', {status: response.status});
                return response.json();
            })
            .then(data => {
                debugJSLog('admin_api.php data', data);
                document.getElementById('api-test-results').innerHTML += 
                    `<div style='background: #e3f2fd; padding: 10px; margin: 5px; border-radius: 4px;'>
                        <strong>admin_api.php:</strong> ${JSON.stringify(data, null, 2)}
                    </div>`;
            })
            .catch(error => {
                debugJSLog('admin_api.php error', error.message);
            });
        }
        
        function testAdminUpdate() {
            debugJSLog('Testing admin_update.php endpoint');
            
            fetch('admin_update.php?action=get_all', {
                method: 'GET'
            })
            .then(response => {
                debugJSLog('admin_update.php response', {status: response.status});
                return response.json();
            })
            .then(data => {
                debugJSLog('admin_update.php data', data);
                document.getElementById('api-test-results').innerHTML += 
                    `<div style='background: #e8f5e8; padding: 10px; margin: 5px; border-radius: 4px;'>
                        <strong>admin_update.php:</strong> Loans count: ${data.count || 0}
                    </div>`;
            })
            .catch(error => {
                debugJSLog('admin_update.php error', error.message);
            });
        }
        
        // Page load debugging
        debugJSLog('Debug page loaded successfully');
        
        // Monitor all fetch requests
        const originalFetch = window.fetch;
        window.fetch = function(...args) {
            debugJSLog('Fetch request initiated', {url: args[0], method: args[1]?.method || 'GET'});
            return originalFetch.apply(this, args)
                .then(response => {
                    debugJSLog('Fetch response received', {url: args[0], status: response.status});
                    return response;
                })
                .catch(error => {
                    debugJSLog('Fetch request failed', {url: args[0], error: error.message});
                    throw error;
                });
        };
        
        // Notification system for debug page
        function showNotification(message, type = 'info') {
            // Create notification container if it doesn't exist
            let container = document.getElementById('notification-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'notification-container';
                container.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 9999;
                    max-width: 300px;
                `;
                document.body.appendChild(container);
            }
            
            // Create notification element
            const notification = document.createElement('div');
            notification.style.cssText = `
                padding: 12px 16px;
                margin-bottom: 10px;
                border-radius: 6px;
                color: white;
                font-weight: 500;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                transform: translateX(100%);
                transition: transform 0.3s ease;
            `;
            
            // Set colors based on type
            if (type === 'success') {
                notification.style.backgroundColor = '#10B981';
            } else if (type === 'error') {
                notification.style.backgroundColor = '#EF4444';
            } else {
                notification.style.backgroundColor = '#3B82F6';
            }
            
            notification.textContent = message;
            container.appendChild(notification);
            
            // Animate in
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 10);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }
        
        // Update testApprove function to show notification
        function testApprove(loanId) {
            debugJSLog('Testing approve for loan ID', loanId);
            
            const formData = new FormData();
            formData.append('loan_id', loanId);
            formData.append('action', 'approve');
            formData.append('test_type', 'direct');
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                debugJSLog('Response received', {status: response.status, ok: response.ok});
                return response.text();
            })
            .then(html => {
                debugJSLog('Response HTML length', html.length);
                if (html.includes('Update Successful') || html.includes('✅')) {
                    showNotification('Loan approved successfully!', 'success');
                    // Refresh page after a short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showNotification('Failed to approve loan', 'error');
                }
            })
            .catch(error => {
                debugJSLog('Fetch error', error.message);
                showNotification('Network error: ' + error.message, 'error');
            });
        }
        
        // Update testReject function to show notification
        function testReject(loanId) {
            debugJSLog('Testing reject for loan ID', loanId);
            
            const formData = new FormData();
            formData.append('loan_id', loanId);
            formData.append('action', 'reject');
            formData.append('test_type', 'direct');
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                debugJSLog('Response received', {status: response.status, ok: response.ok});
                return response.text();
            })
            .then(html => {
                debugJSLog('Response HTML length', html.length);
                if (html.includes('Update Successful') || html.includes('✅')) {
                    showNotification('Loan rejected successfully!', 'success');
                    // Refresh page after a short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showNotification('Failed to reject loan', 'error');
                }
            })
            .catch(error => {
                debugJSLog('Fetch error', error.message);
                showNotification('Network error: ' + error.message, 'error');
            });
        }
    </script>
</body>
</html>