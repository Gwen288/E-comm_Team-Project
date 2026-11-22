/*
 * CrownCare shared script, loaded on every page (store, account, admin, consultant).
 *
 * - Adds the form token (csrf_token) to every POST form.
 * - Show / hide button on every password field.
 * - Styled notices and confirmations (SweetAlert2) instead of browser pop-ups.
 * - Back links that return to the previous page inside the site.
 * - Forms marked data-ajax are sent without reloading the page, so what the
 *   person typed stays in the fields when there is an error.
 */
(function () {
    'use strict';

    var tokenMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = tokenMeta ? tokenMeta.getAttribute('content') : '';
    var hasSwal = typeof window.Swal !== 'undefined';

    /* ---------- Styled dialogs ---------- */

    var dialog = hasSwal ? Swal.mixin({
        buttonsStyling: false,
        customClass: {
            popup: 'cc-swal',
            title: 'cc-swal-title',
            htmlContainer: 'cc-swal-text',
            confirmButton: 'cc-button cc-button--primary',
            cancelButton: 'cc-button cc-button--outline',
            denyButton: 'cc-button cc-button--outline',
            actions: 'cc-swal-actions'
        }
    }) : null;

    var toast = hasSwal ? Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        showCloseButton: true,
        timer: 4500,
        timerProgressBar: true,
        customClass: { popup: 'cc-toast' }
    }) : null;

    /** Short notice in the corner. type: success, error, info, warning. */
    window.ccNotify = function (message, type) {
        if (!message) return;
        if (toast) {
            toast.fire({ icon: type || 'success', title: message, timer: type === 'error' ? 8000 : 4500 });
        } else {
            window.alert(message);
        }
    };

    /** Notice that needs an OK. */
    window.ccAlert = function (message, title, type) {
        if (dialog) {
            return dialog.fire({ icon: type || 'info', title: title || '', text: message, confirmButtonText: 'OK' });
        }
        window.alert(message);
        return Promise.resolve();
    };

    /** Asks to confirm. Resolves to true or false. */
    window.ccConfirm = function (options) {
        options = options || {};
        if (!dialog) {
            return Promise.resolve(window.confirm(options.text || options.title || 'Are you sure?'));
        }
        return dialog.fire({
            icon: options.icon || 'warning',
            title: options.title || 'Are you sure?',
            text: options.text || '',
            showCancelButton: true,
            confirmButtonText: options.confirmText || 'Yes, continue',
            cancelButtonText: options.cancelText || 'Cancel',
            reverseButtons: true,
            focusCancel: true
        }).then(function (result) { return result.isConfirmed; });
    };

    /* ---------- Form tokens ---------- */

    function addToken(form) {
        if (!csrfToken || (form.getAttribute('method') || '').toUpperCase() !== 'POST') return;
        if (form.querySelector('input[name="csrf_token"]')) return;
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'csrf_token';
        input.value = csrfToken;
        form.appendChild(input);
    }

    document.querySelectorAll('form').forEach(addToken);
    document.addEventListener('submit', function (event) { addToken(event.target); }, true);

    /** fetch() that sends the token and asks for JSON. */
    window.ccFetch = function (url, options) {
        options = options || {};
        options.headers = Object.assign({
            'X-CSRF-Token': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }, options.headers || {});
        options.credentials = 'same-origin';
        return fetch(url, options);
    };

    /* ---------- Show / hide password ---------- */

    var eyeOpen = '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.8"/></svg>';
    var eyeShut = '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path d="M3 3l18 18M10.6 5.1A10.8 10.8 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4M6.6 6.6C3.7 8.4 2 12 2 12s3.6 7 10 7a9.7 9.7 0 0 0 5.4-1.6M9.9 9.9a3 3 0 0 0 4.2 4.2" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>';

    document.querySelectorAll('input[type="password"]').forEach(function (input) {
        if (input.dataset.noToggle !== undefined || input.closest('.cc-password')) return;

        var wrap = document.createElement('div');
        wrap.className = 'cc-password';
        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);

        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'cc-password-toggle';
        button.setAttribute('aria-label', 'Show password');
        button.setAttribute('aria-pressed', 'false');
        button.innerHTML = eyeOpen;
        wrap.appendChild(button);

        button.addEventListener('click', function () {
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.innerHTML = show ? eyeShut : eyeOpen;
            button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            button.setAttribute('aria-pressed', show ? 'true' : 'false');
            input.focus();
        });
    });

    /* ---------- Confirmations (data-confirm on forms, buttons and links) ---------- */

    function confirmOptions(el) {
        return {
            title: el.dataset.confirmTitle || el.dataset.confirm || 'Are you sure?',
            text: el.dataset.confirmText || '',
            confirmText: el.dataset.confirmButton || 'Yes, continue',
            icon: el.dataset.confirmIcon || 'warning'
        };
    }

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!form.dataset || form.dataset.confirm === undefined || form.dataset.confirmed === '1') return;
        event.preventDefault();
        var submitter = event.submitter;
        ccConfirm(confirmOptions(form)).then(function (ok) {
            if (!ok) return;
            form.dataset.confirmed = '1';
            if (submitter && submitter.name) {
                var hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = submitter.name;
                hidden.value = submitter.value;
                form.appendChild(hidden);
            }
            if (form.dataset.ajax !== undefined) {
                form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
            } else {
                HTMLFormElement.prototype.submit.call(form);
            }
        });
    });

    document.addEventListener('click', function (event) {
        var link = event.target.closest('a[data-confirm]');
        if (!link) return;
        event.preventDefault();
        ccConfirm(confirmOptions(link)).then(function (ok) {
            if (ok) window.location.href = link.href;
        });
    });

    /* ---------- Back links ---------- */
    // <a href="fallback" data-back>Back</a> returns to the previous page when it
    // was inside the site, otherwise goes to the fallback address.
    document.addEventListener('click', function (event) {
        var link = event.target.closest('[data-back]');
        if (!link) return;
        var ref = document.referrer;
        var sameSite = ref && ref.indexOf(window.location.origin) === 0 && ref !== window.location.href;
        if (sameSite && window.history.length > 1) {
            event.preventDefault();
            window.history.back();
        }
    });

    /* ---------- Forms sent without reloading (data-ajax) ---------- */
    // The action replies with JSON: { ok, message, errors: { field: text }, redirect }.

    function clearErrors(form) {
        form.querySelectorAll('.cc-field-error').forEach(function (el) { el.textContent = ''; });
        form.querySelectorAll('[aria-invalid="true"]').forEach(function (el) { el.removeAttribute('aria-invalid'); });
        var box = form.querySelector('.cc-form-alert');
        if (box) { box.hidden = true; box.textContent = ''; }
    }

    function showErrors(form, data) {
        var first = null;
        Object.keys(data.errors || {}).forEach(function (name) {
            var field = form.querySelector('[name="' + name + '"]');
            var holder = form.querySelector('[data-error-for="' + name + '"]');
            if (field) {
                field.setAttribute('aria-invalid', 'true');
                first = first || field;
            }
            if (holder) holder.textContent = data.errors[name];
        });
        if (data.message) {
            var box = form.querySelector('.cc-form-alert');
            if (box) {
                box.textContent = data.message;
                box.hidden = false;
            } else {
                ccNotify(data.message, 'error');
            }
        }
        if (first) first.focus();
    }

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!form.dataset || form.dataset.ajax === undefined || event.defaultPrevented) return;
        event.preventDefault();

        // Forms marked novalidate are checked by the server, which explains each problem.
        if (!form.noValidate && typeof form.reportValidity === 'function' && !form.reportValidity()) return;

        var button = form.querySelector('[type="submit"]');
        var label = button ? button.innerHTML : '';
        if (button) {
            button.disabled = true;
            button.innerHTML = button.dataset.busy || 'Please wait...';
        }
        clearErrors(form);

        ccFetch(form.action, { method: 'POST', body: new FormData(form) })
            .then(function (response) {
                return response.json().catch(function () {
                    return { ok: false, message: 'Something went wrong. Please try again.' };
                });
            })
            .then(function (data) {
                if (data.ok) {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                        return;
                    }
                    if (data.message) ccNotify(data.message, 'success');
                    if (form.dataset.resetOnSuccess !== undefined) form.reset();
                } else {
                    showErrors(form, data);
                }
                form.dataset.confirmed = '';
                if (button) { button.disabled = false; button.innerHTML = label; }
            })
            .catch(function () {
                showErrors(form, { message: 'We could not reach CrownCare. Check your connection and try again.' });
                form.dataset.confirmed = '';
                if (button) { button.disabled = false; button.innerHTML = label; }
            });
    });

    /* ---------- Messages from the previous page ---------- */

    var flash = document.getElementById('cc-flash');
    if (flash) {
        if (flash.dataset.success) ccNotify(flash.dataset.success, 'success');
        if (flash.dataset.error) ccNotify(flash.dataset.error, 'error');
        if (flash.dataset.info) ccNotify(flash.dataset.info, 'info');
    }

    /* ---------- Skip link target ---------- */

    if (!document.getElementById('main-content')) {
        var main = document.querySelector('main') || document.querySelector('.admin-content');
        if (main) {
            main.id = 'main-content';
            main.setAttribute('tabindex', '-1');
        }
    }

    /* ---------- Store menu on phones ---------- */

    var menuButton = document.querySelector('[data-cc-menu-toggle]');
    if (menuButton) {
        menuButton.addEventListener('click', function () {
            var open = document.body.classList.toggle('cc-menu-open');
            menuButton.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }

    /* ---------- Header dropdowns (Learn, Company) ---------- */

    var dropdowns = Array.prototype.slice.call(document.querySelectorAll('[data-cc-dropdown]'));

    function closeDropdowns(except) {
        dropdowns.forEach(function (group) {
            if (group !== except) {
                group.querySelector('.cc-nav-trigger').setAttribute('aria-expanded', 'false');
            }
        });
    }

    dropdowns.forEach(function (group) {
        var trigger = group.querySelector('.cc-nav-trigger');

        trigger.addEventListener('click', function () {
            var open = trigger.getAttribute('aria-expanded') !== 'true';
            closeDropdowns(group);
            trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        });

        // Leaving the menu with the Tab key closes it.
        group.addEventListener('focusout', function (event) {
            if (!group.contains(event.relatedTarget)) {
                trigger.setAttribute('aria-expanded', 'false');
            }
        });
    });

    if (dropdowns.length || menuButton) {
        document.addEventListener('click', function (event) {
            if (!event.target.closest('[data-cc-dropdown]')) {
                closeDropdowns(null);
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') return;

            var openTrigger = document.querySelector('.cc-nav-trigger[aria-expanded="true"]');
            if (openTrigger) {
                closeDropdowns(null);
                openTrigger.focus();
            } else if (menuButton && document.body.classList.contains('cc-menu-open')) {
                document.body.classList.remove('cc-menu-open');
                menuButton.setAttribute('aria-expanded', 'false');
                menuButton.focus();
            }
        });
    }
})();
