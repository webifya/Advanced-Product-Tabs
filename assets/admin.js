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

    $(function () {
        var $list = $('.wct-tab-list');

        if (!$list.length) {
            return;
        }

        reindexRows($list);

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
            $list.append(template({ index: $list.children('.wct-tab-row').length }));
            reindexRows($list);
            markProductChanged();
        });

        $list.on('click', '.wct-remove', function () {
            $(this).closest('.wct-tab-row').remove();
            reindexRows($list);
            markProductChanged();
        });

        $list.on('click', '.wct-toggle', function () {
            $(this).closest('.wct-tab-row').find('.wct-tab-fields').slideToggle(150);
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