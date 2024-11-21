<!DOCTYPE html>
<html>
<head>
    <title>New Ticket Created</title>
</head>
<body>
    <h1>New Ticket: {{ $ticket->title }}</h1>
    <p>{{ $ticket->description }}</p>
    <p>Priority: {{ ucfirst($ticket->priority) }}</p>
    <p>Status: {{ ucfirst($ticket->status) }}</p>
</body>
</html>