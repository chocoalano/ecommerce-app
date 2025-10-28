// resources/js/toast-manager.js

// 1. Deklarasikan kelas ToastManager (sama)
class ToastManager {
    constructor() {
        this.container = document.getElementById('toast-container');
        this.toastId = 0;

        // Cek container saat konstruksi, tapi tidak fatal jika null/undefined
        if (!this.container) {
             console.error('Toast container element #toast-container not found! Toasts will not display.');
        }
    }

    /**
     * Menampilkan notifikasi toast.
     */
    show(message, type = 'default', options = {}) {
        // Cek container saat dipanggil
        if (!this.container) {
            console.error('Toast container element is missing. Cannot show toast.');
            return;
        }

        const toast = this.createToast(message, type, options);
        this.container.appendChild(toast);

        // ... (Logika dismiss dan animasi tetap sama) ...
        const duration = options.duration || 5000;
        if (duration > 0) {
            setTimeout(() => {
                this.dismiss(toast);
            }, duration);
        }

        return toast;
    }

    createToast(message, type, options) {
        // ... (Logika pembuatan toast tetap sama) ...
        this.toastId++;
        const toastId = `toast-${this.toastId}`;

        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = 'flex items-center w-full max-w-xs p-4 text-gray-500 bg-white rounded-lg shadow-lg border border-gray-200 transform transition-all duration-300 ease-in-out translate-x-full opacity-0';
        toast.setAttribute('role', 'alert');

        const config = this.getTypeConfig(type);

        toast.innerHTML = `
            <div class="inline-flex items-center justify-center shrink-0 w-8 h-8 ${config.bgColor} ${config.textColor} rounded-lg">
                ${config.icon}
                <span class="sr-only">${config.label}</span>
            </div>
            <div class="ms-3 text-sm font-normal flex-1">${message}</div>
            <button type="button"
                    data-toast-dismiss="${toastId}"
                    class="ms-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8 transition-colors duration-200"
                    aria-label="Close">
                <span class="sr-only">Close</span>
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
            </button>
        `;

        const dismissButton = toast.querySelector('button[data-toast-dismiss]');
        dismissButton.addEventListener('click', () => {
            // Gunakan window.showToast() agar dismissal dapat diakses dari mana saja
            this.dismiss(toast);
        });

        setTimeout(() => {
            toast.classList.remove('translate-x-full', 'opacity-0');
            toast.classList.add('translate-x-0', 'opacity-100');
        }, 10);

        return toast;
    }

    dismiss(toast) {
        if (!toast) return;

        toast.classList.add('translate-x-full', 'opacity-0');
        toast.classList.remove('translate-x-0', 'opacity-100');

        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }

    // ... (Metode getTypeConfig dan Convenience methods tetap sama) ...
    getTypeConfig(type) {
        // ... (seluruh kode getTypeConfig Anda) ...
        const configs = {
            success: {
                bgColor: 'bg-green-100',
                textColor: 'text-green-500',
                label: 'Success icon',
                icon: `<svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 12">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5.917 5.724 10.5 15 1.5"/>
                </svg>`
            },
            error: {
                bgColor: 'bg-red-100',
                textColor: 'text-red-500',
                label: 'Error icon',
                icon: `<svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>`
            },
            warning: {
                bgColor: 'bg-orange-100',
                textColor: 'text-orange-500',
                label: 'Warning icon',
                icon: `<svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>`
            },
            info: {
                bgColor: 'bg-blue-100',
                textColor: 'text-blue-500',
                label: 'Info icon',
                icon: `<svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9h2v5m-2 0h4M9.408 5.5h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>`
            },
            default: {
                bgColor: 'bg-gray-100',
                textColor: 'text-gray-500',
                label: 'Default icon',
                icon: `<svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 20">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.147 15.085a7.159 7.159 0 0 1-6.189 3.307A6.713 6.713 0 0 1 3.1 15.444c-2.679-4.513.287-8.737.888-9.548A4.373 4.373 0 0 0 5 1.608c1.287.953 6.445 3.218 5.537 10.5 1.5-1.122 2.706-3.01 2.853-6.14 1.433 1.049 3.993 5.395 1.757 9.117Z"/>
                </svg>`
            }
        };

        return configs[type] || configs.default;
    }

    success(message, options = {}) { return this.show(message, 'success', options); }
    error(message, options = {}) { return this.show(message, 'error', options); }
    warning(message, options = {}) { return this.show(message, 'warning', options); }
    info(message, options = {}) { return this.show(message, 'info', options); }
}


// 2. MODUL INITIATION & GLOBAL EXPOSURE (Perbaikan Lazy Initialization)

// Hapus block document.addEventListener('DOMContentLoaded', ...)
// dan inisialisasi di sini

/**
 * Fungsi utama untuk menampilkan toast.
 * Menggunakan Lazy Initialization untuk memastikan ToastManager sudah ada saat dipanggil.
 */
function showToast(message, type = 'default', options = {}) {
    // 1. Cek dan inisialisasi ToastManager jika belum ada
    if (!window.toastManager) {
        // Jika belum ada, buat instance baru. Ini mengamankan dari timing issue.
        window.toastManager = new ToastManager();
    }

    // 2. Sekarang kita yakin window.toastManager sudah ada
    if (window.toastManager && window.toastManager.container) {
        return window.toastManager.show(message, type, options);
    } else {
        // Fallback jika container tidak ditemukan (misalnya, HTML belum fully loaded)
        console.warn(`Toast Manager failed to load container. Toast: [${type}]: ${message}`);
    }
}

// 3. Mengekspos semua fungsi helper ke objek window
window.showToast = showToast;
window.showSuccessToast = (message, options = {}) => showToast(message, 'success', options);
window.showErrorToast = (message, options = {}) => showToast(message, 'error', options);
window.showWarningToast = (message, options = {}) => showToast(message, 'warning', options);
window.showInfoToast = (message, options = {}) => showToast(message, 'info', options);
