<span class="absolute -top-0 -right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-purple-600 rounded-full {{ $cartCount > 0 ? '' : 'hidden' }}">
    {{ $cartCount > 99 ? '99+' : $cartCount }}
</span>
