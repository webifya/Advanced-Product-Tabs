(function ($) {
    'use strict';

    function fieldIndex($row) {
        var name = $row.find('input.wct-title').attr('name') || '';
        var match = name.match(/wct_tabs\[([^\]]+)\]/);
        return match ? match[1] : null;
    }

    function addFields($row) {
        if ($row.data('wctEnhanced')) {
            return;
        }
        var index = fieldIndex($row);
        if (index === null) {
            return;
        }

        var extras = window.wctTabExtras && window.wctTabExtras[index] ? window.wctTabExtras[index] : {};
        var visibility = extras.visibility || 'all';
        var html = '<div class="wct-extra-fields">' +
            '<p class="form-field"><label>Icon or emoji</label><input type="text" name="wct_tabs[' + index + '][icon]" value="' + $('<div>').text(extras.icon || '').html() + '" placeholder="✓"><span class="description">Shown before the tab title.</span></p>' +
            '<p class="form-field"><label>Custom CSS class</label><input type="text" name="wct_tabs[' + index + '][css_class]" value="' + $('<div>').text(extras.css_class || '').html() + '"></p>' +
            '<p class="form-field"><label>Visibility</label><select name="wct_tabs[' + index + '][visibility]">' +
            '<option value="all"' + (visibility === 'all' ? ' selected' : '') + '>Everyone</option>' +
            '<option value="logged_in"' + (visibility === 'logged_in' ? ' selected' : '') + '>Logged-in users</option>' +
            '<option value="logged_out"' + (visibility === 'logged_out' ? ' selected' : '') + '>Guests only</option>' +
            '</select></p></div>';

        $row.find('.wct-tab-fields').append(html);
        $row.data('wctEnhanced', true);
    }

    function enhanceAll() {
        $('.wct-tab-row').each(function () {
            addFields($(this));
        });
    }

    $(enhanceAll);
    $(document).on('click', '.wct-add-tab', function () {
        window.setTimeout(enhanceAll, 20);
    });
})(jQuery);