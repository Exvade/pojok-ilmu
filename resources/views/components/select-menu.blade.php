@props([
    'name',
    'options' => [],
    'selected' => null,
    'placeholder' => 'Pilih opsi',
    'disabled' => false,
    'error' => false,
    'multiple' => false,
    'searchPlaceholder' => 'Cari pilihan...',
    'emptyMessage' => 'Pilihan tidak ditemukan.',
])

@php
    $selectedValue = old($name, $selected ?? ($multiple ? [] : ''));
    $selectedValues = $multiple
        ? collect((array) $selectedValue)->map(fn ($value) => (string) $value)->values()->all()
        : (string) $selectedValue;
    $items = collect($options)
        ->map(fn ($label, $value) => ['value' => (string) $value, 'label' => (string) $label])
        ->values();
    $invalid = $error || $errors->has($name);
@endphp

<div
    x-data="{
        open: false,
        value: @js($selectedValues),
        multiple: @js($multiple),
        items: @js($items),
        placeholder: @js($placeholder),
        search: '',
        get label() {
            if (!this.multiple) return this.items.find((item) => item.value === this.value)?.label || this.placeholder;
            if (this.value.includes('__all__')) return this.items.find((item) => item.value === '__all__')?.label || this.placeholder;
            const labels = this.items.filter((item) => this.value.includes(item.value)).map((item) => item.label);
            return labels.length ? labels.join(', ') : this.placeholder;
        },
        isSelected(item) { return this.multiple ? this.value.includes(item.value) : this.value === item.value },
        get selectedItems() { return this.items.filter((item) => this.value.includes(item.value)); },
        get filteredItems() {
            const keyword = this.search.trim().toLocaleLowerCase();
            return keyword ? this.items.filter((item) => item.label.toLocaleLowerCase().includes(keyword)) : this.items;
        },
        choose(item) {
            if (!this.multiple) { this.value = item.value; this.open = false; return; }
            if (item.value === '__all__') { this.value = ['__all__']; return; }
            this.value = this.value.filter((value) => value !== '__all__');
            this.value = this.value.includes(item.value)
                ? this.value.filter((value) => value !== item.value)
                : [...this.value, item.value];
        },
        remove(value) { this.value = this.value.filter((item) => item !== value); },
    }"
    @keydown.escape.window="open = false"
    @click.outside="open = false"
    class="relative"
>
    <template x-if="multiple">
        <template x-for="selectedItem in value" :key="selectedItem">
            <input type="hidden" name="{{ $name }}[]" :value="selectedItem">
        </template>
    </template>
    <template x-if="!multiple">
        <input type="hidden" name="{{ $name }}" x-model="value">
    </template>
    <button
        type="button"
        @disabled($disabled)
        @click="open = ! open; search = ''; if (open && multiple) $nextTick(() => $refs.searchInput?.focus())"
        @keydown.down.prevent="open = true; search = ''; if (multiple) $nextTick(() => $refs.searchInput?.focus())"
        :aria-expanded="open.toString()"
        aria-haspopup="listbox"
        {{ $attributes->class([
            'flex min-h-11 w-full items-center justify-between gap-3 rounded-lg border bg-white px-3.5 py-2.5 text-left text-sm font-medium text-gray-800 transition focus:outline-none focus:ring-2 focus:ring-brand-light/30 dark:bg-gray-800 dark:text-gray-100',
            'border-red-500 focus:border-red-500' => $invalid,
            'border-gray-300 hover:border-brand-light focus:border-brand-light dark:border-gray-700 dark:hover:border-green-500 dark:focus:border-green-500' => ! $invalid,
        ])->except('name') }}
    >
        <span class="min-w-0 truncate" x-text="label"></span>
        <x-icon name="chevron-down" class="h-4 w-4 shrink-0 text-gray-400 transition-transform duration-150" x-bind:class="open ? 'rotate-180' : ''" />
    </button>

    <div x-show="multiple && selectedItems.length" class="mt-2 flex flex-wrap gap-1.5" aria-live="polite">
        <template x-for="item in selectedItems" :key="item.value">
            <span class="inline-flex max-w-full items-center gap-1 rounded-full bg-green-100 py-1 pl-2.5 pr-1 text-xs font-semibold text-brand-dark dark:bg-green-950/60 dark:text-green-200">
                <span class="truncate" x-text="item.label"></span>
                <button type="button" @click="remove(item.value)" class="rounded-full p-0.5 transition hover:bg-green-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-light dark:hover:bg-green-900" :aria-label="`Hapus ${item.label}`">
                    <x-icon name="close" class="h-3.5 w-3.5" />
                </button>
            </span>
        </template>
    </div>

    <div
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="absolute z-30 mt-2 max-h-72 w-full overflow-y-auto rounded-xl border border-gray-100 bg-white p-1.5 shadow-xl dark:border-gray-700 dark:bg-gray-800"
        role="listbox"
    >
        <div x-show="multiple" class="sticky top-0 z-10 bg-white px-1 pb-2 pt-1 dark:bg-gray-800">
            <div class="relative">
                <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                <x-input type="search" x-ref="searchInput" x-model="search" @click.stop @keydown.enter.prevent placeholder="{{ $searchPlaceholder }}" class="min-h-10 pl-9 text-sm" aria-label="{{ $searchPlaceholder }}" />
            </div>
        </div>
        <template x-for="item in filteredItems" :key="item.value">
            <button
                type="button"
                @click="choose(item)"
                class="flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-gray-700 transition hover:bg-brand-surface hover:text-brand-dark dark:text-gray-200 dark:hover:bg-green-950/40 dark:hover:text-green-300"
                :class="isSelected(item) ? 'bg-green-50 text-brand-dark dark:bg-green-950/50 dark:text-green-300' : ''"
                :aria-selected="isSelected(item).toString()"
                role="option"
            >
                <span x-text="item.label"></span>
                <x-icon name="check" class="h-4 w-4 text-brand-dark dark:text-green-300" x-show="isSelected(item)" />
            </button>
        </template>
        <p x-show="filteredItems.length === 0" class="px-3 py-4 text-center text-sm text-gray-500">{{ $emptyMessage }}</p>
    </div>
</div>

@error($name)
    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
@enderror
