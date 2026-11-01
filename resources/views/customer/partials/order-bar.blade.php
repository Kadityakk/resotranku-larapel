{{-- Inner markup of the order bar on the menu page. It is rendered here on
     page load and again on the server after every change, then swapped in by
     cart.js, so the bar only ever has one source of markup. --}}
@if ($orderBar['lines'] !== [])
    <div class="rk-orderbar__panel" id="order-bar-panel">
        <ul class="rk-orderbar__list">
            @foreach ($orderBar['lines'] as $line)
                <li class="rk-orderbar__line" data-cart-line="{{ $line['id'] }}">
                    <img class="rk-orderbar__thumb" src="{{ $line['image'] }}" alt="" loading="lazy">

                    <div class="rk-orderbar__info">
                        <div class="rk-orderbar__name">{{ $line['name'] }}</div>
                        <x-rupiah :value="$line['price']" class="rk-orderbar__unit" />

                        <input type="text" class="rk-orderbar__note"
                               value="{{ $line['note'] ?? '' }}" maxlength="255"
                               placeholder="Pesan, mis. tanpa wasabi"
                               aria-label="Pesan untuk {{ $line['name'] }}"
                               data-cart-note
                               data-item-id="{{ $line['id'] }}"
                               data-url="{{ route('cart.update') }}">
                    </div>

                    <div class="rk-orderbar__side">
                        <div class="rk-stepper rk-stepper--sm">
                            <button type="button" data-cart-step="-1" data-item-id="{{ $line['id'] }}"
                                    data-url="{{ route('cart.update') }}" aria-label="Kurangi {{ $line['name'] }}">
                                <i class="fa fa-minus"></i>
                            </button>
                            <input type="number" value="{{ $line['qty'] }}" min="0" max="99"
                                   data-qty-input aria-label="Jumlah {{ $line['name'] }}">
                            <button type="button" data-cart-step="1" data-item-id="{{ $line['id'] }}"
                                    data-url="{{ route('cart.update') }}" aria-label="Tambah {{ $line['name'] }}">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                        <x-rupiah :value="$line['price'] * $line['qty']" class="rk-orderbar__total" />
                        <button type="button" class="rk-orderbar__remove"
                                data-cart-remove
                                data-item-id="{{ $line['id'] }}"
                                data-url="{{ route('cart.remove') }}"
                                aria-label="Hapus {{ $line['name'] }}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </li>
            @endforeach
        </ul>

        <dl class="rk-orderbar__sums">
            <div><dt>Subtotal</dt><dd><x-rupiah :value="$orderBar['totals']['subtotal']" /></dd></div>
            <div><dt>Pajak ({{ (int) round($orderBar['taxRate'] * 100) }}%)</dt><dd><x-rupiah :value="$orderBar['totals']['tax']" /></dd></div>
        </dl>
    </div>

    <div class="rk-orderbar__summary">
        <button type="button" class="rk-orderbar__toggle"
                data-order-bar-toggle aria-controls="order-bar-panel" aria-expanded="false">
            <span class="rk-orderbar__count">{{ $orderBar['quantity'] }}</span>
            <span class="rk-orderbar__label">
                Menu dipilih
                <x-rupiah :value="$orderBar['totals']['grand_total']" class="rk-orderbar__grand" />
            </span>
            <i class="fa fa-chevron-up rk-orderbar__chevron" aria-hidden="true"></i>
        </button>

        <a href="{{ route('checkout') }}" class="rk-orderbar__checkout">
            Pesan Sekarang <i class="fa fa-arrow-right ms-1"></i>
        </a>
    </div>
@endif
