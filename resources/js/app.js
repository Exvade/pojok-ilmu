import './bootstrap';
import Alpine from 'alpinejs';
import Swal from 'sweetalert2';

window.Alpine = Alpine;
window.Swal = Swal;

const focusable = el => [...el.querySelectorAll('a[href], button, input, select, textarea, [tabindex="0"]')].filter(item => !item.disabled && item.getClientRects().length);
function trap(event, container) {
    const items = focusable(container);
    const first = items[0], last = items.at(-1);
    if (!first) { event.preventDefault(); return; }
    if (event.shiftKey && (document.activeElement === first || !items.includes(document.activeElement))) { event.preventDefault(); last.focus(); }
    else if (!event.shiftKey && (document.activeElement === last || !items.includes(document.activeElement))) { event.preventDefault(); first.focus(); }
}
const popup = options => Swal.fire({ confirmButtonColor: '#14532d', cancelButtonColor: '#64748b', ...options });
window.addEventListener('notify', event => popup({ toast: true, position: 'top-end', icon: event.detail.type || 'info', title: event.detail.title || 'Pemberitahuan', text: event.detail.message, timer: 5000, showConfirmButton: false, showCloseButton: true }));
window.addEventListener('confirm', async event => {
    const result = await popup({ icon: 'warning', title: event.detail.title || 'Konfirmasi tindakan', text: event.detail.message, showCancelButton: true, confirmButtonText: event.detail.confirmLabel || 'Lanjutkan', cancelButtonText: 'Batal', focusCancel: true });
    if (result.isConfirmed && typeof event.detail.action === 'function') event.detail.action();
});
document.addEventListener('submit', async event => {
    const form = event.target;
    if (!form.matches('form[data-confirm]')) return;
    if (form.dataset.confirmed === 'true') { delete form.dataset.confirmed; return; }
    event.preventDefault();
    const result = await popup({ icon: 'warning', title: 'Konfirmasi tindakan', text: form.dataset.confirm, showCancelButton: true, confirmButtonText: form.dataset.confirmLabel || 'Hapus', cancelButtonText: 'Batal', focusCancel: true });
    if (result.isConfirmed) { form.dataset.confirmed = 'true'; form.requestSubmit(); }
});

