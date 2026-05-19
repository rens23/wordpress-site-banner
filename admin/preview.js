(function () {
    'use strict';

    if (typeof window.siteBannerPreview !== 'object' || !window.siteBannerPreview) {
        return;
    }

    var num = parseInt(window.siteBannerPreview.numBanners, 10) || 1;

    var purifyConfig = {
        ALLOWED_TAGS: ['a', 'b', 'strong', 'i', 'em', 'u', 'br', 'span', 'small'],
        ALLOWED_ATTR: ['href', 'target', 'rel', 'style', 'class'],
    };

    var hrefRegex = /href\s*=\s*['"](?!https?:|mailto:|tel:|\/|#)([^'"]+)['"]/gi;

    function sanitize(html) {
        var normalized = html.replace(hrefRegex, 'href="https://$1"');
        return typeof DOMPurify !== 'undefined'
            ? DOMPurify.sanitize(normalized, purifyConfig)
            : normalized;
    }

    function getTextInput(suffix) {
        // Rich editor stores into a hidden textarea named site_banner_text{suffix}.
        return document.getElementById('site_banner_text' + suffix);
    }

    function bindCTAPreview(suffix) {
        var preview     = document.getElementById('sb-preview' + suffix);
        var textInput   = document.getElementById('site_banner_cta_text' + suffix);
        var urlInput    = document.getElementById('site_banner_cta_url' + suffix);
        var bgInput     = document.getElementById('site_banner_cta_bg_color' + suffix);
        var colorInput  = document.getElementById('site_banner_cta_text_color' + suffix);
        var bgPicker    = document.getElementById('site_banner_cta_bg_color' + suffix + '_picker');
        var colorPicker = document.getElementById('site_banner_cta_text_color' + suffix + '_picker');
        if (!preview || !textInput) return;

        // Sync each colour picker into its sibling text input (saved via name attribute).
        function syncPicker(picker, input) {
            if (!picker || !input) return;
            picker.addEventListener('input', function () {
                input.value = picker.value;
                input.dispatchEvent(new Event('input', { bubbles: true }));
            });
        }
        syncPicker(bgPicker, bgInput);
        syncPicker(colorPicker, colorInput);

        var ctaId = 'sb-preview-cta' + suffix;
        function refresh() {
            var existing = document.getElementById(ctaId);
            if (existing) existing.parentNode.removeChild(existing);
            var text = textInput.value.trim();
            if (!text) return;
            var cta = document.createElement('a');
            cta.id = ctaId;
            cta.className = 'site-banner-cta' + suffix;
            cta.href = (urlInput && urlInput.value) || '#';
            cta.textContent = text;
            if (bgInput && bgInput.value)       cta.style.background = bgInput.value;
            if (colorInput && colorInput.value) cta.style.color      = colorInput.value;
            // Layout: inline (default) or block (under text).
            var posRadios = document.querySelectorAll('input[type="radio"][name="site_banner_cta_position' + suffix + '"]');
            var pos = 'inline';
            for (var i = 0; i < posRadios.length; i++) {
                if (posRadios[i].checked) { pos = posRadios[i].value; break; }
            }
            if (pos === 'block') {
                cta.classList.add('site-banner-cta-block');
            }
            preview.appendChild(cta);
        }
        [textInput, urlInput, bgInput, colorInput].forEach(function (i) {
            if (i) i.addEventListener('input', refresh);
        });
        // Also refresh when the position radios change.
        var posRadios = document.querySelectorAll('input[type="radio"][name="site_banner_cta_position' + suffix + '"]');
        for (var k = 0; k < posRadios.length; k++) {
            posRadios[k].addEventListener('change', refresh);
        }
        refresh();
    }

    function bindBanner(i) {
        var suffix = '_' + i;
        var textInput = getTextInput(suffix);
        var preview = document.getElementById('sb-preview' + suffix);
        var previewText = document.getElementById('sb-preview-text' + suffix);
        if (!preview || !previewText) return;

        function refreshText() {
            var value = textInput ? textInput.value : '';
            previewText.innerHTML = value !== ''
                ? '<span>' + sanitize(value) + '</span>'
                : '<span>This is what your banner will look like with a <a href="/">link</a>.</span>';
        }

        if (textInput) {
            textInput.addEventListener('input', refreshText);
            // TinyMCE syncs into the underlying textarea on change; mirror that.
            if (window.tinymce) {
                window.tinymce.on('AddEditor', function (e) {
                    if (e.editor.id !== textInput.id) return;
                    e.editor.on('change keyup input undo redo', function () {
                        e.editor.save();
                        refreshText();
                    });
                });
            }
        }
        refreshText();

        function bindColor(optionKey, cssProperty, target, fallback) {
            var input = document.getElementById(optionKey + suffix);
            var picker = document.getElementById(optionKey + suffix + '_picker');
            if (!input) return;
            function apply() {
                target.style[cssProperty] = input.value || fallback;
                if (picker) picker.value = input.value || fallback;
            }
            input.addEventListener('input', apply);
            if (picker) {
                picker.addEventListener('input', function () {
                    input.value = picker.value;
                    apply();
                });
            }
            apply();
        }

        bindColor('site_banner_color',      'background', preview,     '#024985');
        bindColor('site_banner_text_color', 'color',      previewText, '#ffffff');

        // Link color via injected stylesheet (per-suffix to scope correctly).
        var styleEl = document.createElement('style');
        styleEl.id = 'sb-preview-link-color' + suffix;
        document.head.appendChild(styleEl);
        var linkInput = document.getElementById('site_banner_link_color' + suffix);
        var linkPicker = document.getElementById('site_banner_link_color' + suffix + '_picker');
        function applyLink() {
            var v = (linkInput && linkInput.value) || '#f16521';
            styleEl.textContent = '#sb-preview' + suffix + ' .site-banner-text' + suffix + ' a { color:' + v + ' }';
            if (linkPicker) linkPicker.value = v;
        }
        if (linkInput) {
            linkInput.addEventListener('input', applyLink);
            if (linkPicker) {
                linkPicker.addEventListener('input', function () { linkInput.value = linkPicker.value; applyLink(); });
            }
        }
        applyLink();

        // Font size.
        var fontInput = document.getElementById('site_banner_font_size' + suffix);
        if (fontInput) {
            fontInput.addEventListener('input', function () {
                previewText.style.fontSize = fontInput.value || '';
            });
        }
    }

    function bindPageChecklists() {
        var lists = document.querySelectorAll('.sb-page-checklist');
        for (var i = 0; i < lists.length; i++) {
            (function (list) {
                var suffix = list.getAttribute('data-suffix');
                var hidden = document.getElementById('site_banner_disabled_pages' + suffix);
                if (!hidden) return;
                function sync() {
                    var ids = [];
                    var cbs = list.querySelectorAll('.sb-disabled-page-cb');
                    for (var j = 0; j < cbs.length; j++) {
                        if (cbs[j].checked) ids.push(cbs[j].value);
                    }
                    hidden.value = ids.join(',');
                }
                list.addEventListener('change', sync);
                sync();
            })(lists[i]);
        }
    }

    function bindSelector() {
        var selector = document.getElementById('sb_banner_selector');
        if (!selector) return;
        function show(suffix) {
            var sections = document.querySelectorAll('.sb-banner-section');
            for (var i = 0; i < sections.length; i++) {
                sections[i].style.display = sections[i].getAttribute('data-suffix') === suffix ? '' : 'none';
            }
            var previews = document.querySelectorAll('.sb-preview-outer');
            for (var j = 0; j < previews.length; j++) {
                previews[j].style.display = previews[j].getAttribute('data-suffix') === suffix ? '' : 'none';
            }
        }
        selector.addEventListener('change', function () { show(selector.value); });
        show(selector.value);
    }

    function bindPresetButtons() {
        var btns = document.querySelectorAll('.sb-preset-btn');
        function setInput(id, value) {
            var input = document.getElementById(id);
            if (!input) return;
            input.value = value;
            input.dispatchEvent(new Event('input', { bubbles: true }));
        }
        for (var i = 0; i < btns.length; i++) {
            btns[i].addEventListener('click', function (e) {
                var b = e.currentTarget;
                var suffix = b.getAttribute('data-suffix');
                setInput('site_banner_color' + suffix,       b.getAttribute('data-bg'));
                setInput('site_banner_text_color' + suffix,  b.getAttribute('data-text'));
                setInput('site_banner_link_color' + suffix,  b.getAttribute('data-link'));
                setInput('site_banner_close_color' + suffix, b.getAttribute('data-close'));
            });
        }
    }

    function bindTargetRoles() {
        var containers = document.querySelectorAll('.sb-role-targeting');
        for (var i = 0; i < containers.length; i++) {
            (function (container) {
                var suffix = container.getAttribute('data-suffix');
                var hidden = document.getElementById('site_banner_visibility_roles' + suffix);
                if (!hidden) return;
                function sync() {
                    var roles = [];
                    var cbs = container.querySelectorAll('.sb-target-role-cb');
                    for (var j = 0; j < cbs.length; j++) {
                        if (cbs[j].checked) roles.push(cbs[j].value);
                    }
                    hidden.value = roles.join(',');
                }
                container.addEventListener('change', sync);

                var radios = document.querySelectorAll('input[type="radio"][name="site_banner_visibility' + suffix + '"]');
                function applyVisibility() {
                    var v = '';
                    for (var k = 0; k < radios.length; k++) {
                        if (radios[k].checked) { v = radios[k].value; break; }
                    }
                    container.style.display = v === 'specific_roles' ? '' : 'none';
                }
                for (var r = 0; r < radios.length; r++) {
                    radios[r].addEventListener('change', applyVisibility);
                }
                applyVisibility();
                sync();
            })(containers[i]);
        }
    }

    function bindRolePermissions() {
        var hidden = document.getElementById('site_banner_role_permissions');
        if (!hidden) return;
        var cbs = document.querySelectorAll('.sb-role-cb');
        function sync() {
            var roles = [];
            for (var i = 0; i < cbs.length; i++) {
                if (cbs[i].checked) roles.push(cbs[i].value);
            }
            hidden.value = roles.join(',');
        }
        for (var i = 0; i < cbs.length; i++) {
            cbs[i].addEventListener('change', sync);
        }
        sync();
    }

    function init() {
        for (var i = 1; i <= num; i++) {
            bindBanner(i);
        }
        for (var k = 1; k <= num; k++) {
            bindCTAPreview('_' + k);
        }
        bindPageChecklists();
        bindTargetRoles();
        bindRolePermissions();
        bindPresetButtons();
        bindSelector();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
