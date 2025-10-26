<span class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-purple-500 rounded-full min-w-[1.25rem] {{ $cartCount > 0 ? '' : 'hidden' }}">
    {{ $cartCount > 99 ? '99+' : $cartCount }}
</span>