const interactionLock = document.getElementById('interaction-lock');
const pageSkeleton = document.getElementById('page-skeleton');
let previousOverflow = '';
let skeletonTimer = null;
const lockPageInteraction = () => {
    previousOverflow ||= document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    interactionLock?.classList.replace('hidden', 'block');
};
const unlockPageInteraction = () => {
    interactionLock?.classList.replace('block', 'hidden');
    pageSkeleton?.classList.replace('block', 'hidden');
    document.body.style.overflow = previousOverflow;
    previousOverflow = '';
};
const setSkeletonScreen = href => {
    if (!pageSkeleton) return;
    const path = new URL(href, window.location.origin).pathname;
    const screen = path.includes('/training-reports')
        ? 'training-report'
        : path.includes('/training-follow-ups')
            ? 'follow-up'
            : path.includes('/admin/roles') || path.includes('/admin/ojt')
                ? 'management-table'
                : path === '/admin/dashboard' || path.includes('/product-matrix')
        ? 'matrix'
        : path.includes('/product-assignments')
            ? 'assignment'
            : path.includes('/trainings/') || path.includes('/ojt/') || path.includes('/operator/training/')
                ? 'form'
                : 'cards';
    pageSkeleton.querySelectorAll('[data-skeleton-screen]').forEach(element => element.classList.toggle('hidden', element.dataset.skeletonScreen !== screen));
};
const showPageSkeleton = href => {
    lockPageInteraction();
    setSkeletonScreen(href);
    pageSkeleton?.classList.replace('hidden', 'block');
};
const schedulePageSkeleton = href => {
    const path = new URL(href, window.location.origin).pathname;

    if (path.startsWith('/login')) return;

    window.clearTimeout(skeletonTimer);
    skeletonTimer = window.setTimeout(() => showPageSkeleton(href), 350);
};
const showButtonLoading = button => {
    if (!button || button.dataset.loading === 'true') return;
    button.dataset.loading = 'true';
    button.disabled = true;
    button.setAttribute('aria-busy', 'true');
    const loader = document.createElement('span');
    loader.dataset.buttonLoader = 'true';
    loader.className = 'h-4 w-4 shrink-0 animate-spin rounded-full border-2 border-current border-t-transparent';
    loader.setAttribute('aria-hidden', 'true');
    button.prepend(loader);
};
window.lockPageInteraction = lockPageInteraction;
window.showFormLoading = form => {
    showButtonLoading(form?.querySelector('[type="submit"]'));
    lockPageInteraction();
};
document.addEventListener('submit', event => {
    const form = event.target;
    if (event.defaultPrevented) return;
    if (form.dataset.confirm && form.dataset.confirmed !== 'true') return;
    if (!form.checkValidity()) return;
    showButtonLoading(event.submitter || form.querySelector('[type="submit"]'));
    lockPageInteraction();
});
document.addEventListener('click', event => {
    const link = event.target.closest('a[href]');
    if (event.defaultPrevented || event.button !== 0 || !link || link.target === '_blank' || link.hasAttribute('download') || link.dataset.noLoading !== undefined || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
    if (link.href.startsWith(window.location.origin) && link.getAttribute('href') !== '#') schedulePageSkeleton(link.href);
});
document.querySelectorAll('form[data-auto-search]').forEach(form => {
    const delay = Number(form.dataset.autoSearchDelay || 450);
    let timer = null;
    const submit = () => {
        window.clearTimeout(timer);
        if (!form.dataset.autoSearchSubmitting) {
            form.dataset.autoSearchSubmitting = 'true';
            form.requestSubmit();
        }
    };
    form.querySelectorAll('input[name="search"]').forEach(input => {
        input.addEventListener('input', () => {
            const query = input.value.trim();
            window.clearTimeout(timer);
            if (query !== '' && query.length < 2) return;
            timer = window.setTimeout(submit, delay);
        });
    });
    form.querySelectorAll('select[name]').forEach(select => select.addEventListener('change', submit));
});
window.addEventListener('pageshow', () => {
    window.clearTimeout(skeletonTimer);
    unlockPageInteraction();
});

const kioskTimeout = Number(document.body.dataset.kioskIdleTimeout || 0);
const kioskLogoutUrl = document.body.dataset.kioskLogoutUrl;
if (kioskTimeout > 0 && kioskLogoutUrl) {
    let kioskTimer = null;
    const logoutKioskUser = () => {
        if (document.body.dataset.kioskLoggingOut === 'true') return;
        document.body.dataset.kioskLoggingOut = 'true';
        lockPageInteraction();
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = kioskLogoutUrl;
        const token = document.createElement('input');
        token.type = 'hidden';
        token.name = '_token';
        token.value = document.querySelector('meta[name="csrf-token"]')?.content || '';
        form.append(token);
        document.body.append(form);
        HTMLFormElement.prototype.submit.call(form);
    };
    const resetKioskTimer = () => {
        if (document.body.dataset.kioskLoggingOut === 'true') return;
        window.clearTimeout(kioskTimer);
        kioskTimer = window.setTimeout(logoutKioskUser, kioskTimeout);
    };
    ['pointerdown', 'keydown', 'touchstart', 'focusin'].forEach(event => window.addEventListener(event, resetKioskTimer, { passive: true }));
    resetKioskTimer();
}

Alpine.data('appShell', () => ({
    sidebarOpen: false, desktop: window.innerWidth >= 1024, darkMode: document.documentElement.classList.contains('dark'), opener: null,
    toggleTheme() { this.darkMode = !this.darkMode; document.documentElement.classList.toggle('dark', this.darkMode); try { localStorage.setItem('theme', this.darkMode ? 'dark' : 'light'); } catch {} },
    openSidebar() { this.opener = document.activeElement; this.sidebarOpen = true; document.body.style.overflow = 'hidden'; this.$nextTick(() => focusable(this.$refs.sidebar)[0]?.focus()); },
    closeSidebar() { if (!this.sidebarOpen) return; this.sidebarOpen = false; document.body.style.overflow = ''; this.opener?.focus(); },
    trapSidebar(event) { if (this.sidebarOpen) trap(event, this.$refs.sidebar); },
    persistSidebarScroll() {
        try {
            sessionStorage.setItem('pojok-ilmu:sidebar-scroll-top', String(this.$refs.sidebarNavigation?.scrollTop ?? 0));
        } catch {}
    },
    restoreSidebarScroll() {
        try {
            const scrollTop = Number(sessionStorage.getItem('pojok-ilmu:sidebar-scroll-top'));
            if (Number.isFinite(scrollTop)) this.$refs.sidebarNavigation.scrollTop = scrollTop;
        } catch {}
    },
    init() {
        this.resizeHandler = () => { this.desktop = window.innerWidth >= 1024; if (this.desktop) this.closeSidebar(); };
        this.pageHideHandler = () => this.persistSidebarScroll();
        window.addEventListener('resize', this.resizeHandler);
        window.addEventListener('pagehide', this.pageHideHandler);
        this.$nextTick(() => this.restoreSidebarScroll());
    },
    destroy() {
        window.removeEventListener('resize', this.resizeHandler);
        window.removeEventListener('pagehide', this.pageHideHandler);
    }
}));
Alpine.data('modalDialog', (name, initiallyOpen) => ({
    name, open: false, opener: null, previousOverflow: '',
    init() { if (initiallyOpen) this.$nextTick(() => this.openDialog()); },
    openDialog() { if (this.open) return; this.opener = document.activeElement; this.previousOverflow = document.body.style.overflow; this.open = true; document.body.style.overflow = 'hidden'; this.$nextTick(() => (focusable(this.$refs.panel).find(el => el.matches('input:not([type=hidden]), select, textarea')) || this.$refs.panel).focus()); },
    closeDialog() { if (!this.open) return; this.open = false; document.body.style.overflow = this.previousOverflow; this.opener?.focus(); },
    trapFocus(event) { if (this.open) trap(event, this.$refs.panel); },
    destroy() { if (this.open) document.body.style.overflow = this.previousOverflow; }
}));
Alpine.data('scanLogin', url => ({
    token: '', loading: false, error: '',
    init() { this.$nextTick(() => this.$refs.barcodeInput.focus()); },
    async submitScan() {
        if (this.loading || !this.token.trim()) return;
        this.loading = true; this.error = '';
        window.lockPageInteraction();
        try {
            const response = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ token: this.token.trim() }) });
            const data = await response.json();
            if (response.ok && data.success && data.redirect) { window.location.assign(data.redirect); return; }
            this.error = response.status === 419 ? 'Sesi kedaluwarsa. Muat ulang halaman dan coba kembali.' : (data.message || 'Kode belum dapat digunakan untuk masuk.');
        } catch { this.error = 'Koneksi terputus. Periksa jaringan lalu coba kembali.'; }
        this.loading = false;
        unlockPageInteraction();
        this.$nextTick(() => this.$refs.barcodeInput.focus());
    }
}));

