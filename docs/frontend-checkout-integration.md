# Frontend Integration Example untuk Midtrans Checkout

## Overview
Contoh implementasi frontend untuk checkout dengan Midtrans integration yang telah diupdate di CheckoutController.

## HTML Form Structure

### Checkout Form
```html
<form id="checkoutForm" action="{{ route('checkout.place') }}" method="POST">
    @csrf
    
    <!-- Shipping Address -->
    <div class="shipping-section">
        <h3>Alamat Pengiriman</h3>
        <div class="grid grid-cols-2 gap-4">
            <input type="text" name="first_name" placeholder="Nama Depan" required>
            <input type="text" name="last_name" placeholder="Nama Belakang" required>
        </div>
        <input type="tel" name="phone" placeholder="Nomor Telepon" required>
        <textarea name="address" placeholder="Alamat Lengkap" required></textarea>
        <div class="grid grid-cols-3 gap-4">
            <input type="text" name="city" placeholder="Kota" required>
            <input type="text" name="province" placeholder="Provinsi" required>
            <input type="text" name="zip" placeholder="Kode Pos" required>
        </div>
    </div>

    <!-- Payment Method Selection -->
    <div class="payment-section">
        <h3>Metode Pembayaran</h3>
        <div class="payment-methods">
            <label class="payment-option">
                <input type="radio" name="payment_method" value="cod" checked>
                <span>Cash on Delivery (COD)</span>
            </label>
            <label class="payment-option">
                <input type="radio" name="payment_method" value="credit_card">
                <span>Kartu Kredit</span>
            </label>
            <label class="payment-option">
                <input type="radio" name="payment_method" value="bank_transfer">
                <span>Transfer Bank</span>
            </label>
            <label class="payment-option">
                <input type="radio" name="payment_method" value="e_wallet">
                <span>E-Wallet (GoPay, ShopeePay, QRIS)</span>
            </label>
            <label class="payment-option">
                <input type="radio" name="payment_method" value="midtrans">
                <span>Midtrans (Semua Metode)</span>
            </label>
        </div>
    </div>

    <!-- Submit Button -->
    <button type="submit" id="checkoutBtn" class="checkout-btn">
        <span class="btn-text">Buat Pesanan</span>
        <span class="btn-loading hidden">
            <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Memproses...
        </span>
    </button>
</form>

<!-- Toast Container untuk Notifikasi -->
<div id="toast-container" class="fixed top-4 right-4 z-50"></div>
```

## JavaScript Implementation

### Main Checkout Handler
```javascript
document.addEventListener('DOMContentLoaded', function() {
    const checkoutForm = document.getElementById('checkoutForm');
    const checkoutBtn = document.getElementById('checkoutBtn');
    const btnText = checkoutBtn.querySelector('.btn-text');
    const btnLoading = checkoutBtn.querySelector('.btn-loading');

    checkoutForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Show loading state
        showLoadingState(checkoutBtn, btnText, btnLoading);
        
        try {
            const formData = new FormData(checkoutForm);
            
            const response = await fetch(checkoutForm.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            });

            const result = await response.json();
            
            if (result.success) {
                handleSuccessResponse(result);
            } else {
                handleErrorResponse(result);
            }
        } catch (error) {
            console.error('Checkout error:', error);
            showToast('Terjadi kesalahan. Silakan coba lagi.', 'error');
        } finally {
            // Hide loading state
            hideLoadingState(checkoutBtn, btnText, btnLoading);
        }
    });
});

function showLoadingState(btn, textEl, loadingEl) {
    btn.disabled = true;
    textEl.classList.add('hidden');
    loadingEl.classList.remove('hidden');
}

function hideLoadingState(btn, textEl, loadingEl) {
    btn.disabled = false;
    textEl.classList.remove('hidden');
    loadingEl.classList.add('hidden');
}
```

