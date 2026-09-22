@props(['name', 'label', 'value' => null, 'rows' => 4, 'hint' => null])

@php $id = $attributes->get('id', $name); @endphp

<div>
    <label for="{{ $id }}" class="mb-2 block text-sm font-semibold text-ink">{{ $label }}</label>
    <textarea
        id="{{ $id }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        {{ $attributes->except('id')->class([
            'w-full rounded-field border bg-surface px-3.5 py-3 text-sm text-ink transition placeholder:text-ink-subtle focus:border-brand focus:ring-2 focus:ring-brand-soft focus:outline-none',
            'border-danger' => $errors->has($name),
            'border-line-strong' => ! $errors->has($name),
        ]) }}
    >{{ old($name, $value) }}</textarea>
    @error($name)
        <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
    @else
        @if ($hint)<p class="mt-1.5 text-sm text-ink-muted">{{ $hint }}</p>@endif
    @enderror
</div>
