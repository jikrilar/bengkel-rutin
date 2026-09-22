@props(['name', 'label', 'hint' => null])

@php $id = $attributes->get('id', $name); @endphp

<div>
    <label for="{{ $id }}" class="mb-2 block text-sm font-semibold text-ink">{{ $label }}</label>
    <select
        id="{{ $id }}"
        name="{{ $name }}"
        {{ $attributes->except('id')->class([
            'min-h-11 w-full rounded-field border bg-surface px-3.5 text-sm text-ink transition focus:border-brand focus:ring-2 focus:ring-brand-soft focus:outline-none',
            'border-danger' => $errors->has($name),
            'border-line-strong' => ! $errors->has($name),
        ]) }}
    >{{ $slot }}</select>
    @error($name)
        <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
    @else
        @if ($hint)<p class="mt-1.5 text-sm text-ink-muted">{{ $hint }}</p>@endif
    @enderror
</div>
