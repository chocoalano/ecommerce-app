<flux:navbar.item
    icon="shopping-cart"
    href="#"
    label="Keranjang"
    badge="{{ $count }}"
    :current="request()->routeIs('cart.*')"
/>
