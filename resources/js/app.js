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

Alpine.data('appShell', () => ({
    sidebarOpen: false, desktop: window.innerWidth >= 1024, darkMode: document.documentElement.classList.contains('dark'), opener: null,
    toggleTheme() { this.darkMode = !this.darkMode; document.documentElement.classList.toggle('dark', this.darkMode); try { localStorage.setItem('theme', this.darkMode ? 'dark' : 'light'); } catch {} },
    openSidebar() { this.opener = document.activeElement; this.sidebarOpen = true; document.body.style.overflow = 'hidden'; this.$nextTick(() => focusable(this.$refs.sidebar)[0]?.focus()); },
    closeSidebar() { if (!this.sidebarOpen) return; this.sidebarOpen = false; document.body.style.overflow = ''; this.opener?.focus(); },
    trapSidebar(event) { if (this.sidebarOpen) trap(event, this.$refs.sidebar); },
    init() { this.resizeHandler = () => { this.desktop = window.innerWidth >= 1024; if (this.desktop) this.closeSidebar(); }; window.addEventListener('resize', this.resizeHandler); },
    destroy() { window.removeEventListener('resize', this.resizeHandler); }
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
        try {
            const response = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ token: this.token.trim() }) });
            const data = await response.json();
            if (response.ok && data.success && data.redirect) { window.location.assign(data.redirect); return; }
            this.error = response.status === 419 ? 'Sesi kedaluwarsa. Muat ulang halaman dan coba kembali.' : (data.message || 'Kode belum dapat digunakan untuk masuk.');
        } catch { this.error = 'Koneksi terputus. Periksa jaringan lalu coba kembali.'; }
        this.loading = false;
        this.$nextTick(() => this.$refs.barcodeInput.focus());
    }
}));

Alpine.data('quizEngine', (expiresAt, total) => ({
    timeLeft: 0, total, answered: 0, submitting: false, timer: null,
    init() {
        this.$nextTick(() => this.countAnswers());
        if (!total) return;
        this.deadline = new Date(expiresAt).getTime();
        this.timeLeft = Math.max(0, Math.ceil((this.deadline - Date.now()) / 1000));
        this.timer = setInterval(() => {
            this.timeLeft = Math.max(0, Math.ceil((this.deadline - Date.now()) / 1000));
            if (this.timeLeft === 0 && !this.submitting) {
                this.submitting = true; clearInterval(this.timer);
                Swal.close();
                this.$refs.quizForm.submit();
            }
        }, 1000);
    },
    countAnswers() { this.answered = this.$refs.quizForm.querySelectorAll('input[type=radio]:checked').length; },
    async confirmSubmit() {
        if (this.submitting || !this.total || !this.$refs.quizForm.reportValidity()) return;
        const result = await popup({ icon: 'question', title: 'Kirim jawaban evaluasi?', text: 'Pastikan jawaban Anda sudah sesuai sebelum dikirim.', showCancelButton: true, confirmButtonText: 'Kirim jawaban', cancelButtonText: 'Periksa kembali' });
        if (result.isConfirmed && !this.submitting) { this.submitting = true; clearInterval(this.timer); this.$refs.quizForm.submit(); }
    },
    formatTime(seconds) { return String(Math.floor(seconds / 60)).padStart(2, '0') + ':' + String(seconds % 60).padStart(2, '0'); },
    destroy() { clearInterval(this.timer); }
}));
Alpine.start();
const feedback = document.getElementById('app-feedback');
if (feedback) {
    const messages = JSON.parse(feedback.textContent);
    for (const type of ['success', 'error']) if (messages[type]) window.dispatchEvent(new CustomEvent('notify', { detail: { type, title: type === 'success' ? 'Berhasil' : 'Periksa kembali', message: messages[type] } }));
}