Alpine.data('welcomeSpeech', (message, shouldPlay) => ({
    message, shouldPlay,
    enabled: true, supported: 'speechSynthesis' in window, autoplayBlocked: false, speaking: false, speechToken: 0, voiceRetryTimer: null, voiceUnavailable: false,
    init() {
        try { this.enabled = localStorage.getItem('pojokIlmuWelcomeSpeech') !== 'off'; } catch {}
        if (this.shouldPlay && this.enabled && this.supported) this.$nextTick(() => window.setTimeout(() => this.speak(), 300));
    },
    preferredVoice() {
        const voices = window.speechSynthesis.getVoices();
        const isIndonesian = voice => voice.lang.toLowerCase().replace('_', '-').startsWith('id');
        return voices.find(voice => isIndonesian(voice) && /microsoft|google|natural/i.test(voice.name))
            || voices.find(isIndonesian)
            || null;
    },
    speak(retries = 0) {
        if (!this.supported || !this.message) return;
        window.clearTimeout(this.voiceRetryTimer);
        const voice = this.preferredVoice();
        if (!voice) {
            if (retries < 12) {
                this.voiceRetryTimer = window.setTimeout(() => this.speak(retries + 1), 250);
                return;
            }

            this.voiceUnavailable = true;
            this.speaking = false;
            return;
        }

        this.voiceUnavailable = false;
        const token = ++this.speechToken;
        window.speechSynthesis.cancel();
        const utterance = new SpeechSynthesisUtterance(this.message);
        utterance.lang = 'id-ID';
        utterance.rate = 0.92;
        utterance.pitch = 1;
        utterance.voice = voice;
        utterance.onstart = () => { if (token === this.speechToken) { this.speaking = true; this.autoplayBlocked = false; } };
        utterance.onend = () => { if (token === this.speechToken) this.speaking = false; };
        utterance.onerror = () => { if (token === this.speechToken) { this.speaking = false; this.autoplayBlocked = true; } };
        window.speechSynthesis.speak(utterance);
    },
    enable() {
        this.enabled = true;
        this.autoplayBlocked = false;
        this.voiceUnavailable = false;
        try { localStorage.setItem('pojokIlmuWelcomeSpeech', 'on'); } catch {}
        this.speak();
    },
    disable() {
        this.enabled = false;
        this.speaking = false;
        this.speechToken++;
        window.speechSynthesis?.cancel();
        try { localStorage.setItem('pojokIlmuWelcomeSpeech', 'off'); } catch {}
    },
    replay() { this.voiceUnavailable = false; this.speak(); },
    destroy() { this.speechToken++; window.clearTimeout(this.voiceRetryTimer); window.speechSynthesis?.cancel(); },
}));

