(function ($) {
    'use strict';

    function reindexRows($list) {
        $list.find('.tabora-tab-row').each(function (index) {
            var $row = $(this);

            $row.attr('data-tabora-order', index);
            $row.find('[name]').each(function () {
                this.name = this.name.replace(/tabora_tabs\[[^\]]+\]/, 'tabora_tabs[' + index + ']');
            });

            $row.find('.tabora-order-number').text(index + 1);
        });
    }

    function markProductChanged() {
        $('#publish').removeClass('disabled');
        $('#woocommerce-product-data').trigger('woocommerce_variations_loaded');
        $(document.body).trigger('tabora_tabs_reordered');
    }

    function collapseRow($row, animate) {
        var $fields = $row.find('> .tabora-tab-fields');
        $row.removeClass('is-open').addClass('is-collapsed');
        $row.find('> .tabora-tab-row-bar .tabora-toggle').attr('aria-expanded', 'false').text('Expand');
        animate ? $fields.stop(true, true).slideUp(140) : $fields.hide();
    }

    function expandRow($row, animate) {
        var $list = $row.closest('.tabora-tab-list');

        $list.children('.tabora-tab-row').not($row).each(function () {
            collapseRow($(this), animate);
        });

        var $fields = $row.find('> .tabora-tab-fields');
        $row.removeClass('is-collapsed').addClass('is-open');
        $row.find('> .tabora-tab-row-bar .tabora-toggle').attr('aria-expanded', 'true').text('Collapse');
        animate ? $fields.stop(true, true).slideDown(140) : $fields.show();
    }

    $(function () {
        var $list = $('.tabora-tab-list');

        if (!$list.length) {
            return;
        }

        reindexRows($list);

        $list.children('.tabora-tab-row').each(function (index) {
            if (index === 0) {
                expandRow($(this), false);
            } else {
                collapseRow($(this), false);
            }
        });

        $list.sortable({
            handle: '.tabora-drag',
            items: '> .tabora-tab-row',
            axis: 'y',
            tolerance: 'pointer',
            distance: 4,
            cursor: 'grabbing',
            placeholder: 'tabora-sort-placeholder',
            forcePlaceholderSize: true,
            helper: function (event, item) {
                var $helper = item.clone();
                $helper.width(item.outerWidth());
                return $helper;
            },
            start: function (event, ui) {
                ui.item.addClass('tabora-is-dragging');
                ui.placeholder.height(ui.item.outerHeight());
            },
            stop: function (event, ui) {
                ui.item.removeClass('tabora-is-dragging');
                reindexRows($list);
                markProductChanged();
            },
            update: function () {
                reindexRows($list);
            }
        });

        $('.tabora-add-tab').on('click', function () {
            var template = wp.template('tabora-tab-row');
            var $row = $(template({ index: $list.children('.tabora-tab-row').length }));
            $list.append($row);
            reindexRows($list);
            expandRow($row, true);
            markProductChanged();
        });

        $list.on('click', '.tabora-remove', function () {
            var $row = $(this).closest('.tabora-tab-row');
            var wasOpen = $row.hasClass('is-open');
            $row.remove();
            reindexRows($list);
            if (wasOpen && $list.children('.tabora-tab-row').length) {
                expandRow($list.children('.tabora-tab-row').first(), true);
            }
            markProductChanged();
        });

        $list.on('click', '.tabora-toggle, .tabora-row-title', function (event) {
            if ($(event.target).closest('.tabora-remove, .tabora-drag').length) {
                return;
            }

            var $row = $(this).closest('.tabora-tab-row');
            if ($row.hasClass('is-open')) {
                collapseRow($row, true);
            } else {
                expandRow($row, true);
            }
        });

        $list.on('input change', 'input, textarea, select', function () {
            markProductChanged();
        });

        $list.on('input', '.tabora-title', function () {
            var value = $.trim($(this).val());
            $(this).closest('.tabora-tab-row').find('.tabora-row-title').text(value || 'New Tab');
        });
    });
})(jQuery);