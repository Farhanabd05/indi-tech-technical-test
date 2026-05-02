<h1>Ticket Detail</h1>

@isset($ticket)
    <article>
        <h2>{{ $ticket->title }}</h2>
        <p>{{ $ticket->description }}</p>
    </article>

    @php
        $canViewInternal = auth()->user()?->hasRole(['administrator', 'supervisor', 'agent']);
    @endphp

    @foreach ($ticket->comments as $comment)
        @if (! $comment->is_internal || $canViewInternal)
            <p>{{ $comment->body }}</p>
        @endif
    @endforeach
@endisset
