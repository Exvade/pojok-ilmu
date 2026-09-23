<div
    x-data="{
        notices: [],
        add(notice) {
            notice.id = Date.now() + Math.random().toString(36).substring(2, 9);
            this.notices.push(notice);
            setTimeout(() => {
                this.remove(notice.id);
            }, 3000);
        },
        remove(id) {
            const index = this.notices.findIndex(n => n.id === id);
            if (index !== -1) {
                this.notices.splice(index, 1);
            }
        }
    }"
    @notify.window="add($event.detail)"
    class="fixed top-4 right-4 z-[9999] flex flex-col items-end space-y-3 pointer-events-none sm:top-6 sm:right-6"
>
    <template x-for="notice in notices" :key="notice.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="translate-x-full opacity-0"
            x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="translate-x-0 opacity-100"
            x-transition:leave-end="translate-x-full opacity-0"
            class="flex items-start p-4 w-80 sm:w-96 rounded-xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] border pointer-events-auto bg-white/90 dark:bg-gray-800/90 backdrop-blur-md"
            :class="{
                'border-green-500/30 dark:border-green-400/20': notice.type === 'success',
                'border-red-500/30 dark:border-red-400/20': notice.type === 'error',
                'border-blue-500/30 dark:border-blue-400/20': notice.type === 'info',
                'border-yellow-500/30 dark:border-yellow-400/20': notice.type === 'warning'
            }"
        >
            <!-- Ikon Status -->
            <div class="flex-shrink-0 mt-0.5">
                <!-- Success -->
                <svg x-show="notice.type === 'success'" class="w-5 h-5 text-green-500 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <!-- Error -->
                <svg x-show="notice.type === 'error'" style="display: none;" class="w-5 h-5 text-red-500 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <!-- Info -->
                <svg x-show="notice.type === 'info'" style="display: none;" class="w-5 h-5 text-blue-500 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <!-- Warning -->
                <svg x-show="notice.type === 'warning'" style="display: none;" class="w-5 h-5 text-yellow-500 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            
            <!-- Teks -->
            <div class="ml-3 w-0 flex-1">
                <p class="text-sm font-bold text-gray-900 dark:text-gray-100 font-heading" x-text="notice.title || 'Pemberitahuan'"></p>
                <p class="mt-0.5 text-sm text-gray-600 dark:text-gray-300" x-text="notice.message"></p>
            </div>

            <!-- Tombol Tutup -->
            <div class="ml-4 flex-shrink-0 flex">
                <button @click="remove(notice.id)" class="inline-flex text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors focus:outline-none">
                    <span class="sr-only">Tutup</span>
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    </template>
</div>