Alpine.data('importUploader', () => ({
    dragging: false, fileName: '', loading: false,
    submitFile(files) {
        if (!files || !files.length || this.loading) return;
        this.fileName = files[0].name;
        this.$refs.importFile.files = files;
        this.$nextTick(() => this.upload());
    },
    async upload() {
        if (this.loading || !this.$refs.importForm.reportValidity()) return;
        this.loading = true;
        try {
            const response = await fetch(this.$refs.importForm.action, {
                method: 'POST',
                headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(this.$refs.importForm),
            });
            const documentResponse = new DOMParser().parseFromString(await response.text(), 'text/html');
            const nextPage = documentResponse.querySelector('#main-content .page-shell');
            const currentPage = document.querySelector('#main-content .page-shell');

            if (!nextPage || !currentPage) {
                window.location.assign(response.url);
                return;
            }

            Alpine.destroyTree(currentPage);
            currentPage.replaceWith(nextPage);
            Alpine.initTree(nextPage);
            nextPage.querySelector('[data-import-preview]')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } catch {
            this.loading = false;
            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', title: 'Unggahan belum berhasil', message: 'Koneksi terputus. Periksa jaringan lalu coba kembali.' } }));
        }
    },
}));

Alpine.data('quizEngine', (remainingSeconds, total) => ({
    timeLeft: 0, total, answered: 0, submitting: false, timer: null,
    init() {
        this.$nextTick(() => this.countAnswers());
        if (!total) return;
        this.timeLeft = Math.max(0, Number(remainingSeconds || 0));
        this.timer = setInterval(() => {
            this.timeLeft = Math.max(0, this.timeLeft - 1);
            if (this.timeLeft === 0 && !this.submitting) {
                this.submitting = true; clearInterval(this.timer);
                Swal.close();
                this.sendQuiz();
            }
        }, 1000);
    },
    countAnswers() { this.answered = this.$refs.quizForm.querySelectorAll('input[type=radio]:checked').length; },
    async confirmSubmit() {
        if (this.submitting || !this.total || !this.$refs.quizForm.reportValidity()) return;
        const result = await popup({ icon: 'question', title: 'Kirim jawaban evaluasi?', text: 'Pastikan jawaban Anda sudah sesuai sebelum dikirim.', showCancelButton: true, confirmButtonText: 'Kirim jawaban', cancelButtonText: 'Periksa kembali' });
        if (result.isConfirmed && !this.submitting) { this.submitting = true; clearInterval(this.timer); this.sendQuiz(); }
    },
    sendQuiz() { window.showFormLoading(this.$refs.quizForm); HTMLFormElement.prototype.submit.call(this.$refs.quizForm); },
    formatTime(seconds) { return String(Math.floor(seconds / 60)).padStart(2, '0') + ':' + String(seconds % 60).padStart(2, '0'); },
    destroy() { clearInterval(this.timer); }
}));
Alpine.start();
const feedback = document.getElementById('app-feedback');
if (feedback) {
    const messages = JSON.parse(feedback.textContent);
    for (const type of ['success', 'error']) if (messages[type]) window.dispatchEvent(new CustomEvent('notify', { detail: { type, title: type === 'success' ? 'Berhasil' : 'Periksa kembali', message: messages[type] } }));
}
