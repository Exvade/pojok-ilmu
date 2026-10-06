@props(['disabled' => false, 'error' => false])
@php
    $name = $attributes->get('name');
    $invalid = $error || ($name && $errors->has($name));
    if ($name && !in_array($attributes->get('type'), ['password', 'file', 'checkbox', 'radio'])) {
        $value = old($name, $attributes->get('value'));
        $attributes = $attributes->except('value')->merge(['value' => $value]);
    }
@endphp
<input @disabled($disabled) @if($invalid) aria-invalid="true" @endif {{ $attributes->class(['form-control', 'border-red-500' => $invalid])->merge(['id' => $name]) }} @if(!$attributes->has('value') && $name && !in_array($attributes->get('type'), ['password', 'file', 'checkbox', 'radio'])) value="{{ old($name) }}" @endif>
@if($name) @error($name)<p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror @endif
