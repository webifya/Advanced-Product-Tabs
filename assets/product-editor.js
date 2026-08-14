(function ($) {
    'use strict';

    var editorCounter = 0;

    function syncEditor(editorId) {
        if (window.tinymce && tinymce.get(editorId)) {
            tinymce.get(editorId).save();
        }
    }

    function syncAllEditors() {
        $('.tabora-rich-editor').each(function () {
            syncEditor(this.id);
        });
    }

    function initializeEditor($textarea) {
        if ($textarea.hasClass('tabora-rich-editor')) {
            return;
        }

        editorCounter += 1;
        var editorId = 'tabora_tab_editor_' + editorCounter;
        $textarea.attr('id', editorId).addClass('tabora-rich-editor');

        var $toolbar = $('<div class="tabora-editor-media-row"></div>');
        var $mediaButton = $('<button type="button" class="button tabora-add-media"><span class="dashicons dashicons-admin-media"></span> Add Media</button>');
        $mediaButton.attr('data-editor', editorId);
        $toolbar.append($mediaButton);
        $textarea.before($toolbar);

        if (wp.editor && wp.editor.initialize) {
            wp.editor.initialize(editorId, {
                tinymce: {
                    wpautop: true,
                    toolbar1: 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,undo,redo',
                    toolbar2: 'strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,wp_more,fullscreen'
                },
                quicktags: true,
                mediaButtons: false
            });
        }
    }

    function initializeAllEditors(context) {
        $(context || document).find('.tabora-content-field textarea').each(function () {
            initializeEditor($(this));
        });
    }

    function openMediaFrame(editorId) {
        var frame = wp.media({
            title: taboraEditorSettings.mediaTitle,
            button: { text: taboraEditorSettings.mediaButton },
            multiple: false
        });

        frame.on('select', function () {
            var attachment = frame.state().get('selection').first().toJSON();
            var html;

            if (attachment.type === 'image') {
                html = '<img src="' + attachment.url + '" alt="' + (attachment.alt || attachment.title || '') + '">';
            } else {
                html = '<a href="' + attachment.url + '">' + (attachment.title || attachment.filename) + '</a>';
            }

            if (window.tinymce && tinymce.get(editorId) && !tinymce.get(editorId).isHidden()) {
                tinymce.get(editorId).execCommand('mceInsertContent', false, html);
            } else if (window.QTags) {
                QTags.insertContent(html);
            } else {
                var $textarea = $('#' + editorId);
                $textarea.val($textarea.val() + html).trigger('change');
            }
        });

        frame.open();
    }

    $(function () {
        initializeAllEditors(document);

        $(document).on('click', '.tabora-add-media', function (event) {
            event.preventDefault();
            openMediaFrame($(this).data('editor'));
        });

        $(document).on('click', '.tabora-add-tab', function () {
            window.setTimeout(function () {
                initializeAllEditors($('.tabora-tab-list'));
            }, 50);
        });

        $('.tabora-tab-list').on('sortstart', function () {
            syncAllEditors();
        });

        $('#post').on('submit', function () {
            syncAllEditors();
        });

        $(document).on('woocommerce-product-type-change', function () {
            syncAllEditors();
        });
    });
})(jQuery);
