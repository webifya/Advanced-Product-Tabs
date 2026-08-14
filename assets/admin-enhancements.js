(function ($) {
    'use strict';

    var dashicons = [
        'admin-appearance','admin-collapse','admin-comments','admin-customizer','admin-generic','admin-home','admin-links','admin-media','admin-network','admin-page','admin-plugins','admin-post','admin-settings','admin-site','admin-tools','admin-users',
        'align-center','align-left','align-none','align-right','analytics','archive','arrow-down','arrow-down-alt','arrow-down-alt2','arrow-left','arrow-left-alt','arrow-left-alt2','arrow-right','arrow-right-alt','arrow-right-alt2','arrow-up','arrow-up-alt','arrow-up-alt2',
        'awards','backup','bell','book','book-alt','buddicons-activity','buddicons-bbpress-logo','buddicons-buddypress-logo','buddicons-community','buddicons-forums','buddicons-friends','buddicons-groups','buddicons-pm','buddicons-replies','buddicons-topics','buddicons-tracking',
        'building','businessman','businessperson','businesswoman','button','calendar','calendar-alt','camera','cart','category','chart-area','chart-bar','chart-line','chart-pie','clipboard','clock','cloud','controls-back','controls-forward','controls-pause','controls-play','controls-repeat','controls-skipback','controls-skipforward','controls-volumeoff','controls-volumeon',
        'database','desktop','dismiss','download','edit','editor-aligncenter','editor-alignleft','editor-alignright','editor-bold','editor-break','editor-code','editor-contract','editor-customchar','editor-expand','editor-help','editor-indent','editor-insertmore','editor-italic','editor-justify','editor-kitchensink','editor-ltr','editor-ol','editor-ol-rtl','editor-outdent','editor-paragraph','editor-paste-text','editor-paste-word','editor-quote','editor-removeformatting','editor-rtl','editor-spellcheck','editor-strikethrough','editor-table','editor-textcolor','editor-ul','editor-underline','editor-unlink','editor-video',
        'email','email-alt','email-alt2','excerpt-view','external','facebook','facebook-alt','feedback','filter','flag','format-aside','format-audio','format-chat','format-gallery','format-image','format-quote','format-status','format-video','forms','google','grid-view','groups','hammer','heart','hidden','id','id-alt','image-crop','image-filter','image-flip-horizontal','image-flip-vertical','image-rotate','image-rotate-left','image-rotate-right','images-alt','images-alt2','index-card','info','insert','instagram','laptop','layout','leftright','lightbulb','list-view','location','location-alt','lock','marker','media-archive','media-audio','media-code','media-default','media-document','media-interactive','media-spreadsheet','media-text','media-video','megaphone','menu','menu-alt','menu-alt2','menu-alt3','microphone','migrate','minus','money-alt','nametag','networking','no','no-alt','open-folder','palmtree','paperclip','performance','pets','phone','playlist-audio','playlist-video','plus','plus-alt','portfolio','post-status','pressthis','printer','privacy','products','randomize','reddit','redo','remove','rest-api','rss','schedule','screenoptions','search','share','share-alt','share-alt2','shield','shield-alt','shortcode','slides','smartphone','smiley','sort','sos','star-empty','star-filled','star-half','sticky','store','superhero','superhero-alt','table-col-after','table-col-before','table-col-delete','table-row-after','table-row-before','table-row-delete','tablet','tag','testimonial','text','thumbs-down','thumbs-up','tickets','tickets-alt','translation','trash','twitter','undo','universal-access','universal-access-alt','unlock','update','update-alt','upload','vault','video-alt','video-alt2','video-alt3','visibility','warning','welcome-add-page','welcome-comments','welcome-learn-more','welcome-view-site','wordpress','wordpress-alt','yes','yes-alt','youtube'
    ];

    function escapeHtml(value) {
        return $('<div>').text(value || '').html();
    }

    function fieldIndex($row) {
        var name = $row.find('input.tabora-title').attr('name') || '';
        var match = name.match(/tabora_tabs\[([^\]]+)\]/);
        return match ? match[1] : null;
    }

    function iconButton(icon) {
        var selected = icon ? '<span class="dashicons dashicons-' + escapeHtml(icon.replace(/^dashicons-/, '')) + '"></span>' : '<span class="dashicons dashicons-plus-alt2"></span>';
        return '<button type="button" class="button tabora-open-icon-picker">' + selected + '<span class="tabora-icon-button-label">Choose icon</span></button>';
    }

    function addFields($row) {
        if ($row.data('taboraEnhanced')) {
            return;
        }

        var index = fieldIndex($row);
        if (index === null) {
            return;
        }

        var extras = window.taboraTabExtras && window.taboraTabExtras[index] ? window.taboraTabExtras[index] : {};
        var visibility = extras.visibility || 'all';
        var icon = extras.icon || '';
        var html = '<div class="tabora-extra-fields">' +
            '<div class="tabora-extra-field tabora-icon-field"><label>Icon</label><div class="tabora-icon-control">' +
            '<input type="hidden" class="tabora-icon-value" name="tabora_tabs[' + index + '][icon]" value="' + escapeHtml(icon) + '">' +
            iconButton(icon) +
            '<button type="button" class="button-link-delete tabora-clear-icon"' + (icon ? '' : ' hidden') + '>Remove icon</button></div></div>' +
            '<div class="tabora-extra-field"><label>Custom CSS class</label><input type="text" name="tabora_tabs[' + index + '][css_class]" value="' + escapeHtml(extras.css_class || '') + '" placeholder="example-class"></div>' +
            '<div class="tabora-extra-field tabora-visibility-field"><label>Visibility</label><select name="tabora_tabs[' + index + '][visibility]">' +
            '<option value="all"' + (visibility === 'all' ? ' selected' : '') + '>Everyone</option>' +
            '<option value="logged_in"' + (visibility === 'logged_in' ? ' selected' : '') + '>Logged-in users</option>' +
            '<option value="logged_out"' + (visibility === 'logged_out' ? ' selected' : '') + '>Guests only</option>' +
            '</select></div></div>';

        $row.find('.tabora-tab-fields').append(html);
        $row.data('taboraEnhanced', true);
    }

    function modalHtml() {
        var items = dashicons.map(function (icon) {
            return '<button type="button" class="tabora-icon-option" data-icon="dashicons-' + icon + '" data-search="' + icon.replace(/-/g, ' ') + '" title="' + icon + '"><span class="dashicons dashicons-' + icon + '"></span><span>' + icon + '</span></button>';
        }).join('');

        return '<div class="tabora-icon-modal" hidden><div class="tabora-icon-backdrop"></div><div class="tabora-icon-dialog" role="dialog" aria-modal="true" aria-labelledby="tabora-icon-title">' +
            '<div class="tabora-icon-modal-header"><div><h2 id="tabora-icon-title">Choose an icon</h2><p>Search and select any available WordPress Dashicon.</p></div><button type="button" class="tabora-icon-close" aria-label="Close"><span class="dashicons dashicons-no-alt"></span></button></div>' +
            '<div class="tabora-icon-search-wrap"><span class="dashicons dashicons-search"></span><input type="search" class="tabora-icon-search" placeholder="Search icons…"></div>' +
            '<div class="tabora-icon-grid">' + items + '</div><div class="tabora-icon-empty" hidden>No icons found.</div></div></div>';
    }

    function enhanceAll() {
        $('.tabora-tab-row').each(function () {
            addFields($(this));
        });
    }

    $(function () {
        var $activeRow = null;
        $('body').append(modalHtml());
        var $modal = $('.tabora-icon-modal');

        enhanceAll();

        $(document).on('click', '.tabora-add-tab', function () {
            window.setTimeout(enhanceAll, 30);
        });

        $(document).on('click', '.tabora-open-icon-picker', function () {
            $activeRow = $(this).closest('.tabora-tab-row');
            $modal.removeAttr('hidden').addClass('is-open');
            $('body').addClass('tabora-icon-modal-open');
            $modal.find('.tabora-icon-search').val('').trigger('input').focus();
        });

        function closeModal() {
            $modal.attr('hidden', true).removeClass('is-open');
            $('body').removeClass('tabora-icon-modal-open');
            $activeRow = null;
        }

        $(document).on('click', '.tabora-icon-close, .tabora-icon-backdrop', closeModal);
        $(document).on('keydown', function (event) {
            if (event.key === 'Escape' && $modal.hasClass('is-open')) {
                closeModal();
            }
        });

        $(document).on('input', '.tabora-icon-search', function () {
            var query = $.trim($(this).val().toLowerCase());
            var visible = 0;
            $modal.find('.tabora-icon-option').each(function () {
                var match = !query || ($(this).data('search') || '').indexOf(query) !== -1;
                $(this).toggle(match);
                if (match) visible++;
            });
            $modal.find('.tabora-icon-empty').prop('hidden', visible !== 0);
        });

        $(document).on('click', '.tabora-icon-option', function () {
            if (!$activeRow) return;
            var icon = $(this).data('icon');
            var $field = $activeRow.find('.tabora-icon-field');
            $field.find('.tabora-icon-value').val(icon).trigger('change');
            $field.find('.tabora-open-icon-picker .dashicons').attr('class', 'dashicons ' + icon);
            $field.find('.tabora-clear-icon').removeAttr('hidden');
            closeModal();
        });

        $(document).on('click', '.tabora-clear-icon', function () {
            var $field = $(this).closest('.tabora-icon-field');
            $field.find('.tabora-icon-value').val('').trigger('change');
            $field.find('.tabora-open-icon-picker .dashicons').attr('class', 'dashicons dashicons-plus-alt2');
            $(this).attr('hidden', true);
        });
    });
})(jQuery);