/**
 * GPS Control Panel — site-wide toast + inline notifications.
 * Server: window.__GPS_PAGE_FLASH__ from partials/gps-flash-scripts.blade.php
 * Client: showGpsToast, notifyGps, notifyGpsFromXhr, notifyGpsValidationErrors
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

    function scrollToFeedback(scope) {
        var root = scope && scope.querySelector ? scope : document;
        var target =
            root.querySelector('#alert_msg') ||
            root.querySelector('.success_msg:visible') ||
            root.querySelector('.error_msg:visible') ||
            root.querySelector('#main-content') ||
            document.documentElement;
        if (target && target.scrollIntoView) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function updateInlineAlerts(type, message, opts) {
        opts = opts || {};
        var scope = opts.scope || document;
        type = normalizeType(type);
        var cssClass =
            type === 'success'
                ? 'alert-success'
                : type === 'warning'
                  ? 'alert-warning'
                  : type === 'info'
                    ? 'alert-info'
                    : 'alert-danger';

        if (opts.inline === false) {
            return;
        }

        var msgHtml = escapeHtml(String(message || '')).replace(/\n/g, '<br>');

        if (type === 'success' && scope.querySelector) {
            var successBox = scope.querySelector('.success_msg');
            if (successBox) {
                successBox.innerHTML = msgHtml;
                successBox.style.display = '';
                successBox.classList.remove('d-none');
            }
        }

        if (type !== 'success' && scope.querySelector) {
            var errorBox = scope.querySelector('.error_msg');
            if (errorBox) {
                errorBox.innerHTML = msgHtml;
                errorBox.style.display = '';
                errorBox.classList.remove('d-none');
            }
        }

        var alertHost = scope.querySelector ? scope.querySelector('#alert_msg') : null;
        if (alertHost && message) {
            var existing = alertHost.querySelector('.gps-js-inline-alert');
            if (existing) {
                existing.parentNode.removeChild(existing);
            }
            var alertEl = document.createElement('div');
            alertEl.className = 'col-sm-12 alert ' + cssClass + ' gps-js-inline-alert';
            alertEl.setAttribute('role', 'alert');
            alertEl.innerHTML = msgHtml;
            alertHost.insertBefore(alertEl, alertHost.firstChild);
        }

        if (opts.scroll !== false) {
            scrollToFeedback(scope);
        }
    }

    /**
     * @param {string} type success|error|warning|info
     * @param {string} title
     * @param {string} [message]
     * @param {{ durationMs?: number, inline?: boolean, scroll?: boolean, scope?: ParentNode }} [opts]
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
            (rawMsg ? '<p class="gps-site-toast__msg">' + safeMsg + '</p>' : '') +
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

    /**
     * Unified notification: toast + optional inline alert regions.
     */
    function notifyGps(type, message, title, opts) {
        opts = opts || {};
        type = normalizeType(type);
        var body = message != null ? String(message) : '';
        if (!body.trim()) return;

        showGpsToast(type, title || TITLES[type], body, opts);
        updateInlineAlerts(type, body, opts);
    }

    function notifyGpsSuccess(message, opts) {
        notifyGps('success', message, TITLES.success, opts);
    }

    function notifyGpsError(message, opts) {
        notifyGps('error', message, TITLES.error, opts);
    }

    function notifyGpsWarning(message, opts) {
        notifyGps('warning', message, TITLES.warning, opts);
    }

    function notifyGpsValidationErrors(errors, opts) {
        opts = opts || {};
        var list = [];

        if (Array.isArray(errors)) {
            list = errors.map(String).filter(Boolean);
        } else if (errors && typeof errors === 'object') {
            Object.keys(errors).forEach(function (key) {
                var val = errors[key];
                if (Array.isArray(val)) {
                    val.forEach(function (item) {
                        if (item) list.push(String(item));
                    });
                } else if (val) {
                    list.push(String(val));
                }
            });
        }

        if (!list.length) {
            notifyGpsError('Please correct the highlighted fields.', opts);
            return;
        }

        notifyGpsError(list.join('\n'), 'Validation Error', opts);
    }

    function parseAjaxPayload(payload) {
        if (payload == null || payload === '') return null;
        if (typeof payload === 'object') return payload;
        if (typeof payload === 'string') {
            try {
                return JSON.parse(payload);
            } catch (e) {
                return { message: payload };
            }
        }
        return null;
    }

    function extractSuccessMessage(data) {
        if (!data || typeof data !== 'object') return '';
        return (
            data.success ||
            data.status_message ||
            data.status_msg ||
            data.message ||
            (data.status === 200 || data.status === '200' ? data.msg : '') ||
            ''
        );
    }

    function humanizeTechnicalMessage(message) {
        if (!message || typeof message !== 'string') {
            return 'Something went wrong. Please try again.';
        }

        var trimmed = message.trim();
        if (!trimmed) {
            return 'Something went wrong. Please try again.';
        }

        if (
            trimmed.length > 500 ||
            /<html[\s>]/i.test(trimmed) ||
            /SQLSTATE|foreach\(\)|Stack trace|vendor\\/i.test(trimmed) ||
            /must be of type|Undefined array key|Integrity constraint violation/i.test(trimmed)
        ) {
            return 'We could not complete your request. Please refresh the page, check your entries, and try again.';
        }

        return trimmed;
    }

    function extractErrorMessage(data, fallback) {
        if (!data) return humanizeTechnicalMessage(fallback || 'Something went wrong. Please try again.');
        if (typeof data === 'string') return humanizeTechnicalMessage(data);
        if (typeof data !== 'object') return humanizeTechnicalMessage(fallback || 'Something went wrong. Please try again.');

        if (data.errors) return '';
        return humanizeTechnicalMessage(
            data.error ||
                data.message ||
                data.status_message ||
                data.status_msg ||
                fallback ||
                'Something went wrong. Please try again.'
        );
    }

    function notifyGpsFromXhr(xhr, opts) {
        opts = opts || {};
        var data = parseAjaxPayload(xhr && xhr.responseText);
        if (data && data.errors) {
            notifyGpsValidationErrors(data.errors, opts);
            return;
        }
        var message = extractErrorMessage(data, xhr && xhr.statusText);
        notifyGpsError(message, TITLES.error, opts);
    }

    function notifyGpsAjaxSuccess(response, opts) {
        opts = opts || {};
        var data = parseAjaxPayload(response);
        var message = extractSuccessMessage(data);
        if (!message && typeof response === 'string' && response.trim()) {
            message = response.trim();
        }
        if (message) {
            notifyGpsSuccess(message, opts);
        }
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

            var type =
                key === 'success'
                    ? 'success'
                    : key === 'warning'
                      ? 'warning'
                      : key === 'info'
                        ? 'info'
                        : 'error';
            if (key === 'message' || key === 'status') type = 'info';

            notifyGps(type, str, TITLES[type], { inline: false, scroll: false });
        });

        if (Array.isArray(raw.validation_errors) && raw.validation_errors.length) {
            notifyGpsValidationErrors(raw.validation_errors, { inline: false, scroll: false });
        }
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
    global.notifyGps = notifyGps;
    global.notifyGpsSuccess = notifyGpsSuccess;
    global.notifyGpsError = notifyGpsError;
    global.notifyGpsWarning = notifyGpsWarning;
    global.notifyGpsValidationErrors = notifyGpsValidationErrors;
    global.notifyGpsFromXhr = notifyGpsFromXhr;
    global.notifyGpsAjaxSuccess = notifyGpsAjaxSuccess;
})(window);
