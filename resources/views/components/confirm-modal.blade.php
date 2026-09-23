<div x-data="{
    show: false,
    title: '',
    message: '',
    confirmAction: null,
    
    init() {
        this.$watch('show', value => {
            if (value) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        });
    },
    
    openModal(title, message, action) {
        this.title = title;
        this.message = message;
        this.confirmAction = action;
        this.show = true;
    },
    
    closeModal() {
        this.show = false;
        setTimeout(() => {
            this.title = '';
            this.message = '';
            this.confirmAction = null;
        }, 300);
    },
    
    execute() {
        if (typeof this.confirmAction === 'function') {
            this.confirmAction();
        }
        this.closeModal();
    }
}"
@confirm.window="openModal($event.detail.title, $event.detail.message, $event.detail.action)"
class="relative z-[9999]"
aria-labelledby="modal-title"
role="dialog"
aria-modal="true"
x-cloak>

    <!-- Background backdrop -->
    <div x-show="show" 
         x-transition:enter="ease-out duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="ease-in duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" 
         class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity" 
         @click="closeModal()"></div>

    <div x-show="show" class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <!-- Modal panel -->
            <div x-show="show" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 @click.stop 
                 class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-sm">
                
                <!-- Close Button -->
                <button type="button" @click="closeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-500 focus:outline-none">
                    <span class="sr-only">Tutup</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <div class="px-6 pt-10 pb-6">
                    <div class="flex flex-col items-center justify-center">
                        <!-- Warning Triangle Icon -->
                        <div class="mx-auto flex flex-shrink-0 items-center justify-center mb-4">
                            <svg class="h-16 w-16 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        
                        <div class="text-center w-full">
                            <h3 class="text-xl font-bold leading-6 text-slate-800" id="modal-title" x-text="title"></h3>
                            <div class="mt-4">
                                <p class="text-sm text-slate-500" x-text="message"></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="border-t border-gray-100 px-6 py-5 flex flex-row justify-center gap-4">
                    <button type="button" @click="closeModal()" class="flex-1 inline-flex justify-center rounded-full bg-white border border-gray-200 px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm hover:bg-gray-50 transition-colors focus:outline-none">Batal</button>
                    <button type="button" @click="execute()" class="flex-1 inline-flex justify-center rounded-full bg-red-500 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-red-600 transition-colors focus:outline-none">Hapus</button>
                </div>
                
            </div>
        </div>
    </div>
</div>
