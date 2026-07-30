(function ($) {
    'use strict';

    function buildAccordion($wrapper) {
        if ($wrapper.data('wctAccordionReady')) {
            return;
        }

        var $nav = $wrapper.children('.tabs, ul.tabs').first();
        if (!$nav.length) {
            return;
        }

        $nav.find('li').each(function (index) {
            var $item = $(this);
            var $link = $item.find('a').first();
            var target = $link.attr('href');
            var $panel = target ? $wrapper.children(target) : $();

            if (!$panel.length) {
                return;
            }

            var buttonId = 'wct-accordion-' + index + '-' + Math.random().toString(36).slice(2, 8);
            var panelId = $panel.attr('id');
            var $button = $('<button type="button" class="wct-accordion-title" aria-expanded="false"></button>');
            $button.attr({ id: buttonId, 'aria-controls': panelId }).html($link.html());
            $panel.attr({ role: 'region', 'aria-labelledby': buttonId });
            $panel.before($button);

            $button.on('click', function () {
                var isOpen = $(this).attr('aria-expanded') === 'true';
                $wrapper.find('.wct-accordion-title').attr('aria-expanded', 'false');
                $wrapper.children('.woocommerce-Tabs-panel').stop(true, true).slideUp(180);
                if (!isOpen) {
                    $(this).attr('aria-expanded', 'true');
                    $panel.stop(true, true).slideDown(180);
                }
            });
        });

        $wrapper.data('wctAccordionReady', true);
    }

    function updateMode() {
        var config = window.wctFrontend || {};
        var breakpoint = parseInt(config.breakpoint, 10) || 768;

        $('.woocommerce-tabs').each(function () {
            var $wrapper = $(this);
            buildAccordion($wrapper);
            $wrapper.addClass('wct-style-' + (config.style || 'default'));

            if (config.accordion && window.innerWidth <= breakpoint) {
                $wrapper.addClass('wct-accordion-mode');
                var $buttons = $wrapper.find('.wct-accordion-title');
                var $panels = $wrapper.children('.woocommerce-Tabs-panel');
                $panels.hide();
                $buttons.attr('aria-expanded', 'false');
                if (config.openFirst && $buttons.length) {
                    $buttons.first().attr('aria-expanded', 'true');
                    $panels.first().show();
                }
            } else {
                $wrapper.removeClass('wct-accordion-mode');
                $wrapper.find('.wct-accordion-title').attr('aria-expanded', 'false');
                $wrapper.children('.woocommerce-Tabs-panel').show();
            }
        });
    }

    $(updateMode);
    var timer;
    $(window).on('resize', function () {
        clearTimeout(timer);
        timer = setTimeout(updateMode, 120);
    });
})(jQuery);