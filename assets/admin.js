(function ($) {
    'use strict';

    function getInitialTab($root) {
        var fromInput = ($root.find('#sbkboard_active_tab').val() || '').toString().trim();
        if (fromInput) {
            return fromInput;
        }

        try {
            var url = new URL(window.location.href);
            var queryTab = (url.searchParams.get('tab') || '').trim();
            if (queryTab) {
                return queryTab;
            }
        } catch (err) {
            // Ignore URL API errors in old environments.
        }

        return 'basic';
    }

    function activateTab($root, tab) {
        var $tabs = $root.find('a.nav-tab[data-tab]');
        var $panels = $root.find('.sbkboard-panel[data-tab-panel]');

        if (!$panels.length) {
            return false;
        }

        var targetTab = tab;
        var $target = $panels.filter('[data-tab-panel="' + targetTab + '"]');
        if (!$target.length) {
            $target = $panels.first();
            targetTab = ($target.data('tab-panel') || 'basic').toString();
        }

        $tabs.removeClass('nav-tab-active');
        $tabs.filter('[data-tab="' + targetTab + '"]').addClass('nav-tab-active');

        $panels.removeClass('is-active').hide();
        $target.addClass('is-active').show();

        $root.find('#sbkboard_active_tab, #sbkboard_active_tab_input').val(targetTab);
        return true;
    }

    function toggleDependentFields($root) {
        var skin = ($root.find('#sbkboard_skin_select').val() || '').toString();
        $root.find('.sbkboard-gallery-columns-row').toggle(skin === 'gallery');

        var editorType = ($root.find('#sbkboard_editor_type_select').val() || '').toString();
        $root.find('.sbkboard-editor-html-row').toggle(editorType === 'textarea');
    }

    $(function () {
        $('.sbkboard-admin').each(function () {
            var $root = $(this);
            var initialTab = getInitialTab($root);
            activateTab($root, initialTab);
            toggleDependentFields($root);

            $root.on('change', '#sbkboard_skin_select, #sbkboard_editor_type_select', function () {
                toggleDependentFields($root);
            });
        });

        $(document).on('click', '.sbkboard-delete-board', function (e) {
            var msg = (window.SBKBoardAdmin && SBKBoardAdmin.confirm_delete) || 'Delete this board?';
            if (!window.confirm(msg)) {
                e.preventDefault();
            }
        });
    });

    $(document).on('click', 'a.nav-tab[data-tab]', function (e) {
        var $tab = $(this);
        var $root = $tab.closest('.sbkboard-admin');
        var tab = ($tab.data('tab') || '').toString();

        if (!tab) {
            return;
        }

        var $targetPanel = $root.find('.sbkboard-panel[data-tab-panel="' + tab + '"]');
        if (!$targetPanel.length) {
            // Some tabs (for example backup) render on a dedicated request.
            return;
        }

        e.preventDefault();
        activateTab($root, tab);

        try {
            var url = new URL(window.location.href);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url.toString());
        } catch (err) {
            // Ignore URL API errors in old environments.
        }
    });
}(jQuery));
