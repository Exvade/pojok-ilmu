@props([
    'name', 
    'label' => false, 
    'accept' => '*/*',
    'helpText' => 'Unggah file sesuai format yang ditentukan.'
])

<div class="w-full"
    x-data="{ 
        isDropping: false, 
        fileName: '',
        handleDrop(e) {
            this.isDropping = false;
            if (e.dataTransfer.files.length > 0) {
                this.$refs.fileInput.files = e.dataTransfer.files;
                this.fileName = e.dataTransfer.files[0].name;
            }
        },
        handleChange(e) {
            if (e.target.files.length > 0) {
                this.fileName = e.target.files[0].name;
            } else {
                this.fileName = '';
            }
        }
    }">
    
    @if($label)
        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $label }}</label>
    @endif
    
    <div class="flex items-center justify-center w-full">
        <label 
            @dragover.prevent="isDropping = true"
            @dragleave.prevent="isDropping = false"
            @drop.prevent="handleDrop($event)"
            :class="isDropping ? 'bg-brand-surface dark:bg-gray-800 border-brand-light' : 'bg-gray-50 dark:bg-gray-900 border-gray-300 dark:border-gray-700'"
            class="flex flex-col items-center justify-center w-full h-44 border-2 border-dashed rounded-2xl cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 hover:border-brand-light dark:hover:border-brand-light transition-all duration-300 focus-within:ring-2 focus-within:ring-brand-light focus-within:ring-offset-2">
            
            <!-- Keadaan Kosong (Belum ada file) -->
            <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center" x-show="!fileName">
                <svg class="w-10 h-10 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                </svg>
                <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold text-brand-dark dark:text-brand-light">Klik untuk unggah</span> atau seret file ke sini</p>
                @if($helpText)
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $helpText }}</p>
                @endif
            </div>

            <!-- Keadaan Terisi (File dipilih) -->
            <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center" x-show="fileName" style="display: none;">
                <div class="p-3 mb-3 rounded-full bg-green-100 dark:bg-green-900/30 text-brand-dark dark:text-brand-light">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100" x-text="fileName"></p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Klik kotak ini untuk mengganti file</p>
            </div>
            
            <input 
                x-ref="fileInput"
                type="file" 
                name="{{ $name }}" 
                class="hidden" 
                accept="{{ $accept }}"
                @change="handleChange"
                {{ $attributes }} />
        </label>
    </div> 
</div>
