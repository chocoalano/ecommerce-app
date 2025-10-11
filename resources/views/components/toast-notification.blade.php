{{-- Toast Notification Container --}}
<div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2">
</div>

<script>
// Toast Notification System
class ToastManager {
    constructor() {
        this.container = document.getElementById('toast-container');
        this.toastId = 0;
    }

    /**
     * Menampilkan notifikasi toast.
     * @param {string} message - Isi pesan notifikasi.
     * @param {string} type - Tipe notifikasi ('success', 'error', 'warning', 'info', 'default').
     * @param {object} options - Opsi notifikasi (misalnya, duration).
     */
    show(message, type = 'default', options = {}) {
        const toast = this.createToast(message, type, options);
        this.container.appendChild(toast);

        // Auto dismiss after specified duration
        const duration = options.duration || 5000;
        if (duration > 0) {
            setTimeout(() => {
                this.dismiss(toast);
            }, duration);
        }

        return toast;
    }

    createToast(message, type, options) {
        this.toastId++;
        const toastId = `toast-${this.toastId}`;

        const toast = document.createElement('div');
        // Menghilangkan semua kelas dark:
        toast.className = 'flex items-center w-full max-w-xs p-4 text-gray-500 bg-white rounded-lg shadow-lg border border-gray-200 transform transition-all duration-300 ease-in-out translate-x-full opacity-0';
        toast.setAttribute('role', 'alert');

        const config = this.getTypeConfig(type);

        // Menghilangkan semua kelas dark: pada HTML internal
        toast.innerHTML = `
            <div class="inline-flex items-center justify-center shrink-0 w-8 h-8 ${config.bgColor} ${config.textColor} rounded-lg">
                ${config.icon}
                <span class="sr-only">${config.label}</span>
            </div>
            <div class="ms-3 text-sm font-normal flex-1">${message}</div>
            <button type="button"
                    class="ms-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8 transition-colors duration-200"
                    onclick="toastManager.dismiss(document.getElementById('${toastId}'))"
                    aria-label="Close">
                <span class="sr-only">Close</span>
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
            </button>
        `;

        // Animate in
        setTimeout(() => {
            toast.classList.remove('translate-x-full', 'opacity-0');
            toast.classList.add('translate-x-0', 'opacity-100');
        }, 10);

        return toast;
    }

    dismiss(toast) {
        if (!toast) return;

        // Animate out
        toast.classList.add('translate-x-full', 'opacity-0');
        toast.classList.remove('translate-x-0', 'opacity-100');

        // Remove from DOM after animation
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }

    getTypeConfig(type) {
        // Menghilangkan properti darkBg dan darkText
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

    // Convenience methods
    success(message, options = {}) {
        return this.show(message, 'success', options);
    }

    error(message, options = {}) {
        return this.show(message, 'error', options);
    }

    warning(message, options = {}) {
        return this.show(message, 'warning', options);
    }

    info(message, options = {}) {
        return this.show(message, 'info', options);
    }
}

// Initialize global toast manager
let toastManager;

document.addEventListener('DOMContentLoaded', function() {
    toastManager = new ToastManager();
});

// Global helper functions for backward compatibility
function showToast(message, type = 'default', options = {}) {
    if (typeof toastManager !== 'undefined') {
        return toastManager.show(message, type, options);
    } else {
        // Fallback if toast manager not ready
        console.log(`Toast [${type}]: ${message}`);
        alert(`${type.toUpperCase()}: ${message}`);
    }
}

function showSuccessToast(message, options = {}) {
    return showToast(message, 'success', options);
}

function showErrorToast(message, options = {}) {
    return showToast(message, 'error', options);
}

function showWarningToast(message, options = {}) {
    return showToast(message, 'warning', options);
}

function showInfoToast(message, options = {}) {
    return showToast(message, 'info', options);
}
</script>

<style>
/* Additional styles for better toast appearance */
#toast-container .bg-white {
    /* Menjaga styling blur untuk tampilan modern */
    backdrop-filter: blur(10px);
    background-color: rgba(255, 255, 255, 0.95);
}

/* Hapus blok dark mode yang tidak terpakai
#toast-container .dark\:bg-gray-800 {
    backdrop-filter: blur(10px);
    background-color: rgba(31, 41, 55, 0.95);
}
*/

/* Smooth hover transitions */
#toast-container button:hover {
    transform: scale(1.05);
}

/* Mobile responsive adjustments */
@media (max-width: 640px) {
    #toast-container {
        left: 1rem;
        right: 1rem;
        top: 1rem;
    }

    #toast-container > div {
        max-width: none;
        width: 100%;
    }
}
</style>
