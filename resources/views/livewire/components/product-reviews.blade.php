<div id="reviews" class="pt-4 pb-8">
    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="mb-4 p-4 text-green-800 border border-green-300 rounded-lg bg-green-50" role="alert">
            <div class="flex items-center">
                <svg class="flex-shrink-0 w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                </svg>
                <span class="sr-only">Info</span>
                <div class="ms-3 text-sm font-medium">{{ session('success') }}</div>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 text-red-800 border border-red-300 rounded-lg bg-red-50" role="alert">
            <div class="flex items-center">
                <svg class="flex-shrink-0 w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                </svg>
                <span class="sr-only">Info</span>
                <div class="ms-3 text-sm font-medium">{{ session('error') }}</div>
            </div>
        </div>
    @endif

    <h2 class="text-2xl font-bold text-gray-900 mb-6 border-b border-gray-200 pb-3">
        Ulasan Pelanggan ({{ $reviewStats['total'] }})
    </h2>

    <div class="lg:grid lg:grid-cols-3 lg:gap-10">
        {{-- Ringkasan Rating --}}
        <div class="lg:col-span-1">
            <div class="sticky top-8 bg-gray-50 p-6 rounded-xl">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Ringkasan Rating</h3>

                <div class="flex items-center space-x-3 mb-4">
                    <p class="text-5xl font-extrabold text-gray-900">{{ number_format($reviewStats['average'], 1) }}</p>
                    <div>
                        <div class="flex items-center text-2xl text-yellow-500">
                            {{ str_repeat('★', $reviewStats['average_int']) }}{{ str_repeat('☆', 5 - $reviewStats['average_int']) }}
                        </div>
                        <p class="text-sm text-gray-500">{{ $reviewStats['total'] }} Total Ulasan</p>
                    </div>
                </div>

                {{-- Distribusi Rating --}}
                <div class="space-y-1">
                    @foreach ($reviewStats['distribution'] as $star => $data)
                        <div class="flex items-center space-x-3">
                            <span class="text-xs text-gray-600 w-4 font-medium">{{ $star }} ★</span>
                            <div class="flex-1 h-2 bg-gray-200 rounded-full">
                                <div class="h-2 bg-yellow-400 rounded-full transition-all duration-300" style="width: {{ $data['percentage'] }}%"></div>
                            </div>
                            <span class="text-xs text-gray-600 w-8 text-right font-medium">{{ $data['percentage'] }}%</span>
                        </div>
                    @endforeach
                </div>

                {{-- Filter Rating --}}
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <label for="filter_rating" class="block text-sm font-medium text-gray-700 mb-2">Filter by Rating:</label>
                    <select wire:model.live="filterByRating" id="filter_rating"
                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-zinc-900 focus:border-zinc-900 block p-2">
                        <option value="all">Semua Rating</option>
                        <option value="5">5 Bintang ({{ $reviewStats['distribution'][5]['count'] }})</option>
                        <option value="4">4 Bintang ({{ $reviewStats['distribution'][4]['count'] }})</option>
                        <option value="3">3 Bintang ({{ $reviewStats['distribution'][3]['count'] }})</option>
                        <option value="2">2 Bintang ({{ $reviewStats['distribution'][2]['count'] }})</option>
                        <option value="1">1 Bintang ({{ $reviewStats['distribution'][1]['count'] }})</option>
                    </select>
                </div>

                {{-- Tombol tulis ulasan --}}
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-sm text-gray-700 mb-3">Bagikan pengalaman Anda tentang produk ini.</p>
                    <button type="button"
                            x-ref="openBtn"
                            wire:click="openWriteReviewModal"
                            class="w-full inline-flex items-center justify-center py-3 text-base font-semibold text-center text-white
                                   bg-zinc-900 rounded-full hover:bg-zinc-700 focus:ring-4 focus:ring-zinc-300 transition duration-300">
                        <svg class="w-4 h-4 mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tulis Ulasan Anda
                    </button>
                </div>
            </div>
        </div>

        {{-- Daftar Ulasan --}}
        <div class="lg:col-span-2 mt-8 lg:mt-0">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 pb-4 mb-6">
                <h3 class="text-lg font-semibold text-gray-800">{{ $reviews->total() }} Ulasan</h3>
                <div class="flex items-center space-x-3 mt-3 sm:mt-0">
                    <label for="sort_reviews" class="text-sm text-gray-600 whitespace-nowrap">Urutkan:</label>
                    <select wire:model.live="sortBy" id="sort_reviews"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-zinc-900 focus:border-zinc-900 block p-2">
                        <option value="newest">Terbaru</option>
                        <option value="oldest">Terlama</option>
                        <option value="highest_rating">Rating Tertinggi</option>
                        <option value="lowest_rating">Rating Terendah</option>
                    </select>
                </div>
            </div>

            {{-- Loading --}}
            <div wire:loading.delay wire:target="sortBy, filterByRating" class="text-center py-4">
                <div role="status">
                    <svg aria-hidden="true" class="inline w-6 h-6 text-gray-200 animate-spin fill-zinc-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                        <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                    </svg>
                    <span class="sr-only">Loading...</span>
                </div>
            </div>

            {{-- List Ulasan --}}
            <div wire:loading.remove wire:target="sortBy, filterByRating" class="space-y-8">
                @forelse ($reviews as $review)
                    <div class="border-b border-gray-100 pb-8 last:border-b-0 animate-fade-in">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center space-x-3">
                                <div class="size-8 rounded-full bg-zinc-100 grid place-items-center text-zinc-600 font-semibold text-sm shrink-0">
                                    {{ strtoupper(substr($review->customer->name ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $review->customer->name ?? 'User' }}</p>
                                    <span class="text-xs text-gray-500">{{ $review->created_at->diffForHumans() }}</span>
                                    @if($review->is_approved ?? false)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 ml-2">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            Terverifikasi
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-sm text-yellow-500 shrink-0">
                                {{ str_repeat('★', (int)$review->rating) }}{{ str_repeat('☆', 5 - (int)$review->rating) }}
                            </div>
                        </div>

                        @if($review->title)
                            <h4 class="text-base font-bold text-gray-800 mt-2 mb-1">{{ $review->title }}</h4>
                        @endif
                        <p class="text-gray-700 leading-relaxed text-sm">{{ $review->comment }}</p>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada ulasan</h3>
                        <p class="mt-1 text-sm text-gray-500">Jadilah yang pertama memberikan ulasan untuk produk ini.</p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($reviews->hasPages())
                <div class="mt-8">
                    {{ $reviews->onEachSide(1)->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- WRITE REVIEW MODAL: selalu di DOM, TANPA aria-hidden --}}
    <div
        x-data="{
            show: @entangle('showWriteReviewModal').live,
            close() {
                // Lepas fokus dari elemen di dalam modal agar tidak ada focused descendant
                if (document.activeElement) { document.activeElement.blur(); }
                // Tutup (Alpine + Livewire)
                this.show = false;
                $wire.closeWriteReviewModal();
                // Kembalikan fokus ke tombol pembuka
                $nextTick(() => $refs.openBtn?.focus());
            }
        }"
        x-cloak
        @keydown.escape.window="if (show) close()"
        x-on:open-review-modal.window="show = true"   {{-- kompatibel dgn $this->dispatch('open-review-modal') --}}
        x-effect="
            if (show) {
                document.documentElement.classList.add('overflow-hidden');
                $nextTick(() => $refs.review_title?.focus());
            } else {
                document.documentElement.classList.remove('overflow-hidden');
            }
        "
    >
        <div
            class="fixed inset-0 z-50 isolate overflow-y-auto"
            x-show="show"
            x-transition.opacity
            role="dialog"
            aria-modal="true"
            aria-labelledby="write-review-title"
            style="display:none"
        >
            {{-- Backdrop: HANYA di sini pakai backdrop-blur --}}
            <div
                class="absolute inset-0 z-0 bg-black/30 backdrop-blur-sm"
                x-show="show"
                x-transition.opacity
                @click="close()"
            ></div>

            {{-- Content wrapper: di ATAS backdrop --}}
            <div
                class="relative z-10 flex min-h-full items-end sm:items-center justify-center p-4"
                x-show="show"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                @click.self="close()"
            >
                <div class="w-full sm:max-w-lg rounded-lg bg-white shadow-xl overflow-hidden" @click.stop>
                    <form wire:submit.prevent="submitReview">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="flex items-center justify-between mb-4">
                                <h3 id="write-review-title" class="text-lg font-medium text-gray-900">Tulis Ulasan</h3>
                                <button type="button"
                                        class="text-gray-400 hover:text-gray-600 focus:outline-none"
                                        @click="close()"
                                        aria-label="Tutup">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            {{-- Rating --}}
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Rating *</label>
                                <div class="flex items-center space-x-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <button type="button"
                                                wire:click="$set('newReview.rating', {{ $i }})"
                                                class="text-2xl {{ ($newReview['rating'] ?? 0) >= $i ? 'text-yellow-400' : 'text-gray-300' }} hover:text-yellow-400 focus:outline-none"
                                                aria-label="Pilih rating {{ $i }}">
                                            ★
                                        </button>
                                    @endfor
                                    <span class="ml-2 text-sm text-gray-600">({{ $newReview['rating'] ?? 0 }} dari 5)</span>
                                </div>
                                @error('newReview.rating')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Title --}}
                            <div class="mb-4">
                                <label for="review_title" class="block text-sm font-medium text-gray-700 mb-2">Judul Ulasan</label>
                                <input type="text" wire:model="newReview.title" id="review_title" x-ref="review_title"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-zinc-500 focus:border-zinc-500"
                                       placeholder="Ringkasan pengalaman Anda...">
                                @error('newReview.title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Comment --}}
                            <div class="mb-1">
                                <label for="review_comment" class="block text-sm font-medium text-gray-700 mb-2">Ulasan *</label>
                                <textarea wire:model="newReview.comment" id="review_comment" rows="4"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-zinc-500 focus:border-zinc-500"
                                          placeholder="Ceritakan pengalaman Anda dengan produk ini..."></textarea>
                                <p class="mt-1 text-xs text-gray-500">Minimal 10 karakter, maksimal 1000 karakter</p>
                                @error('newReview.comment')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" wire:loading.attr="disabled"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-zinc-900 text-base font-medium text-white hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                                <span wire:loading.remove>Kirim Ulasan</span>
                                <span wire:loading>Mengirim...</span>
                            </button>
                            <button type="button"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                                    @click="close()">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

@push('styles')
<style>
    .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px);} to { opacity: 1; transform: translateY(0);} }
    [x-cloak] { display: none !important; }
</style>
@endpush
