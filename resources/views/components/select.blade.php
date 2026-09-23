@props(['disabled' => false, 'error' => false])

@php
    $baseClasses =
        'w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border rounded-xl focus-visible:outline-none transition-colors duration-200 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 cursor-pointer text-left';

    if ($error) {
        $classes =
            $baseClasses .
            ' border-red-300 focus-visible:border-red-500 focus-visible:ring-red-200 dark:border-red-600 dark:focus-visible:ring-red-900/50';
    } else {
        $classes =
            $baseClasses .
            ' border-gray-200 dark:border-gray-700 focus-visible:border-brand-light focus-visible:ring-brand-light/30 dark:focus-visible:ring-brand-light/20';
    }
@endphp

<div x-data="{
    open: false,
    options: [],
    selectedValue: '',
    selectedLabel: 'Pilih opsi...',
    init() {
        let selectEl = this.$refs.nativeSelect;
        this.selectedValue = selectEl.value;

        // Parse existing options from the hidden select
        Array.from(selectEl.options).forEach(opt => {
            this.options.push({
                value: opt.value,
                label: opt.text,
                selected: opt.selected
            });
            if (opt.selected || opt.value === this.selectedValue) {
                this.selectedLabel = opt.text;
            }
        });

        // Sync when original select changes via Alpine x-model
        this.$watch('selectedValue', (val) => {
            let opt = this.options.find(o => o.value === val);
            if (opt) {
                this.selectedLabel = opt.label;
            } else if (!val) {
                this.selectedLabel = 'Pilih opsi...';
            }

            if (selectEl.value !== val) {
                selectEl.value = val;
                selectEl.dispatchEvent(new Event('change'));
            }
        });
    },
    selectOption(option) {
        this.selectedValue = option.value;
        this.selectedLabel = option.label;
        this.open = false;
    }
}" class="relative" @click.away="open = false">
    <!-- Hidden Native Select -->
    <select x-ref="nativeSelect" @change="selectedValue = $event.target.value" {{ $disabled ? 'disabled' : '' }}
        {!! $attributes->merge(['class' => 'hidden']) !!}>
        {{ $slot }}
    </select>

    <!-- Custom Trigger -->
    <button @click="!{{ $disabled ? 'true' : 'false' }} && (open = !open)" type="button"
        class="{{ $classes }} flex items-center justify-between shadow-sm"
        :class="{ 'ring-2 ring-brand-light/30 border-brand-light': open }">
        <span x-text="selectedLabel" class="truncate"></span>
        <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" :class="{ 'transform rotate-180': open }"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <!-- Dropdown Menu -->
    <div x-show="open" x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] border border-gray-100 dark:border-gray-700 overflow-hidden py-1 max-h-60 overflow-y-auto"
        style="display: none;">
        <template x-for="option in options" :key="option.value">
            <div @click="selectOption(option)"
                class="px-4 py-3 cursor-pointer transition-colors duration-150 flex items-center justify-between"
                :class="selectedValue === option.value ?
                    'bg-green-50 dark:bg-green-900/20 text-brand-dark dark:text-brand-light font-bold' :
                    'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'">
                <span x-text="option.label"></span>
                <svg x-show="selectedValue === option.value" class="w-5 h-5 text-brand-dark dark:text-brand-light"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </template>
    </div>
</div>
