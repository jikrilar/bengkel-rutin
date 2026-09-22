@props(['status' => null])

@php
    $resolvedStatus = $status ?? \App\Enums\RecommendationStatus::Unavailable;
@endphp

<x-status-badge :status="$resolvedStatus->tone()" {{ $attributes }}>
    {{ $resolvedStatus->label() }}
</x-status-badge>
