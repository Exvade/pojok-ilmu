@props(['error' => false])
@php($name = $attributes->get('name'))
<textarea {{ $attributes->class(['form-control min-h-24', 'border-red-500' => $error || ($name && $errors->has($name))])->merge(['id' => $name]) }}>{{ $name ? old($name, (string) $slot) : $slot }}</textarea>
@if($name) @error($name)<p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror @endif
