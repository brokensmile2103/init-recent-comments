(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var i18n = (window.InitRecentCommentsShortcodeBuilder && window.InitRecentCommentsShortcodeBuilder.i18n) || {};
        var t = function (key, fallback) { return i18n[key] || fallback; };

        var target = document.querySelector('[data-plugin="init-recent-comments"]');
        if (!target || typeof renderShortcodeBuilderPanel !== 'function') return;

        var baseAttrs = {
            number: { label: t('number', 'Number'), type: 'number', default: 5 },
            paged: { label: t('paged', 'Pagination'), type: 'number', default: '' },
            maxheight: { label: t('maxheight', 'Max Height'), type: 'text', default: '' },
            theme: { label: t('theme', 'Theme'), type: 'select', options: ['light', 'dark'], default: 'light' }
        };

        var buttons = [
            // Recent Comments
            { shortcode: 'init_recent_comments',  label: 'Recent Comments',  attributes: baseAttrs },

            // Recent Reviews
            { shortcode: 'init_recent_reviews',   label: 'Recent Reviews',   attributes: baseAttrs },

            // User Recent Comments (user_id / login / email)
            {
                shortcode: 'init_user_recent_comments',
                label: 'User Recent Comments',
                attributes: Object.assign({
                    user_id:   { label: 'User ID', type: 'number', default: '' },
                    user_login:{ label: 'User Login', type: 'text', default: '' },
                    user_email:{ label: 'User Email', type: 'text', default: '' }
                }, baseAttrs)
            },

            // User Recent Reviews
            {
                shortcode: 'init_user_recent_reviews',
                label: 'User Recent Reviews',
                attributes: Object.assign({
                    user_id: { label: 'User ID', type: 'number', default: '' }
                }, baseAttrs)
            }
        ];

        var panel = renderShortcodeBuilderPanel({
            title: t('shortcode_builder', 'Shortcodes'),
            buttons: buttons.map(function (btn) {
                return {
                    label: btn.label,
                    onClick: function () {
                        initShortcodeBuilder({
                            shortcode: btn.shortcode,
                            config: { label: btn.label, attributes: btn.attributes }
                        });
                    }
                };
            })
        });

        target.appendChild(panel);
    });
})();
