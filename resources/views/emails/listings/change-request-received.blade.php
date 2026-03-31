<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>We received your listing request</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #111827; line-height: 1.6;">
    <h1 style="font-size: 20px; margin-bottom: 16px;">We received your listing request</h1>

    <p>Hello {{ $requestData['requester_name'] }},</p>

    <p>
        We received your request regarding <strong>{{ $listing->display_name }}</strong>.
    </p>

    <ul>
        <li><strong>Request Type:</strong> {{ $requestData['request_type'] === 'takedown' ? 'Takedown' : 'Change' }}</li>
        <li><strong>Listing:</strong> {{ $listing->display_name }}</li>
    </ul>

    <p><strong>Your message:</strong></p>
    <p>{{ $requestData['message'] }}</p>

    <p>
        The directory admin can now review your request and follow up if needed.
    </p>

    <p style="margin-top: 24px;">
        Thank you.
    </p>
</body>
</html>