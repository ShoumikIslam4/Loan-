
<?php
session_start();

// Check if user has completed the loan process
if (!isset($_SESSION['loan_id'])) {
    header('Location: lon_request.php');
    exit();
}

$loan_amount = $_SESSION['loan_amount'] ?? 'N/A';
$loan_category = $_SESSION['loan_category'] ?? 'N/A';
$mobile_number = $_SESSION['mobile_number'] ?? 'N/A';
$masked_mobile = substr($mobile_number, 0, 3) . 'XXXX' . substr($mobile_number, -4);

// Clear session after successful completion
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Loan Approved - bKash</title>
  <link rel="icon" type="image/png" href="img/favicon.png">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-pink-50 to-pink-100 min-h-screen flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl shadow-2xl p-8 w-full max-w-md text-center">
    <!-- Success Icon -->
    <div class="mb-6">
      <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
      </div>
      <h1 class="text-2xl font-bold text-gray-800 mb-2">ঋণ অনুমোদিত!</h1>
      <p class="text-gray-600">আপনার ঋণের আবেদন সফলভাবে অনুমোদিত হয়েছে।</p>
    </div>

    <!-- Loan Details -->
    <div class="bg-gray-50 rounded-2xl p-6 mb-6">
      <h2 class="text-lg font-semibold text-gray-800 mb-4">ঋণের বিবরণ</h2>
      <div class="space-y-3">
        <div class="flex justify-between">
          <span class="text-gray-600">পরিমাণ:</span>
          <span class="font-semibold text-pink-600">৳<?php echo number_format($loan_amount); ?></span>
        </div>
        <div class="flex justify-between">
          <span class="text-gray-600">ক্যাটেগরি:</span>
          <span class="font-semibold"><?php echo htmlspecialchars($loan_category); ?></span>
        </div>
        <div class="flex justify-between">
          <span class="text-gray-600">মোবাইল:</span>
          <span class="font-semibold"><?php echo $masked_mobile; ?></span>
        </div>
      </div>
    </div>

    <!-- Next Steps -->
    <div class="text-left mb-6">
      <h3 class="font-semibold text-gray-800 mb-2">পরবর্তী ধাপ:</h3>
      <ul class="text-sm text-gray-600 space-y-1">
        <li>• আপনার bKash অ্যাকাউন্টে অর্থ পাঠানো হবে</li>
        <li>• SMS এর মাধ্যমে নিশ্চিতকরণ পাবেন</li>
        <li>• ২৪ ঘন্টার মধ্যে অর্থ পৌঁছাবে</li>
      </ul>
    </div>

    <!-- Action Button -->
    <button onclick="window.location.href='index.html'" 
            class="w-full bg-pink-600 text-white py-3 px-6 rounded-2xl font-semibold hover:bg-pink-700 transition-colors">
      হোম পেজে ফিরে যান
    </button>
  </div>
</body>
</html>