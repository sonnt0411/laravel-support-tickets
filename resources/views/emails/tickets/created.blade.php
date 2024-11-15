@component('mail::message')
# New Support Ticket Created

A new support ticket has been created.

**Title:** {{ $ticket->title }}

**Description:** {{ $ticket->description }}

**Priority:** {{ ucfirst($ticket->priority) }}

@component('mail::button', ['url' => route('tickets.show', $ticket)])
View Ticket
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
