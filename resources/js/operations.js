const money = value => `${new Intl.NumberFormat('vi-VN', { maximumFractionDigits: 0 }).format(value)} đ`;

function initMeters() {
    document.querySelectorAll('[data-meter-pair]').forEach(pair => {
        const previous = pair.querySelector('[data-meter-old]');
        const current = pair.querySelector('[data-meter-new]');
        const error = pair.querySelector('[data-meter-error]');
        const validate = () => {
            current.min = previous.value || '0';
            const decreasing = previous.value !== '' && current.value !== '' && Number(current.value) < Number(previous.value);
            const message = decreasing ? `${pair.dataset.meterLabel}: chỉ số mới phải bằng hoặc lớn hơn chỉ số cũ.` : '';
            current.setCustomValidity(message);
            current.setAttribute('aria-invalid', decreasing ? 'true' : 'false');
            error.textContent = message;
            error.hidden = !decreasing;
        };
        pair.addEventListener('input', validate);
        validate();
    });
}

function initBilling() {
    const form = document.querySelector('[data-bulk-billing]');
    if (!form) return;
    const rooms = [...form.querySelectorAll('[data-billing-room]')];
    const selections = [...form.querySelectorAll('.row-selection')];
    const selectAll = form.querySelector('#select-all');
    const refresh = () => {
        let count = 0;
        let total = 0;
        let hasInvalid = false;
        rooms.forEach(room => {
            const selection = room.querySelector('.row-selection');
            if (!selection) return;
            let roomTotal = Number(room.dataset.base);
            let valid = true;
            room.querySelectorAll('[data-meter-pair]').forEach(pair => {
                const previous = pair.querySelector('[data-meter-old]');
                const current = pair.querySelector('[data-meter-new]');
                valid = valid && previous.validity.valid && current.validity.valid;
                const usage = Math.round((Number(current.value) - Number(previous.value)) * 100) / 100;
                roomTotal += Math.round(Math.max(0, usage) * Number(pair.dataset.rate));
            });
            room.classList.toggle('is-selected', selection.checked);
            room.querySelector('[data-room-total]').textContent = valid ? money(roomTotal) : 'Kiểm tra chỉ số';
            if (selection.checked) {
                count++;
                total += roomTotal;
                hasInvalid = hasInvalid || !valid;
            }
        });
        selectAll.checked = selections.length > 0 && count === selections.length;
        selectAll.indeterminate = count > 0 && count < selections.length;
        form.querySelector('[data-selected-count]').textContent = `${count} phòng đã chọn`;
        form.querySelector('[data-billing-total]').textContent = hasInvalid ? 'Kiểm tra chỉ số' : money(total);
        form.querySelectorAll('[data-billing-preview]').forEach(button => { button.disabled = count === 0 || form.getAttribute('aria-busy') === 'true'; });
    };
    selectAll.addEventListener('change', () => {
        selections.forEach(input => { input.checked = selectAll.checked; });
        refresh();
    });
    form.addEventListener('input', event => { if (event.target !== selectAll) refresh(); });
    form.addEventListener('change', refresh);
    refresh();
    window.addEventListener('pageshow', refresh);
}

function initSubmissions() {
    const restore = () => {
        document.querySelectorAll('[data-submit-state][aria-busy="true"]').forEach(form => {
            form.removeAttribute('aria-busy');
            form.querySelectorAll('button[type="submit"], button:not([type])').forEach(button => {
                button.disabled = false;
                const label = button.querySelector('[data-submit-label]');
                if (label?.dataset.originalLabel) label.textContent = label.dataset.originalLabel;
            });
        });
    };
    document.querySelectorAll('[data-submit-state]').forEach(form => {
        form.addEventListener('submit', event => {
            if (event.defaultPrevented) return;
            if (form.getAttribute('aria-busy') === 'true') {
                event.preventDefault();
                return;
            }
            form.setAttribute('aria-busy', 'true');
            form.querySelectorAll('button[type="submit"], button:not([type])').forEach(button => {
                button.disabled = true;
                const label = button.querySelector('[data-submit-label]');
                if (label) {
                    label.dataset.originalLabel = label.textContent;
                    label.textContent = 'Đang xử lý...';
                }
            });
        });
    });
    window.addEventListener('pageshow', restore);
    // Reveal invalid optional fields before the browser tries to focus them.
    document.addEventListener('invalid', event => {
        for (let parent = event.target.parentElement; parent; parent = parent.parentElement) {
            if (parent.tagName === 'DETAILS') parent.open = true;
        }
    }, true);
    document.querySelector('#ops-errors')?.focus();
}

function initNavigation() {
    const sidebar = document.querySelector('#app-sidebar');
    const panel = document.querySelector('.main-panel');
    const backdrop = document.querySelector('.ops-nav-backdrop');
    if (!sidebar || !panel || !backdrop) return;
    const mobile = window.matchMedia('(max-width: 991px)');
    const toggles = [...document.querySelectorAll('.sidenav-toggler')];
    let opener;
    let wasOpen = false;
    const close = () => {
        if (document.documentElement.classList.contains('nav_open')) toggles[0]?.click();
    };
    const sync = () => {
        const open = mobile.matches && document.documentElement.classList.contains('nav_open');
        toggles.forEach(toggle => {
            toggle.setAttribute('aria-controls', 'app-sidebar');
            toggle.setAttribute('aria-expanded', String(open));
        });
        backdrop.hidden = !open;
        panel.inert = open;
        sidebar.inert = mobile.matches && !open;
        document.body.classList.toggle('ops-menu-open', open);
        if (open && !wasOpen) sidebar.querySelector('[data-close-nav]')?.focus();
        if (!open && wasOpen) opener?.focus();
        wasOpen = open;
    };
    toggles.forEach(toggle => toggle.addEventListener('click', () => {
        if (!document.documentElement.classList.contains('nav_open')) opener = toggle;
    }, true));
    document.querySelectorAll('[data-close-nav]').forEach(button => button.addEventListener('click', close));
    document.addEventListener('keydown', event => {
        if (!wasOpen) return;
        if (event.key === 'Escape') { event.preventDefault(); close(); }
        if (event.key === 'Tab') {
            const focusable = [...sidebar.querySelectorAll('a[href], button, summary, input')].filter(element => element.getClientRects().length && !element.disabled);
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
        }
    });
    new MutationObserver(sync).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    mobile.addEventListener('change', () => { if (!mobile.matches) close(); sync(); });
    sync();
}

function initialize() {
    initMeters();
    initBilling();
    initSubmissions();
    initNavigation();
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize, { once: true });
else initialize();
