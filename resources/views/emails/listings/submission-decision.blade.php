<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>
        @if ($decision === 'approved')
            Your directory listing was approved
        @elseif ($decision === 'rejected')
            Your directory listing was not approved
        @else
            Update on your directory listing
        @endif
    </title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #111827; line-height: 1.6;">
    <h1 style="font-size: 20px; margin-bottom: 16px;">
        @if ($decision === 'approved')
            Your directory listing was approved
        @elseif ($decision === 'rejected')
            Your directory listing was not approved
        @else
            Update on your directory listing
        @endif
    </h1>

    <p>Hello,</p>

    @if ($decision === 'approved')
        <p>
            Your listing <strong>{{ $listing->display_name }}</strong> has been approved and is now active in the directory.
        </p>

        <p>
            You can view it here:
            <a href="{{ route('listings.show', $listing) }}">{{ route('listings.show', $listing) }}</a>
        </p>
    @elseif ($decision === 'rejected')
        <p>
            Your listing <strong>{{ $listing->display_name }}</strong> was reviewed and was not approved at this time.
        </p>

        <p>
            It is not public in the directory.
        </p>
    @else
        <p>
            There was an update to your listing <strong>{{ $listing->display_name }}</strong>.
        </p>
    @endif

    <ul>
        <li><strong>Display Name:</strong> {{ $listing->display_name }}</li>
        <li><strong>Listing Type:</strong> {{ ucfirst($listing->listing_type) }}</li>
        <li><strong>Municipality:</strong> {{ $listing->municipality }}</li>
        <li><strong>Current Status:</strong> {{ ucfirst($listing->submission_status) }}</li>
    </ul>

    <p style="margin-top: 24px;">
        Thank you.
    </p>
</body>
</html>