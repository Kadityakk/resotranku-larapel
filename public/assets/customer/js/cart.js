/**
 * Cart interactions for the customer pages.
 *
 * Every endpoint and item id is read from data attributes rendered by Blade, so
 * no URL or token is hard-coded here.
 */
(function () {
    'use strict';

    var csrfToken = document.querySelector('meta[name="csrf-token"]');
    csrfToken = csrfToken ? csrfToken.getAttribute('content') : '';

    var rupiah = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });

    function formatRupiah(value) {
        return 'Rp' + rupiah.format(Math.round(value || 0));
    }

    function toast(message, variant) {
        var stack = document.getElementById('toast-stack');

        if (!stack) {
            window.alert(message);
            return;
        }

        var el = document.createElement('div');
        el.className = 'alert alert-' + (variant || 'success') + ' alert-dismissible fade show shadow-sm';
        el.setAttribute('role', 'alert');
        el.textContent = message;

        var close = document.createElement('button');
        close.type = 'button';
        close.className = 'btn-close';
        close.setAttribute('data-bs-dismiss', 'alert');
        close.setAttribute('aria-label', 'Tutup');
        el.appendChild(close);

        stack.appendChild(el);

        window.setTimeout(function () {
            el.classList.remove('show');
            window.setTimeout(function () {
                el.remove();
            }, 300);
        }, 3000);
    }

    function request(url, payload) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        }).then(function (response) {
            return response.json().then(function (data) {
                if (!response.ok || data.success === false) {
                    throw new Error(data.message || 'Terjadi kesalahan. Silakan coba lagi.');
                }

                return data;
            });
        });
    }

    function updateBadge(quantity) {
        var badge = document.getElementById('cart-badge');

        if (!badge) {
            return;
        }

        badge.textContent = quantity;
        badge.classList.toggle('d-none', !quantity);
    }

    function updateSummary(totals) {
        if (!totals) {
            return;
        }

        var map = {
            '[data-summary-subtotal]': totals.subtotal,
            '[data-summary-tax]': totals.tax,
            '[data-summary-total]': totals.grand_total
        };

        Object.keys(map).forEach(function (selector) {
            document.querySelectorAll(selector).forEach(function (node) {
                node.textContent = formatRupiah(map[selector]);
            });
        });
    }

    function lineOf(itemId) {
        return document.querySelector('[data-cart-line="' + itemId + '"]');
    }

    function updateLine(itemId, quantity) {
        var line = lineOf(itemId);

        if (!line) {
            return;
        }

        if (quantity <= 0) {
            line.remove();
            refreshIfCartEmpty();
            return;
        }

        var input = line.querySelector('[data-qty-input]');
        var total = line.querySelector('[data-line-total]');
        var price = parseInt(line.getAttribute('data-price'), 10) || 0;

        if (input) {
            input.value = quantity;
        }

        if (total) {
            total.textContent = formatRupiah(price * quantity);
        }
    }

    function refreshIfCartEmpty() {
        if (!document.querySelectorAll('[data-cart-line]').length) {
            window.location.reload();
        }
    }

    function readQuantity(itemId) {
        var line = lineOf(itemId);
        var input = line ? line.querySelector('[data-qty-input]') : null;

        return input ? parseInt(input.value, 10) || 0 : 0;
    }

    function setBusy(button, busy) {
        if (button) {
            button.disabled = busy;
        }
    }

    function handleAdd(button) {
        var quantityField = document.querySelector(button.getAttribute('data-qty-source') || '');
        var quantity = quantityField ? parseInt(quantityField.value, 10) || 1 : 1;

        setBusy(button, true);

        request(button.getAttribute('data-url'), {
            id: parseInt(button.getAttribute('data-item-id'), 10),
            qty: quantity
        })
            .then(function (data) {
                updateBadge(data.cart_quantity);
                toast(data.message, 'success');
            })
            .catch(function (error) {
                toast(error.message, 'danger');
            })
            .finally(function () {
                setBusy(button, false);
            });
    }

    function handleQuantityChange(button, itemId, quantity) {
        if (quantity <= 0) {
            if (!window.confirm('Hapus item ini dari keranjang?')) {
                return;
            }
        }

        setBusy(button, true);

        request(button.getAttribute('data-url'), { id: itemId, qty: quantity })
            .then(function (data) {
                updateBadge(data.cart_quantity);
                updateSummary(data.totals);
                updateLine(itemId, quantity);
            })
            .catch(function (error) {
                toast(error.message, 'danger');
            })
            .finally(function () {
                setBusy(button, false);
            });
    }

    function handleRemove(button) {
        if (!window.confirm('Apakah Anda yakin ingin menghapus item ini?')) {
            return;
        }

        var itemId = parseInt(button.getAttribute('data-item-id'), 10);

        setBusy(button, true);

        request(button.getAttribute('data-url'), { id: itemId })
            .then(function (data) {
                updateBadge(data.cart_quantity);
                updateSummary(data.totals);
                updateLine(itemId, 0);
                toast(data.message, 'success');
            })
            .catch(function (error) {
                toast(error.message, 'danger');
            })
            .finally(function () {
                setBusy(button, false);
            });
    }

    document.addEventListener('click', function (event) {
        var add = event.target.closest('[data-cart-add]');

        if (add) {
            event.preventDefault();
            handleAdd(add);
            return;
        }

        var step = event.target.closest('[data-cart-step]');

        if (step) {
            event.preventDefault();
            var stepId = parseInt(step.getAttribute('data-item-id'), 10);
            var delta = parseInt(step.getAttribute('data-cart-step'), 10);
            handleQuantityChange(step, stepId, readQuantity(stepId) + delta);
            return;
        }

        var remove = event.target.closest('[data-cart-remove]');

        if (remove) {
            event.preventDefault();
            handleRemove(remove);
        }
    });

    document.addEventListener('change', function (event) {
        var input = event.target.closest('[data-qty-input]');

        if (!input) {
            return;
        }

        var line = input.closest('[data-cart-line]');
        var itemId = parseInt(line.getAttribute('data-cart-line'), 10);
        var quantity = parseInt(input.value, 10);

        if (isNaN(quantity)) {
            input.value = 1;
            return;
        }

        handleQuantityChange(input, itemId, Math.min(99, Math.max(0, quantity)));
    });
})();
