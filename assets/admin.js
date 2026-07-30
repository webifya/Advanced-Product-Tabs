(function ($) {
    'use strict';

    function reindexRows($list) {
        $list.find('.wct-tab-row').each(function (index) {
            $(this).find('[name]').each(function () {
                this.name = this.name.replace(/wct_tabs\[[^\]]+\]/, 'wct_tabs[' + index + ']');
            });
        });
    }

    $(function () {
        var $list = $('.wct-tab-list');
        if (!$list.length) {
            return;
        }

        $list.sortable({
            handle: '.wct-drag',
            items: '.wct-tab-row',
            update: function () {
                reindexRows($list);
            }
        });

        $('.wct-add-tab').on('click', function () {
            var template = wp.template('wct-tab-row');
            $list.append(template({ index: $list.children('.wct-tab-row').length }));
            reindexRows($list);
        });

        $list.on('click', '.wct-remove', function () {
            $(this).closest('.wct-tab-row').remove();
            reindexRows($list);
        });

        $list.on('click', '.wct-toggle', function () {
            $(this).closest('.wct-tab-row').find('.wct-tab-fields').slideToggle(150);
        });

        $list.on('input', '.wct-title', function () {
            var value = $.trim($(this).val());
            $(this).closest('.wct-tab-row').find('.wct-row-title').text(value || 'New Tab');
        });
    });
})(jQuery);
