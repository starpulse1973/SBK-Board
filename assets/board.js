/* SBK Board – Front-end JS */
(function ($) {
    'use strict';

    var SBK = window.SBKBoard || {};

    /* ── Helpers ──────────────────────────────────────────── */
    function ajax(action, data, cb) {
        $.post(SBK.ajax_url, $.extend({ action: action, nonce: SBK.nonce }, data), cb, 'json');
    }

    function msg(type, text, $wrap) {
        var $m = $('<div class="sbkboard-msg sbkboard-msg-' + type + '"></div>').text(text);
        $wrap.prepend($m);
        setTimeout(function () { $m.fadeOut(400, function () { $m.remove(); }); }, 4000);
    }

    /* ── List: AJAX load & pagination ────────────────────── */
    $(document).on('click', '.sbkboard-page-link', function (e) {
        e.preventDefault();
        var $wrap = $(this).closest('.sbkboard');
        loadList($wrap, $(this).data('page'));
    });

    $(document).on('submit', '.sbkboard-search', function (e) {
        e.preventDefault();
        var $wrap = $(this).closest('.sbkboard');
        loadList($wrap, 1);
    });

    function loadList($wrap, page) {
        var boardId = $wrap.data('board-id');
        var perPage = parseInt($wrap.data('per-page'), 10) || 0;
        var galleryColumns = parseInt($wrap.data('gallery-columns'), 10) || 0;
        var pageUrl = $wrap.data('page-url') || '';
        var keyword = $wrap.find('[name="sbk_q"]').val() || $wrap.find('[name="keyword"]').val() || '';
        $wrap.find('.sbkboard-list-area').html('<div class="sbkboard-loading">로딩 중...</div>');
        ajax('sbkboard_list', { board_id: boardId, page: page, keyword: keyword, rpp: perPage, gallery_columns: galleryColumns, page_url: pageUrl }, function (res) {
            if (res.success) {
                $wrap.find('.sbkboard-list-area').html(res.data.html);
            }
        });
    }

    /* ── View: password-protected post ───────────────────── */
    $(document).on('submit', '.sbkboard-password-form', function (e) {
        e.preventDefault();
        var $form   = $(this);
        var $wrap   = $form.closest('.sbkboard');
        var postId  = $form.data('post-id');
        var boardId = $form.data('board-id');
        var pw      = $form.find('[name="post_password"]').val();
        ajax('sbkboard_check_password', { post_id: postId, post_password: pw }, function (res) {
            if (res.success) {
                $wrap.find('.sbkboard-post-area').html(res.data.html);
            } else {
                msg('error', res.data || SBK.i18n.enter_password, $form);
            }
        });
    });

    /* ── Vote ─────────────────────────────────────────────── */
    $(document).on('click', '.sbkboard-vote-btn', function () {
        var $btn   = $(this);
        var postId = $btn.data('post-id');
        var type   = $btn.data('type'); // 'up' | 'down'
        if ($btn.hasClass('voted')) return;
        ajax('sbkboard_vote', { post_id: postId, type: type }, function (res) {
            if (res.success) {
                $btn.addClass('voted');
                $btn.closest('.sbkboard-vote-bar').find('.sbkboard-vote-count').text(res.data.total);
            } else {
                alert(res.data || SBK.i18n.error_occurred);
            }
        });
    });

    /* ── Write / Edit form submit ─────────────────────────── */
    $(document).on('submit', '.sbkboard-write-form', function (e) {
        e.preventDefault();
        var $form   = $(this);
        var $wrap   = $form.closest('.sbkboard');
        var action  = $form.data('action') || 'sbkboard_post_save';

        // Sync TinyMCE/Quicktags content into the underlying textarea before FormData capture.
        if (typeof window.tinyMCE !== 'undefined' && window.tinyMCE && typeof window.tinyMCE.triggerSave === 'function') {
            window.tinyMCE.triggerSave();
        }

        var fd      = new FormData($form[0]);
        fd.append('action', action);
        fd.append('nonce', SBK.nonce);
        $.ajax({
            url:         SBK.ajax_url,
            type:        'POST',
            data:        fd,
            processData: false,
            contentType: false,
            dataType:    'json',
            success: function (res) {
                if (res.success) {
                    // Redirect to board list (strip write/edit params)
                    var url = res.data.redirect || window.location.href;
                    url = url.replace(/[?&]sbk_write=[^&]*/g, '').replace(/[?&]sbk_edit=[^&]*/g, '');
                    url = url.replace(/\?&/, '?').replace(/[?&]$/, '');
                    if (url.indexOf('sbk_t=') === -1) {
                        url += (url.indexOf('?') === -1 ? '?' : '&') + 'sbk_t=' + Date.now();
                    }
                    window.location.href = url;
                } else {
                    msg('error', res.data || SBK.i18n.error_occurred, $form);
                }
            },
            error: function () {
                msg('error', SBK.i18n.error_occurred, $form);
            }
        });
    });

    /* ── Comment submit ───────────────────────────────────── */
    $(document).on('submit', '.sbkboard-comment-form', function (e) {
        e.preventDefault();
        var $form  = $(this);
        var $wrap  = $form.closest('.sbkboard-post-wrap');
        var data   = {
            post_id:   $form.data('post-id'),
            parent_id: $form.find('[name="parent_id"]').val() || 0,
            content:   $form.find('[name="content"]').val(),
            author:    $form.find('[name="author"]').val() || '',
            password:  $form.find('[name="password"]').val() || ''
        };
        ajax('sbkboard_comment_add', data, function (res) {
            if (res.success) {
                $wrap.find('.sbkboard-comments-list').html(res.data.html);
                $form.find('[name="content"]').val('');
            } else {
                msg('error', res.data || SBK.i18n.error_occurred, $form);
            }
        });
    });

    /* ── Comment delete ───────────────────────────────────── */
    $(document).on('click', '.sbkboard-comment-delete', function (e) {
        e.preventDefault();
        if (!confirm(SBK.i18n.confirm_delete || 'Delete?')) return;
        var $btn  = $(this);
        var cmtId = $btn.data('comment-id');
        ajax('sbkboard_comment_delete', { comment_id: cmtId }, function (res) {
            if (res.success) {
                $btn.closest('.sbkboard-comment-item').fadeOut(300, function () { $(this).remove(); });
            } else {
                alert(res.data || SBK.i18n.error_occurred);
            }
        });
    });

    /* ── File delete in write form ────────────────────────── */
    $(document).on('click', '.sbkboard-file-delete', function (e) {
        e.preventDefault();
        var $btn   = $(this);
        var fileId = $btn.data('file-id');
        ajax('sbkboard_file_delete', { file_id: fileId }, function (res) {
            if (res.success) {
                $btn.closest('.sbkboard-file-item').remove();
            } else {
                alert(res.data || SBK.i18n.error_occurred);
            }
        });
    });

}(jQuery));
