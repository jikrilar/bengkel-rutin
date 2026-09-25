@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'hint' => null,
])

@php
    $id = $attributes->get('id', $name);
    $errorId = $id.'-error';
    $hintId = $id.'-hint';
@endphp

<div>
    <label for="{{ $id }}" class="mb-2 block text-sm font-semibold text-ink">{{ $label }}</label>
    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
        @if ($errors->has($name)) aria-invalid="true" aria-describedby="{{ $errorId }}" @elseif ($hint) aria-describedby="{{ $hintId }}" @endif
        {{ $attributes->except('id')->class([
            'min-h-11 w-full rounded-field border bg-surface px-3.5 text-sm text-ink transition placeholder:text-ink-muted focus:border-brand focus:ring-2 focus:ring-brand-soft focus:outline-none',
            'border-danger' => $errors->has($name),
            'border-line-strong' => ! $errors->has($name),
        ]) }}
    >
    @error($name)
        <p id="{{ $errorId }}" class="mt-1.5 text-sm text-danger">{{ $message }}</p>
    @else
        @if ($hint)
            <p id="{{ $hintId }}" class="mt-1.5 text-sm text-ink-muted">{{ $hint }}</p>
        @endif
    @enderror
</div>