### Response Handlers
```javascript
function handleSuccessResponse(result) {
    // Show success message
    showToast(result.message, 'success');
    
    // Handle different redirect types
    if (result.external_redirect && result.redirect_url) {
        // Midtrans payment - redirect to external page
        showToast('Mengalihkan ke halaman pembayaran...', 'info');
        setTimeout(() => {
            window.location.href = result.redirect_url;
        }, 1500);
    } else if (result.redirect_url) {
        // Internal redirect (COD or thank you page)
        setTimeout(() => {
            window.location.href = result.redirect_url;
        }, 1000);
    }

    // Log order information for analytics
    if (result.order) {
        logOrderEvent('order_created', result.order);
    }

    // Track payment method
    if (result.payment) {
        logPaymentEvent('payment_initiated', result.payment);
    }
}

function handleErrorResponse(result) {
    // Show main error message
    showToast(result.message || 'Terjadi kesalahan pada checkout.', 'error');
    
    // Handle validation errors
    if (result.errors) {
        displayValidationErrors(result.errors);
    }

    // Log error for debugging
    console.error('Checkout error:', result);
}
```

### Validation Error Display
```javascript
function displayValidationErrors(errors) {
    // Clear previous errors
    clearValidationErrors();
    
    Object.keys(errors).forEach(field => {
        const input = document.querySelector(`[name="${field}"]`);
        if (input) {
            // Add error class
            input.classList.add('border-red-500');
            
            // Show error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'text-red-500 text-sm mt-1 error-message';
            errorDiv.textContent = errors[field][0];
            input.parentNode.appendChild(errorDiv);
        }
    });
}

function clearValidationErrors() {
    // Remove error classes
    document.querySelectorAll('.border-red-500').forEach(el => {
        el.classList.remove('border-red-500');
    });
    
    // Remove error messages
    document.querySelectorAll('.error-message').forEach(el => {
        el.remove();
    });
}
```

### Toast Notification System
```javascript
function showToast(message, type = 'info', duration = 5000) {
    const toastContainer = document.getElementById('toast-container');
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type} max-w-xs bg-white border border-gray-200 rounded-xl shadow-lg`;
    
    const bgColor = {
        'success': 'bg-green-50 border-green-200',
        'error': 'bg-red-50 border-red-200',
        'warning': 'bg-yellow-50 border-yellow-200',
        'info': 'bg-blue-50 border-blue-200'
    };
    
    const iconColor = {
        'success': 'text-green-400',
        'error': 'text-red-400',
        'warning': 'text-yellow-400',
        'info': 'text-blue-400'
    };

    toast.className += ` ${bgColor[type]}`;
    
    toast.innerHTML = `
        <div class="flex p-4">
            <div class="flex-shrink-0">
                <svg class="h-4 w-4 ${iconColor[type]}" viewBox="0 0 16 16" fill="currentColor">
                    ${getToastIcon(type)}
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-gray-700">${message}</p>
            </div>
            <div class="ml-auto pl-3">
                <button type="button" class="toast-close text-gray-400 hover:text-gray-600">
                    <svg class="h-3 w-3" viewBox="0 0 14 14" fill="currentColor">
                        <path d="M7 0C3.1 0 0 3.1 0 7s3.1 7 7 7 7-3.1 7-7-3.1-7-7-7zM10.5 9.1L9.1 10.5 7 8.4 4.9 10.5 3.5 9.1 5.6 7 3.5 4.9 4.9 3.5 7 5.6 9.1 3.5 10.5 4.9 8.4 7l2.1 2.1z"/>
                    </svg>
                </button>
            </div>
        </div>
    `;
    
    // Add click handler for close button
    toast.querySelector('.toast-close').addEventListener('click', () => {
        removeToast(toast);
    });
    
    // Add toast to container
    toastContainer.appendChild(toast);
    
    // Auto remove after duration
    setTimeout(() => {
        removeToast(toast);
    }, duration);
}

function removeToast(toast) {
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(100%)';
    setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 300);
}

function getToastIcon(type) {
    const icons = {
        'success': '<path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm3.78-9.72a.75.75 0 0 0-1.06-1.06L6.75 9.19 5.28 7.72a.75.75 0 0 0-1.06 1.06l2 2a.75.75 0 0 0 1.06 0l4.5-4.5z"/>',
        'error': '<path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/>',
        'warning': '<path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>',
        'info': '<path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>'
    };
    return icons[type] || icons['info'];
}
```

### Payment Method Enhancement
```javascript
// Enhanced payment method selection
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        updatePaymentMethodInfo(this.value);
    });
});

