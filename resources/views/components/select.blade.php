@props(['disabled' => false, 'error' => false])

@php
    $name = $attributes->get('name');
    $id = $attributes->get('id', $name);
    $invalid = $error || ($name && $errors->has($name));
    $oldValue = $name && old($name) !== null ? (string) old($name) : null;
    $isHidden = str_contains((string) $attributes->get('class'), 'hidden');
@endphp

<div
    x-data="{
        open: false,
        value: @js($oldValue),
        items: [],
        init() {
            this.items = Array.from(this.$refs.select.options).map((option) => ({ value: option.value, label: option.text, disabled: option.disabled }));
            this.value = this.value ?? this.$refs.select.value;
            this.$nextTick(() => { this.value = this.$refs.select.value; });
        },
        choose(item) {
            if (item.disabled) return;
            this.value = item.value;
            this.$refs.select.value = item.value;
            this.$refs.select.dispatchEvent(new Event('input', { bubbles: true }));
            this.$refs.select.dispatchEvent(new Event('change', { bubbles: true }));
            this.open = false;
        },
        get label() { return this.items.find((item) => item.value === this.value)?.label || 'Pilih opsi'; },
    }"
    @keydown.escape.window="open = false"
    @click.outside="open = false"
    @class(['relative', 'hidden' => $isHidden])
>
    <select
        x-ref="select"
        name="{{ $name }}"
        @disabled($disabled)
        tabindex="-1"
        aria-hidden="true"
        {{ $attributes->except(['class', 'id', 'name'])->class('sr-only') }}
    >
        {{ $slot }}
    </select>

    <button
        type="button"
        id="{{ $id }}"
        @disabled($disabled)
        @click="open = ! open"
        @keydown.down.prevent="open = true"
        :aria-expanded="open.toString()"
        aria-haspopup="listbox"
        {{ $attributes->except(['id', 'name', 'x-model'])->class([
            'flex min-h-11 w-full items-center justify-between gap-3 rounded-lg border bg-white px-3.5 py-2.5 text-left text-sm font-medium text-gray-800 transition focus:outline-none focus:ring-2 focus:ring-brand-light/30 dark:bg-gray-800 dark:text-gray-100',
            'border-red-500 focus:border-red-500' => $invalid,
            'border-gray-300 hover:border-brand-light focus:border-brand-light dark:border-gray-700 dark:hover:border-green-500 dark:focus:border-green-500' => ! $invalid,
        ]) }}
    >
        <span class="min-w-0 truncate" x-text="label"></span>
        <x-icon name="chevron-down" class="h-4 w-4 shrink-0 text-gray-400 transition-transform duration-150" x-bind:class="open ? 'rotate-180' : ''" />
    </button>

    <div
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="absolute z-30 mt-2 max-h-64 w-full overflow-y-auto rounded-xl border border-gray-100 bg-white p-1.5 shadow-xl dark:border-gray-700 dark:bg-gray-800"
        role="listbox"
    >
        <template x-for="item in items" :key="item.value">
            <button
                type="button"
                @click="choose(item)"
                :disabled="item.disabled"
                :class="[
                    'flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium transition',
                    item.disabled ? 'cursor-not-allowed text-gray-400' : 'text-gray-700 hover:bg-brand-surface hover:text-brand-dark dark:text-gray-200 dark:hover:bg-green-950/40 dark:hover:text-green-300',
                    value === item.value && ! item.disabled ? 'bg-green-50 text-brand-dark dark:bg-green-950/50 dark:text-green-300' : '',
                ]"
                :aria-selected="(value === item.value).toString()"
                role="option"
            >
                <span x-text="item.label"></span>
                <x-icon name="check" class="h-4 w-4 text-brand-dark dark:text-green-300" x-show="value === item.value" />
            </button>
        </template>
    </div>
</div>

@if($name)
    @error($name)
        <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
@endif
