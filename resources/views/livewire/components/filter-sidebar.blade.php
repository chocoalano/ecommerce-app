<div x-data="{ open: false }">
    <!-- Mobile filter button -->
    <div class="p-4 lg:hidden">
        <button @click="open = true" type="button"
            class="w-full inline-flex items-center justify-center px-5 py-2.5 text-sm font-semibold text-center text-gray-900
                   border border-gray-300 rounded-full hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 transition duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 12.414V17a1 1 0 01-1.447.894l-2-1A1 1 0 018 16v-3.586L3.293 6.707A1 1 0 013 6V3z"
                    clip-rule="evenodd" />
            </svg>
            Filter Produk
        </button>
    </div>

    <!-- Mobile filter dialog -->
    <div x-show="open" class="relative z-40 lg:hidden" role="dialog" aria-modal="true" x-cloak>
        <!-- Removed black background overlay -->

        <div class="fixed inset-0 z-40 flex">
            <div x-show="open" x-transition:enter="transition ease-in-out duration-300 transform"
                x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in-out duration-300 transform"
                x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
                class="relative ml-auto flex h-full w-full max-w-xs flex-col overflow-y-auto bg-white py-4 pb-12 shadow-xl"
                @click.away="open = false">
                <div class="flex items-center justify-between px-4">
                    <h2 class="text-lg font-medium text-gray-900">Filter Produk</h2>
                    <button @click="open = false" type="button"
                        class="-mr-2 flex h-10 w-10 items-center justify-center rounded-md bg-white p-2 text-gray-400">
                        <span class="sr-only">Close menu</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Filters -->
                <div class="mt-4 border-t border-gray-200">
                    <div class="p-5 space-y-6">
                        @include('livewire.components.partials._filter-fields', ['type' => 'mobile'])
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Desktop sidebar -->
    <aside class="lg:w-10/12 sticky top-6 self-start hidden lg:block">
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Filter Produk</h2>
            </div>

            <div class="p-5 space-y-6">
                @include('livewire.components.partials._filter-fields', ['type' => 'desktop'])
            </div>
        </div>
    </aside>
</div>

<script>
    // Optional: scroll to top on filter submit
    document.getElementById('filterForm')?.addEventListener('submit', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
</script>
