/**
 * Admin behaviour: DataTables bootstrapping, SweetAlert2 delete confirmation,
 * the mobile sidebar toggle and flash-message surfacing.
 *
 * Everything is driven by data attributes so no configuration lives in Blade.
 */
(function () {
    'use strict';

    var INDONESIAN_LABELS = {
        search: '',
        searchPlaceholder: 'Cari...',
        lengthMenu: 'Tampilkan _MENU_ data',
        info: 'Menampilkan _START_ s/d _END_ dari _TOTAL_ data',
        infoEmpty: 'Tidak ada data untuk ditampilkan',
        infoFiltered: '(disaring dari _MAX_ total data)',
        zeroRecords: 'Data tidak ditemukan',
        emptyTable: 'Belum ada data',
        paginate: {
            first: 'Pertama',
            last: 'Terakhir',
            next: 'Berikutnya',
            previous: 'Sebelumnya'
        }
    };

    function initDataTables() {
        if (typeof window.jQuery === 'undefined' || !window.jQuery.fn.DataTable) {
            return;
        }

        window.jQuery('table[data-datatable]').each(function () {
            var $table = window.jQuery(this);

            if (window.jQuery.fn.DataTable.isDataTable(this)) {
                return;
            }

            $table.DataTable({
                language: INDONESIAN_LABELS,
                pageLength: parseInt($table.attr('data-page-length'), 10) || 10,
                order: $table.attr('data-order') ? JSON.parse($table.attr('data-order')) : [],
                columnDefs: [{
                    targets: 'no-sort',
                    orderable: false,
                    searchable: false
                }],
                layout: {
                    topStart: 'pageLength',
                    topEnd: 'search',
                    bottomStart: 'info',
                    bottomEnd: 'paging'
                }
            });
        });
    }

    /**
     * Any form carrying `data-confirm-delete` asks for confirmation before it
     * submits. The dialog text comes from `data-confirm-text`.
     */
    function initDeleteConfirmation() {
        document.addEventListener('submit', function (event) {
            var form = event.target.closest('[data-confirm-delete]');

            if (!form || form.dataset.confirmed === 'true') {
                return;
            }

            event.preventDefault();

            var title = form.getAttribute('data-confirm-title') || 'Hapus data ini?';
            var text = form.getAttribute('data-confirm-text') || 'Tindakan ini tidak dapat dibatalkan.';

            if (typeof window.Swal === 'undefined') {
                if (window.confirm(title + '\n\n' + text)) {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }

                return;
            }

            window.Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                reverseButtons: true
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
            });
        });
    }

    /**
     * Confirmation for non-destructive but irreversible actions, e.g. a cashier
     * settling a payment.
     */
    function initActionConfirmation() {
        document.addEventListener('submit', function (event) {
            var form = event.target.closest('[data-confirm-action]');

            if (!form || form.dataset.confirmed === 'true') {
                return;
            }

            event.preventDefault();

            var title = form.getAttribute('data-confirm-title') || 'Lanjutkan tindakan ini?';
            var text = form.getAttribute('data-confirm-text') || '';

            if (typeof window.Swal === 'undefined') {
                if (window.confirm(title)) {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }

                return;
            }

            window.Swal.fire({
                title: title,
                text: text,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, lanjutkan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#81c408',
                cancelButtonColor: '#6c757d',
                reverseButtons: true
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
            });
        });
    }

    /**
     * Flash messages are rendered into a hidden element by Blade and shown here
     * as a toast, so controllers keep using plain `->with('success', ...)`.
     */
    function initFlashToast() {
        var flash = document.getElementById('rk-flash');

        if (!flash || typeof window.Swal === 'undefined') {
            return;
        }

        var message = flash.getAttribute('data-message');

        if (!message) {
            return;
        }

        window.Swal.fire({
            toast: true,
            position: 'top-end',
            icon: flash.getAttribute('data-icon') || 'success',
            title: message,
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true
        });
    }

    function initSidebarToggle() {
        var body = document.body;

        document.addEventListener('click', function (event) {
            if (event.target.closest('[data-sidebar-toggle]')) {
                body.classList.toggle('rk-sidebar-open');
                return;
            }

            if (event.target.closest('.rk-backdrop')) {
                body.classList.remove('rk-sidebar-open');
            }
        });
    }

    /**
     * Image preview for the menu upload field.
     */
    function initImagePreview() {
        document.addEventListener('change', function (event) {
            var input = event.target.closest('[data-image-input]');

            if (!input || !input.files || !input.files[0]) {
                return;
            }

            var preview = document.querySelector(input.getAttribute('data-image-preview'));

            if (preview) {
                preview.src = URL.createObjectURL(input.files[0]);
                preview.classList.remove('d-none');
            }
        });
    }

    /**
     * Status dropdowns submit their own form on change.
     */
    function initAutoSubmit() {
        document.addEventListener('change', function (event) {
            var select = event.target.closest('[data-auto-submit]');

            if (select && select.form) {
                select.form.requestSubmit ? select.form.requestSubmit() : select.form.submit();
            }
        });
    }

    /**
     * Kitchen and till boards reload themselves so new orders appear without
     * anyone pressing refresh.
     */
    function initAutoRefresh() {
        var target = document.querySelector('[data-auto-refresh]');

        if (!target) {
            return;
        }

        var seconds = parseInt(target.getAttribute('data-auto-refresh'), 10);

        if (!seconds || seconds < 5) {
            return;
        }

        window.setTimeout(function () {
            if (!document.hidden) {
                window.location.reload();
            }
        }, seconds * 1000);
    }

    document.addEventListener('DOMContentLoaded', function () {
        initDataTables();
        initDeleteConfirmation();
        initActionConfirmation();
        initFlashToast();
        initSidebarToggle();
        initImagePreview();
        initAutoSubmit();
        initAutoRefresh();
    });
})();
