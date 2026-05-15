@props(['title' => null])

<section {{ $attributes->merge(['class' => 'bg-white rounded-lg shadow-sm p-5']) }}>
    @if ($title)
        <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ $title }}</h2>
    @endif

    {{ $slot }}
</section>
