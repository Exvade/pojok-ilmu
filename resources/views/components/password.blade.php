@props(['name' => 'password', 'id' => 'password'])
<div x-data="{ visible: false }" class="relative">
    <x-input :name="$name" :id="$id" ::type="visible ? 'text' : 'password'" autocomplete="current-password" {{ $attributes->class(['pr-12']) }} />
    <button type="button" @click="visible = !visible" :aria-label="visible ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'" :aria-pressed="visible" class="absolute right-1 top-1 flex h-8 w-9 items-center justify-center rounded text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700"><x-icon name="eye" /></button>
</div>