function updatePaymentMethodInfo(method) {
    const infoDiv = document.getElementById('payment-info');
    if (!infoDiv) return;

    const info = {
        'cod': {
            title: 'Cash on Delivery (COD)',
            description: 'Bayar langsung saat barang diterima. Tidak ada biaya tambahan.',
            icon: '💵'
        },
        'credit_card': {
            title: 'Kartu Kredit',
            description: 'Pembayaran menggunakan Visa, Mastercard, atau JCB melalui Midtrans.',
            icon: '💳'
        },
        'bank_transfer': {
            title: 'Transfer Bank',
            description: 'Transfer melalui Virtual Account BCA, BNI, BRI, atau Permata.',
            icon: '🏦'
        },
        'e_wallet': {
            title: 'E-Wallet',
            description: 'Pembayaran melalui GoPay, ShopeePay, atau QRIS.',
            icon: '📱'
        },
        'midtrans': {
            title: 'Midtrans (Semua Metode)',
            description: 'Pilih metode pembayaran di halaman Midtrans Snap.',
            icon: '🔒'
        }
    };

    const methodInfo = info[method] || info['midtrans'];
    
    infoDiv.innerHTML = `
        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
            <span class="text-2xl mr-3">${methodInfo.icon}</span>
            <div>
                <h4 class="font-medium text-gray-900">${methodInfo.title}</h4>
                <p class="text-sm text-gray-600">${methodInfo.description}</p>
            </div>
        </div>
    `;
}
```

### Analytics Integration
```javascript
// Order tracking untuk analytics
function logOrderEvent(event, orderData) {
    if (typeof gtag !== 'undefined') {
        gtag('event', event, {
            'event_category': 'ecommerce',
            'value': orderData.grand_total,
            'currency': 'IDR',
            'transaction_id': orderData.order_no
        });
    }

    // Custom analytics
    if (typeof analytics !== 'undefined') {
        analytics.track(event, {
            orderId: orderData.order_no,
            revenue: orderData.grand_total,
            currency: 'IDR'
        });
    }
}

function logPaymentEvent(event, paymentData) {
    if (typeof gtag !== 'undefined') {
        gtag('event', event, {
            'event_category': 'payment',
            'payment_type': paymentData.method,
            'payment_gateway': paymentData.gateway
        });
    }
}
```

## CSS Styles

### Toast Animations
```css
.toast {
    transform: translateX(100%);
    opacity: 0;
    transition: all 0.3s ease-in-out;
}

.toast.show {
    transform: translateX(0);
    opacity: 1;
}

.payment-option {
    display: flex;
    align-items: center;
    padding: 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    margin-bottom: 0.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.payment-option:hover {
    border-color: #3b82f6;
    background-color: #f8fafc;
}

.payment-option input[type="radio"]:checked + span {
    color: #3b82f6;
    font-weight: 600;
}

.checkout-btn {
    width: 100%;
    padding: 1rem 2rem;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-weight: 600;
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
}

.checkout-btn:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.checkout-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.border-red-500 {
    border-color: #ef4444 !important;
}

.error-message {
    color: #ef4444;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}
```

## Usage Example

### Complete Integration
```html
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Checkout - Toko Online</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-2xl font-bold mb-6">Checkout</h1>
            
            <!-- Form Implementation -->
            <!-- (Insert form HTML from above) -->
            
            <!-- Payment Method Info -->
            <div id="payment-info" class="mt-4"></div>
        </div>
    </div>

    <!-- JavaScript Implementation -->
    <script>
        // (Insert JavaScript from above)
        
        // Initialize payment method info
        document.addEventListener('DOMContentLoaded', function() {
            const selectedMethod = document.querySelector('input[name="payment_method"]:checked');
            if (selectedMethod) {
                updatePaymentMethodInfo(selectedMethod.value);
            }
        });
    </script>
</body>
</html>
```

Implementasi ini memberikan:
- ✅ Full AJAX checkout experience
- ✅ Real-time validation feedback
- ✅ Loading states dan animations
- ✅ Toast notifications yang user-friendly
- ✅ Payment method information
- ✅ Error handling yang robust
- ✅ Analytics integration ready
- ✅ Responsive design dengan Tailwind CSS
