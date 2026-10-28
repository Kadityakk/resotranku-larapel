/**
 * Opens the Midtrans Snap popup for an order.
 *
 * The Snap token and the redirect target are rendered onto the trigger button as
 * data attributes; the webhook remains the authority on payment status, so every
 * callback here simply forwards the browser to the finish URL.
 */
(function () {
    'use strict';

    var trigger = document.getElementById('snap-pay');

    if (!trigger) {
        return;
    }

    var token = trigger.getAttribute('data-token');
    var finishUrl = trigger.getAttribute('data-finish-url');
    var status = document.getElementById('snap-status');

    function announce(message) {
        if (status) {
            status.textContent = message;
        }
    }

    function goToFinish() {
        window.location.href = finishUrl;
    }

    function openSnap() {
        if (!window.snap) {
            announce('Gagal memuat layanan pembayaran. Periksa koneksi Anda lalu muat ulang halaman.');
            return;
        }

        window.snap.pay(token, {
            onSuccess: goToFinish,
            onPending: goToFinish,
            onError: goToFinish,
            onClose: function () {
                announce('Anda menutup jendela pembayaran sebelum selesai. Klik tombol di atas untuk mencoba lagi.');
            }
        });
    }

    trigger.addEventListener('click', openSnap);

    if (trigger.hasAttribute('data-autostart')) {
        window.setTimeout(openSnap, 600);
    }
})();
