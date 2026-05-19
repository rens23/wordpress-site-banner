(function () {
    'use strict';

    if (typeof window.siteBannerParams !== 'object' || !window.siteBannerParams) {
        return;
    }

    var banners = window.siteBannerParams.banners || [];

    function setCookie(name, value, expiration) {
        var date;
        var trimmed = (expiration || '').toString().trim();
        if (trimmed === '' || trimmed === '0' || !isNaN(parseFloat(trimmed))) {
            var days = parseFloat(trimmed) || 0;
            date = new Date();
            date.setTime(date.getTime() + (days * 86400000));
        } else {
            date = new Date(trimmed);
            if (isNaN(date.getTime())) {
                date = new Date();
                date.setTime(date.getTime() + 86400000);
            }
        }
        document.cookie = name + '=' + value + ';expires=' + date.toUTCString() + ';path=/';
    }

    function getCookie(name) {
        var prefix = name + '=';
        var parts = decodeURIComponent(document.cookie).split(';');
        for (var i = 0; i < parts.length; i++) {
            var part = parts[i].replace(/^\s+/, '');
            if (part.indexOf(prefix) === 0) {
                return part.substring(prefix.length);
            }
        }
        return '';
    }

    function attachClickTracking(wrapper, suffix) {
        var endpoint = (window.siteBannerParams && window.siteBannerParams.click_tracking_endpoint) || '';
        wrapper.addEventListener('click', function (e) {
            var a = e.target && (e.target.closest ? e.target.closest('a') : null);
            if (!a || !wrapper.contains(a)) return;
            var detail = {
                bannerSuffix: suffix,
                href: a.href,
                text: (a.textContent || '').trim(),
            };
            try {
                document.dispatchEvent(new CustomEvent('siteBanner:linkClick', { detail: detail }));
            } catch (err) { /* ignore */ }
            if (endpoint && typeof fetch === 'function') {
                try {
                    fetch(endpoint, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(detail),
                        keepalive: true,
                    });
                } catch (err) { /* ignore */ }
            }
        });
    }

    function renderBanner(banner) {
        var suffix = banner.suffix;
        var bannerId = 'site-banner' + suffix;
        var textCls = 'site-banner-text' + suffix;
        var buttonCls = 'site-banner-button' + suffix;
        var scrollCls = 'site-banner-scrolling' + suffix;
        var cookieName = 'sitebannerclosed' + suffix;

        // If a server-rendered copy already exists (wp_body_open path), don't double-render.
        if (document.getElementById(bannerId)) {
            return;
        }

        // Respect a previously-set close cookie.
        if (banner.close_button_enabled && getCookie(cookieName) === 'true') {
            // Refresh the cookie with current expiration in case it was changed.
            setCookie(cookieName, 'true', banner.close_button_expiration);
            return;
        }

        var selector = banner.insert_inside_element || banner.prepend_element || 'body';
        var target;
        try {
            target = document.querySelector(selector);
        } catch (e) {
            target = null;
        }
        if (!target) {
            target = document.body;
        }

        var wrapper = document.createElement('div');
        wrapper.id = bannerId;
        wrapper.className = bannerId;

        var textWrap = document.createElement('div');
        textWrap.className = textCls;
        var span = document.createElement('span');
        // banner.text is sanitized server-side with wp_kses_post.
        span.innerHTML = banner.text;
        textWrap.appendChild(span);
        wrapper.appendChild(textWrap);

        if (banner.cta_text && banner.cta_url) {
            var cta = document.createElement('a');
            cta.className = 'site-banner-cta' + suffix
                + (banner.cta_position === 'block' ? ' site-banner-cta-block' : '');
            cta.href = banner.cta_url;
            cta.textContent = banner.cta_text;
            if (banner.cta_new_tab) {
                cta.target = '_blank';
                cta.rel = 'noopener';
            }
            if (banner.cta_bg_color) cta.style.background = banner.cta_bg_color;
            if (banner.cta_text_color) cta.style.color = banner.cta_text_color;
            wrapper.appendChild(cta);
        }

        if (banner.close_button_enabled) {
            var btn = document.createElement('button');
            btn.id = 'site-banner-close-button' + suffix;
            btn.className = buttonCls;
            btn.setAttribute('aria-label', 'Close');
            btn.innerHTML = '&#x2715;';
            btn.addEventListener('click', function () {
                wrapper.parentNode && wrapper.parentNode.removeChild(wrapper);
                if (!banner.keep_site_css) {
                    var cssEl = document.getElementById('site-banner-site-css' + suffix);
                    if (cssEl) cssEl.parentNode.removeChild(cssEl);
                }
                if (!banner.keep_site_js) {
                    var jsEl = document.getElementById('site-banner-site-js' + suffix);
                    if (jsEl) jsEl.parentNode.removeChild(jsEl);
                }
                setCookie(cookieName, 'true', banner.close_button_expiration);
            });
            wrapper.appendChild(btn);
        }

        target.insertBefore(wrapper, target.firstChild);

        if (banner.click_tracking_enabled) {
            attachClickTracking(wrapper, suffix);
        }

        // Compensate for themes that put padding on <body>.
        var bodyStyle = window.getComputedStyle(document.body);
        if (bodyStyle.paddingLeft !== '0px') {
            wrapper.style.marginLeft = '-' + bodyStyle.paddingLeft;
            wrapper.style.paddingLeft = bodyStyle.paddingLeft;
        }
        if (bodyStyle.paddingRight !== '0px') {
            wrapper.style.marginRight = '-' + bodyStyle.paddingRight;
            wrapper.style.paddingRight = bodyStyle.paddingRight;
        }

        // Toggle scrolling class once the viewport has scrolled past the banner.
        function onScroll() {
            var threshold = wrapper.offsetHeight;
            if ((window.scrollY || window.pageYOffset) > threshold) {
                wrapper.classList.add(scrollCls);
            } else {
                wrapper.classList.remove(scrollCls);
            }
        }
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    function init() {
        for (var i = 0; i < banners.length; i++) {
            renderBanner(banners[i]);
        }
        if (window.siteBannerParams && window.siteBannerParams.debug_mode) {
            // eslint-disable-next-line no-console
            console.log('[Site Banner]', window.siteBannerParams);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
