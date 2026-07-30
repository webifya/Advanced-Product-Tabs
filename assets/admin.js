(function ($) {
    'use strict';

    function reindexRows($list) {
        $list.find('.wct-tab-row').each(function (index) {
            var $row = $(this);

            $row.attr('data-wct-order', index);
            $row.find('[name]').each(function () {
                this.name = this.name.replace(/wct_tabs\[[^\]]+\]/, 'wct_tabs[' + index + ']');
            });

            $row.find('.wct-order-number').text(index + 1);
        });
    }

    function markProductChanged() {
        $('#publish').removeClass('disabled');
        $('#woocommerce-product-data').trigger('woocommerce_variations_loaded');
        $(document.body).trigger('wct_tabs_reordered');
    }

    function collapseRow($row, animate) {
        var $fields = $row.find('> .wct-tab-fields');
        $row.removeClass('is-open').addClass('is-collapsed');
        $row.find('> .wct-tab-row-bar .wct-toggle').attr('aria-expanded', 'false').text('Expand');
        animate ? $fields.stop(true, true).slideUp(140) : $fields.hide();
    }

    function expandRow($row, animate) {
        var $list = $row.closest('.wct-tab-list');

        $list.children('.wct-tab-row').not($row).each(function () {
            collapseRow($(this), animate);
        });

        var $fields = $row.find('> .wct-tab-fields');
        $row.removeClass('is-collapsed').addClass('is-open');
        $row.find('> .wct-tab-row-bar .wct-toggle').attr('aria-expanded', 'true').text('Collapse');
        animate ? $fields.stop(true, true).slideDown(140) : $fields.show();
    }

    $(function () {
        var $list = $('.wct-tab-list');

        if (!$list.length) {
            return;
        }

        reindexRows($list);

        $list.children('.wct-tab-row').each(function (index) {
            if (index === 0) {
                expandRow($(this), false);
            } else {
                collapseRow($(this), false);
            }
        });

        $list.sortable({
            handle: '.wct-drag',
            items: '> .wct-tab-row',
            axis: 'y',
            tolerance: 'pointer',
            distance: 4,
            cursor: 'grabbing',
            placeholder: 'wct-sort-placeholder',
            forcePlaceholderSize: true,
            helper: function (event, item) {
                var $helper = item.clone();
                $helper.width(item.outerWidth());
                return $helper;
            },
            start: function (event, ui) {
                ui.item.addClass('wct-is-dragging');
                ui.placeholder.height(ui.item.outerHeight());
            },
            stop: function (event, ui) {
                ui.item.removeClass('wct-is-dragging');
                reindexRows($list);
                markProductChanged();
            },
            update: function () {
                reindexRows($list);
            }
        });

        $('.wct-add-tab').on('click', function () {
            var template = wp.template('wct-tab-row');
            var $row = $(template({ index: $list.children('.wct-tab-row').length }));
            $list.append($row);
            reindexRows($list);
            expandRow($row, true);
            markProductChanged();
        });

        $list.on('click', '.wct-remove', function () {
            var $row = $(this).closest('.wct-tab-row');
            var wasOpen = $row.hasClass('is-open');
            $row.remove();
            reindexRows($list);
            if (wasOpen && $list.children('.wct-tab-row').length) {
                expandRow($list.children('.wct-tab-row').first(), true);
            }
            markProductChanged();
        });

        $list.on('click', '.wct-toggle, .wct-row-title', function (event) {
            if ($(event.target).closest('.wct-remove, .wct-drag').length) {
                return;
            }

            var $row = $(this).closest('.wct-tab-row');
            if ($row.hasClass('is-open')) {
                collapseRow($row, true);
            } else {
                expandRow($row, true);
            }
        });

        $list.on('input change', 'input, textarea, select', function () {
            markProductChanged();
        });

        $list.on('input', '.wct-title', function () {
            var value = $.trim($(this).val());
            $(this).closest('.wct-tab-row').find('.wct-row-title').text(value || 'New Tab');
        });
    });
})(jQuery);