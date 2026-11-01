/**
 * Order bar interactions for the customer menu page.
 *
 * Picked menus collect in the bar fixed to the bottom of the screen. The
 * server re-renders the bar after every change and this script swaps that
 * markup in, so no markup is rebuilt here. Every endpoint and item id is read
 * from data attributes rendered by Blade, so no URL or token is hard-coded.
 */
(function () {
    'use strict';

    var csrfToken = document.querySelector('meta[name="csrf-token"]');
    csrfToken = csrfToken ? csrfToken.getAttribute('content') : '';

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

    function orderBar() {
        return document.querySelector('[data-order-bar]');
    }

    function setOpen(open) {
        var bar = orderBar();

        if (!bar) {
            return;
        }

        bar.classList.toggle('is-open', open);
        fitListToTwoLines(bar);

        var toggle = bar.querySelector('[data-order-bar-toggle]');

        if (toggle) {
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        }
    }

    function listOf(bar) {
        return bar.querySelector('.rk-orderbar__list');
    }

    /**
     * Caps the list at the height of its first two lines, so a third menu
     * scrolls inside the bar instead of stacking it higher up the screen.
     */
    function fitListToTwoLines(bar) {
        var list = listOf(bar);
        var lines = list ? list.querySelectorAll('[data-cart-line]') : [];

        if (!list || !bar.classList.contains('is-open')) {
            return;
        }

        if (lines.length < 3) {
            list.style.setProperty('--rk-orderbar-list-max', 'none');
            return;
        }

        var height = lines[1].offsetTop + lines[1].offsetHeight - list.offsetTop;
        list.style.setProperty('--rk-orderbar-list-max', height + 'px');
    }

    /**
     * Swaps in the freshly rendered bar, keeps it open or closed and the list
     * scrolled where it was, and replays the pop animation so a change is
     * visibly acknowledged. A newly added menu is scrolled into view.
     */
    function renderOrderBar(data, options) {
        var bar = orderBar();

        if (!bar || typeof data.order_bar !== 'string') {
            return;
        }

        var body = bar.querySelector('[data-order-bar-body]');
        var isEmpty = !data.cart_quantity;
        var open = !isEmpty && (bar.classList.contains('is-open') || (options && options.open));
        var previousList = listOf(bar);
        var scrollTop = previousList ? previousList.scrollTop : 0;

        body.innerHTML = data.order_bar;
        bar.classList.toggle('is-empty', isEmpty);
        setOpen(open);

        var list = listOf(bar);

        if (list) {
            list.scrollTop = scrollTop;

            var added = options && options.itemId ? lineOf(options.itemId) : null;

            if (added) {
                list.scrollTop = Math.max(0, added.offsetTop - list.offsetTop + added.offsetHeight - list.clientHeight);
            }
        }

        if (isEmpty) {
            return;
        }

        bar.classList.remove('is-bumped');
        void bar.offsetWidth; // reflow, so the animation restarts every time
        bar.classList.add('is-bumped');
    }

    function setBusy(control, busy) {
        if (control) {
            control.disabled = busy;
        }
    }

    function lineOf(itemId) {
        return document.querySelector('[data-cart-line="' + itemId + '"]');
    }

    function readQuantity(itemId) {
        var line = lineOf(itemId);
        var input = line ? line.querySelector('[data-qty-input]') : null;

        return input ? parseInt(input.value, 10) || 0 : 0;
    }

    /**
     * A button may name a quantity input via data-qty-source; only look it up
     * when it is set, since querySelector('') would throw.
     */
    function quantityFieldOf(button) {
        var selector = button.getAttribute('data-qty-source');

        return selector ? document.querySelector(selector) : null;
    }

    function send(control, payload, onSuccess) {
        setBusy(control, true);

        request(control.getAttribute('data-url'), payload)
            .then(onSuccess)
            .catch(function (error) {
                toast(error.message, 'danger');
            })
            .finally(function () {
                setBusy(control, false);
            });
    }

    function handleAdd(button) {
        var field = quantityFieldOf(button);

        send(button, {
            id: parseInt(button.getAttribute('data-item-id'), 10),
            qty: field ? parseInt(field.value, 10) || 1 : 1
        }, function (data) {
            renderOrderBar(data, { open: true, itemId: parseInt(button.getAttribute('data-item-id'), 10) });
            toast(data.message, 'success');

            if (field) {
                field.value = 1;
            }
        });
    }

    function handleQuantityChange(control, itemId, quantity) {
        send(control, { id: itemId, qty: quantity }, function (data) {
            renderOrderBar(data);
        });
    }

    function handleNoteChange(input) {
        send(input, {
            id: parseInt(input.getAttribute('data-item-id'), 10),
            note: input.value
        }, function (data) {
            renderOrderBar(data);
        });
    }

    function handleRemove(button) {
        send(button, { id: parseInt(button.getAttribute('data-item-id'), 10) }, function (data) {
            renderOrderBar(data);
            toast(data.message, 'success');
        });
    }

    document.addEventListener('click', function (event) {
        var add = event.target.closest('[data-cart-add]');

        if (add) {
            event.preventDefault();
            handleAdd(add);
            return;
        }

        // Menu card stepper: only adjusts the local field, the order is
        // untouched until "Tambah Pesanan" reads it through data-qty-source.
        var qtyStep = event.target.closest('[data-qty-step]');

        if (qtyStep) {
            event.preventDefault();
            var field = qtyStep.parentElement.querySelector('input');
            var next = (parseInt(field.value, 10) || 1) + parseInt(qtyStep.getAttribute('data-qty-step'), 10);
            field.value = Math.min(99, Math.max(1, next));
            return;
        }

        var toggle = event.target.closest('[data-order-bar-toggle]');

        if (toggle) {
            event.preventDefault();
            setOpen(!orderBar().classList.contains('is-open'));
            return;
        }

        var step = event.target.closest('[data-cart-step]');

        if (step) {
            event.preventDefault();
            var stepId = parseInt(step.getAttribute('data-item-id'), 10);
            var delta = parseInt(step.getAttribute('data-cart-step'), 10);
            handleQuantityChange(step, stepId, Math.min(99, readQuantity(stepId) + delta));
            return;
        }

        var remove = event.target.closest('[data-cart-remove]');

        if (remove) {
            event.preventDefault();
            handleRemove(remove);
        }
    });

    document.addEventListener('change', function (event) {
        var note = event.target.closest('[data-cart-note]');

        if (note) {
            handleNoteChange(note);
            return;
        }

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

    // Lines reflow when the screen width changes, so measure them again.
    window.addEventListener('resize', function () {
        var bar = orderBar();

        if (bar) {
            fitListToTwoLines(bar);
        }
    });

    // Enter in a message field saves it instead of doing nothing.
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' && event.target.matches('[data-cart-note]')) {
            event.preventDefault();
            event.target.blur();
        }
    });
})();
