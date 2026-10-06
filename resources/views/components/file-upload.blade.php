@props(['name', 'label' => false, 'accept' => '*/*', 'helpText' => 'Unggah file sesuai format yang ditentukan.'])
<div class="space-y-2">
    @if($label)<label for="{{ $attributes->get('id', $name) }}" class="block text-sm font-medium">{{ $label }}</label>@endif
    <x-input type="file" :name="$name" :accept="$accept" {{ $attributes }} />
    @if($helpText)<p class="text-xs text-gray-500 dark:text-gray-400">{{ $helpText }}</p>@endif
</div>
