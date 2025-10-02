<div class="bg-white py-10">
    <div class="mx-auto max-w-5/6 px-4 sm:px-6 lg:px-8">
        <div x-data="{
            scrollBy(delta) {
                const el = $refs.track;
                el.scrollBy({ left: delta, behavior: 'smooth' });
            }
        }" class="w-full">
            {{-- Carousel --}}
            <div class="relative">
                {{-- Judul row kategori --}}
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between mb-10">
                    <div class="max-w-4xl">
                        <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                            {{ $title }}
                        </h2>
                        <p class="mt-2 text-gray-600 text-base">{{ $description ?? '' }}</p>
                    </div>
                    <div class="flex items-center justify-between mt-4 sm:mt-0">
                        <div class="pointer-events-none">
                            <div class="pointer-events-auto mr-2">
                                <button type="button" x-on:click="scrollBy(-320)"
                                    class="inline-flex size-9 items-center justify-center rounded-full border border-gray-300 hover:bg-gray-50 p-6"
                                    aria-label="Sebelumnya">
                                    <flux:icon.chevron-left />
                                </button>
                                <button type="button" x-on:click="scrollBy(320)"
                                    class="inline-flex size-9 items-center justify-center rounded-full border border-gray-300 hover:bg-gray-50 p-6"
                                    aria-label="Berikutnya">
                                    <flux:icon.chevron-right />
                                </button>
                            </div>
                        </div>
                        <flux:button href="#" icon:trailing="arrow-up-right" class="!rounded-full py-6">
                            Lihat Semua
                        </flux:button>
                    </div>
                </div>
                {{-- Track --}}
                <div x-ref="track"
                    class="mx-auto py-4 px-4 sm:px-6 lg:px-8
                        flex gap-6 overflow-x-auto overscroll-x-contain
                        snap-x snap-mandatory pb-8"
                    style="scrollbar-width: thin;">
                    @foreach ($data as $i => $p)
                        <livewire:components.card-product :id="$p['id']" :title="$p['name']" :price="$p['price']" :image="$p['image']"/>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
