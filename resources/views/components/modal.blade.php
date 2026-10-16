@props(['name', 'show' => false, 'title' => 'Formulir', 'maxWidth' => '2xl'])
@php($width = ['sm' => 'max-w-sm', 'md' => 'max-w-md', 'lg' => 'max-w-lg', 'xl' => 'max-w-xl', '2xl' => 'max-w-2xl'][$maxWidth] ?? 'max-w-2xl')
<div {{ $attributes->class('contents') }} x-data="modalDialog(@js($name), @js((bool) $show))"
     @open-modal.window="if ($event.detail === name) openDialog()"
     @close-modal.window="if ($event.detail === name) closeDialog()"
     @keydown.escape.window="if (open) closeDialog()" x-cloak>
    {{-- Teleport prevents a parent scrolling/stacking context from clipping the backdrop. --}}
    <template x-teleport="body">
        <div x-show="open" x-cloak @keydown.tab="trapFocus($event)" @close.stop="closeDialog()"
             class="fixed inset-0 z-[70] flex h-[100dvh] w-screen items-start justify-center overflow-y-auto overscroll-contain bg-gray-950/60 p-3 sm:p-6"
             role="dialog" aria-modal="true" aria-labelledby="{{ $name }}-title"
             @click.self="closeDialog()">
            <div x-ref="panel" tabindex="-1" class="relative my-auto flex max-h-[calc(100dvh-1.5rem)] w-full {{ $width }} flex-col rounded-lg border border-gray-200 bg-white shadow-xl sm:max-h-[calc(100dvh-3rem)] dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-between gap-4 border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                    <h2 id="{{ $name }}-title" class="text-base font-semibold">{{ $title }}</h2>
                    <x-button variant="ghost" size="sm" @click="closeDialog()" aria-label="Tutup formulir"><x-icon name="close" /></x-button>
                </div>
                <div class="min-h-0 overflow-y-auto p-5 sm:p-6">{{ $slot }}</div>
            </div>
        </div>
    </template>
</div>
