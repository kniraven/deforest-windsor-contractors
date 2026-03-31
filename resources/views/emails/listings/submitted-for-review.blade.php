<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New directory listing submitted for review</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #111827; line-height: 1.6;">
    <h1 style="font-size: 20px; margin-bottom: 16px;">New directory listing submitted for review</h1>

    <p>A new listing was submitted and is now pending review.</p>

    <ul>
        <li><strong>Display Name:</strong> {{ $listing->display_name }}</li>
        <li><strong>Listing Type:</strong> {{ ucfirst($listing->listing_type) }}</li>
        <li><strong>Municipality:</strong> {{ $listing->municipality }}</li>
        <li><strong>Service Type:</strong> {{ $listing->service_type === 'Other' && $listing->other_service_type ? $listing->other_service_type : $listing->service_type }}</li>
        <li><strong>Coordinates Provided:</strong> {{ $listing->latitude !== null && $listing->longitude !== null ? 'Yes' : 'No' }}</li>
        <li><strong>Submission Status:</strong> {{ ucfirst($listing->submission_status) }}</li>
    </ul>

    @if ($listing->email)
        <p><strong>Contact Email:</strong> {{ $listing->email }}</p>
    @endif

    @if ($listing->phone)
        <p><strong>Phone:</strong> {{ $listing->phone }}</p>
    @endif

    @if ($listing->website_url)
        <p><strong>Website:</strong> {{ $listing->website_url }}</p>
    @endif

    <p><strong>Short Description:</strong><br>{{ $listing->short_description }}</p>

    @if ($listing->tags->isNotEmpty())
        <p><strong>Tags:</strong> {{ $listing->tags->pluck('name')->implode(', ') }}</p>
    @endif

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
</body>
</html>