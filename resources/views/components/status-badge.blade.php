@props(['status'])

@php
    $value = $status instanceof \BackedEnum ? $status->value : (string) $status;
    $label = $status instanceof \App\Enums\TicketStatus ? $status->label() : str($value)->replace('_', ' ')->title();

    $classes = match ($value) {
        'resolved' => 'bg-green-100 text-green-700',
        'closed' => 'bg-gray-100 text-gray-700',
        'escalated' => 'bg-red-100 text-red-700',
        'waiting_for_customer' => 'bg-yellow-100 text-yellow-700',
        'in_progress' => 'bg-indigo-100 text-indigo-700',
        'assigned' => 'bg-sky-100 text-sky-700',
        'open' => 'bg-blue-100 text-blue-700',
        default => 'bg-gray-100 text-gray-700',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex rounded-full px-2.5 py-1 text-xs font-medium {$classes}"]) }}>
    {{ $label }}
</span>
