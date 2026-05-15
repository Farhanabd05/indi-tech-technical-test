<div class="overflow-x-auto bg-white rounded-lg shadow-sm">
    <table {{ $attributes->merge(['class' => 'min-w-full text-sm text-left']) }}>
        {{ $slot }}
    </table>
</div>
