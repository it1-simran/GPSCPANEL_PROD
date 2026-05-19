/**
 * GPS Control Panel — site-wide toast notifications.
 * Server: set window.__GPS_PAGE_FLASH__ from Blade (see layouts.apps).
 * Client: window.showGpsToast('success'|'error'|'warning'|'info', title, message, { durationMs })
 */
(function (global) {
    'use strict';

    var STACK_ID = 'gps-site-toast-stack';
    var DEFAULT_DURATION = 6500;

    var ICONS = {
        success:
            '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>',
        error:
            '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>',
        warning:
            '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>',
        info:
            '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>'
    };

    var TITLES = {
        success: 'Success',
        error: 'Error',
        warning: 'Attention',
        info: 'Information'
    };

    function escapeHtml(s) {
        if (s == null) return '';
        var d = document.createElement('div');
        d.textContent = String(s);
        return d.innerHTML;
    }

    function ensureStack() {
        var el = document.getElementById(STACK_ID);
        if (el) return el;
        el = document.createElement('div');
        el.id = STACK_ID;
        el.className = 'gps-site-toast-stack';
        el.setAttribute('aria-live', 'polite');
        document.body.appendChild(el);
        return el;
    }

    function normalizeType(t) {
        t = String(t || 'info').toLowerCase();
        if (t === 'danger') return 'error';
        if (t === 'success' || t === 'error' || t === 'warning' || t === 'info') return t;
        return 'info';
    }

    /**
     * @param {string} type success|error|warning|info
     * @param {string} title
     * @param {string} [message]
     * @param {{ durationMs?: number }} [opts]
     */
    function showGpsToast(type, title, message, opts) {
        opts = opts || {};
        type = normalizeType(type);
        var duration = typeof opts.durationMs === 'number' ? opts.durationMs : DEFAULT_DURATION;

        var stack = ensureStack();
        var el = document.createElement('div');
        el.className = 'gps-site-toast gps-site-toast--' + type;
        el.setAttribute('role', 'alert');

        var iconHtml = ICONS[type] || ICONS.info;
        var safeTitle = escapeHtml(title || TITLES[type]);
        var rawMsg = message != null && String(message).trim() !== '' ? String(message) : '';
        var safeMsg = escapeHtml(rawMsg).replace(/\n/g, '<br>');

        var trackHtml =
            duration > 0
                ? '<div class="gps-site-toast__track" aria-hidden="true">' +
                  '<div class="gps-site-toast__progress" style="--gps-toast-duration:' +
                  duration +
                  'ms"></div></div>'
                : '';

        el.innerHTML =
            '<div class="gps-site-toast__main">' +
            '<div class="gps-site-toast__icon-wrap" aria-hidden="true">' +
            iconHtml +
            '</div>' +
            '<div class="gps-site-toast__body">' +
            '<div class="gps-site-toast__title">' +
            safeTitle +
            '</div>' +
            (rawMsg
                ? '<p class="gps-site-toast__msg">' + safeMsg + '</p>'
                : '') +
            '</div>' +
            '<button type="button" class="gps-site-toast__close" aria-label="Dismiss">&times;</button>' +
            '</div>' +
            trackHtml;

        var closeBtn = el.querySelector('.gps-site-toast__close');
        var removeTimer;

        function removeToast() {
            if (removeTimer) clearTimeout(removeTimer);
            var prog = el.querySelector('.gps-site-toast__progress');
            if (prog) {
                prog.style.animation = 'none';
            }
            el.classList.add('gps-site-toast--out');
            setTimeout(function () {
                if (el.parentNode) el.parentNode.removeChild(el);
            }, 240);
        }

        closeBtn.addEventListener('click', removeToast);
        if (duration > 0) {
            removeTimer = setTimeout(removeToast, duration);
        }

        stack.appendChild(el);
        return el;
    }

    function coerceString(v) {
        if (v == null) return '';
        if (typeof v === 'string') return v;
        if (typeof v === 'number' || typeof v === 'boolean') return String(v);
        return '';
    }

    function drainPageFlash() {
        var raw = global.__GPS_PAGE_FLASH__;
        if (!raw || typeof raw !== 'object') return;

        var order = ['success', 'error', 'warning', 'info', 'message', 'status'];
        order.forEach(function (key) {
            var val = raw[key];
            if (val == null || val === '') return;
            var str = coerceString(val);
            if (!str) return;

            var type = key === 'success' ? 'success' : key === 'warning' ? 'warning' : key === 'info' ? 'info' : 'error';
            if (key === 'message' || key === 'status') type = 'info';

            var lines = str.split(/\r?\n/).map(function (l) {
                return l.trim();
            }).filter(Boolean);
            var title = TITLES[type];
            var body = '';
            if (lines.length >= 2) {
                title = lines[0];
                body = lines.slice(1).join('\n');
            } else {
                body = str;
            }

            if (key === 'success' && lines.length < 2) {
                title = TITLES.success;
                body = str;
            }
            if (key === 'error' && lines.length < 2) {
                title = TITLES.error;
                body = str;
            }

            showGpsToast(type, title, body);
        });
    }

    function init() {
        drainPageFlash();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    global.showGpsToast = showGpsToast;
})(window);
