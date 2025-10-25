(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var i18n = (window.InitRecentCommentsShortcodeBuilder && window.InitRecentCommentsShortcodeBuilder.i18n) || {};
        var t = function (key, fallback) {
            return i18n[key] || fallback;
        };

        var target = document.querySelector('[data-plugin="init-recent-comments"]');
        if (!target) return;

        var button = {
            label: t('init_recent_comments', 'Init Recent Comments'),
            shortcode: 'init_recent_comments',
            attributes: {
                number: {
                    label: t('number', 'Number of Comments'),
                    type: 'number',
                    default: 5
                },
                paged: {
                    label: t('paged', 'Pagination (optional)'),
                    type: 'number',
                    default: ''
                },
                maxheight: {
                    label: t('maxheight', 'Max Height (e.g. 300px)'),
                    type: 'text',
                    default: ''
                },
                theme: {
                    label: t('theme', 'Theme'),
                    type: 'select',
                    options: ['light', 'dark'],
                    default: 'light'
                }
            }
        };

        var panel = renderShortcodeBuilderPanel({
            title: button.label,
            buttons: [
                {
                    label: button.label,
                    dashicon: 'admin-comments',
                    className: 'button-default',
                    onClick: function () {
                        initShortcodeBuilder({
                            shortcode: button.shortcode,
                            config: {
                                label: button.label,
                                attributes: button.attributes
                            }
                        });
                    }
                }
            ]
        });

        target.appendChild(panel);
    });
})();
