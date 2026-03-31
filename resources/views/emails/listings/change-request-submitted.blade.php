<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Listing update/takedown request received</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #111827; line-height: 1.6;">
    <h1 style="font-size: 20px; margin-bottom: 16px;">Listing update/takedown request received</h1>

    <p>A public request was submitted for this listing:</p>

    <ul>
        <li><strong>Listing:</strong> {{ $listing->display_name }}</li>
        <li><strong>Request Type:</strong> {{ $requestData['request_type'] === 'takedown' ? 'Takedown' : 'Change' }}</li>
        <li><strong>Requester Name:</strong> {{ $requestData['requester_name'] }}</li>
        <li><strong>Requester Email:</strong> {{ $requestData['requester_email'] }}</li>
    </ul>

    <p><strong>Request Details:</strong></p>
    <p>{{ $requestData['message'] }}</p>

    @if (!empty($supportingDocuments))
        <p><strong>Supporting Documents Attached:</strong></p>
        <ul>
            @foreach ($supportingDocuments as $document)
                <li>{{ $document['name'] }}</li>
            @endforeach
        </ul>
    @endif

    <p style="margin-top: 24px;">
        <a href="{{ route('admin.listings.edit', $listing) }}">Open this listing in admin</a>
    </p>

    <p>
        <a href="{{ route('listings.show', $listing) }}">View the public listing</a>
    </p>
</body>
</html>