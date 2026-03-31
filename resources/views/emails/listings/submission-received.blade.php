<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>We received your directory listing submission</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #111827; line-height: 1.6;">
    <h1 style="font-size: 20px; margin-bottom: 16px;">We received your directory listing submission</h1>

    <p>Hello,</p>

    <p>
        We received your submission for <strong>{{ $listing->display_name }}</strong>.
    </p>

    <p>
        Here is what to expect:
    </p>

    <ul>
        <li>Your listing starts in <strong>pending review</strong>.</li>
        <li>It is <strong>not public yet</strong>.</li>
        <li>If it is approved, you should receive another email.</li>
        <li>If it is rejected, you should also receive an email.</li>
    </ul>

    <p>
        Submitted listing details:
    </p>

    <ul>
        <li><strong>Display Name:</strong> {{ $listing->display_name }}</li>
        <li><strong>Listing Type:</strong> {{ ucfirst($listing->listing_type) }}</li>
        <li><strong>Municipality:</strong> {{ $listing->municipality }}</li>
        <li><strong>Service Type:</strong> {{ $listing->service_type === 'Other' && $listing->other_service_type ? $listing->other_service_type : $listing->service_type }}</li>
        <li><strong>Status:</strong> {{ ucfirst($listing->submission_status) }}</li>
    </ul>

    <p style="margin-top: 24px;">
        Thank you for your submission.
    </p>
</body>
</html>