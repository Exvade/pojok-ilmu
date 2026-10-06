@props(['disabled' => false, 'error' => false])
@php($name = $attributes->get('name'))
<select @disabled($disabled) {{ $attributes->class(['form-control', 'border-red-500' => $error || ($name && $errors->has($name))])->merge(['id' => $name]) }} @if($name && old($name) !== null) x-data x-init="$el.value = @js(old($name))" @endif>
    {{ $slot }}
</select>
@if($name) @error($name)<p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror @endif
