
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

// Clear session after completion
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Loan Rejected - bKash</title>
  <link rel="icon" type="image/png" href="img/favicon.png">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-red-50 to-red-100 min-h-screen flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl shadow-2xl p-8 w-full max-w-md text-center">
    <!-- Error Icon -->
    <div class="mb-6">
      <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </div>
      <h1 class="text-2xl font-bold text-gray-800 mb-2">ঋণ প্রত্যাখ্যাত</h1>
      <p class="text-gray-600">দুঃখিত, আপনার ঋণের আবেদন প্রত্যাখ্যাত হয়েছে।</p>
    </div>

    <!-- Loan Details -->
    <div class="bg-gray-50 rounded-2xl p-6 mb-6">
      <h2 class="text-lg font-semibold text-gray-800 mb-4">আবেদনের বিবরণ</h2>
      <div class="space-y-3">
        <div class="flex justify-between">
          <span class="text-gray-600">পরিমাণ:</span>
          <span class="font-semibold text-red-600">৳<?php echo number_format($loan_amount); ?></span>
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

    <!-- Reasons -->
    <div class="text-left mb-6">
      <h3 class="font-semibold text-gray-800 mb-2">সম্ভাব্য কারণসমূহ:</h3>
      <ul class="text-sm text-gray-600 space-y-1">
        <li>• অপর্যাপ্ত ক্রেডিট স্কোর</li>
        <li>• আয়ের প্রমাণ অসম্পূর্ণ</li>
        <li>• পূর্ববর্তী ঋণের বকেয়া</li>
        <li>• নীতিমালা অনুযায়ী অযোগ্য</li>
      </ul>
    </div>

    <!-- Next Steps -->
    <div class="text-left mb-6">
      <h3 class="font-semibold text-gray-800 mb-2">পরবর্তী করণীয়:</h3>
      <ul class="text-sm text-gray-600 space-y-1">
        <li>• ৩০ দিন পর পুনরায় আবেদন করুন</li>
        <li>• প্রয়োজনীয় কাগজপত্র সংগ্রহ করুন</li>
        <li>• গ্রাহক সেবায় যোগাযোগ করুন</li>
      </ul>
    </div>

    <!-- Action Buttons -->
    <div class="space-y-3">
      <button onclick="window.location.href='index.html'" 
              class="w-full bg-pink-600 text-white py-3 px-6 rounded-2xl font-semibold hover:bg-pink-700 transition-colors">
        হোম পেজে ফিরে যান
      </button>
      <button onclick="window.location.href='lon_request.php'" 
              class="w-full bg-gray-200 text-gray-700 py-3 px-6 rounded-2xl font-semibold hover:bg-gray-300 transition-colors">
        নতুন আবেদন করুন
      </button>
    </div>
  </div>
</body>
</html>