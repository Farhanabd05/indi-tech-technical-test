<h1>Tickets Index</h1>

@foreach ($tickets ?? [] as $ticket)
    <article>
        <h2>{{ $ticket->title }}</h2>
        <p>{{ $ticket->description }}</p>
    </article>
@endforeach
