# ----------------------------------------
# ChatGPT targeted export helper
# Purpose:
# Export only the files most likely needed for:
# 1. Optional file attachments on listing submission
# 2. Optional file attachments on listing change/takedown requests
# 3. Approval/rejection/submission email flow
# 4. Admin listing review flow
# 5. Contrast fix for the Sole Proprietorship tip
#
# IMPORTANT:
# Do NOT export the full .env file.
# Instead, manually paste these lines separately with secrets removed:
# MAIL_MAILER=
# MAIL_HOST=
# MAIL_PORT=
# MAIL_USERNAME=
# MAIL_PASSWORD=
# MAIL_ENCRYPTION=
# MAIL_FROM_ADDRESS=
# MAIL_FROM_NAME=
# DIRECTORY_SUBMISSION_NOTIFICATION_EMAIL=
# ----------------------------------------

$outputFile = "chatgpt-targeted-export.txt"

# ----------------------------------------
# Exact files most likely needed
# ----------------------------------------

$explicitFiles = @(
    "routes/web.php",

    "app/Models/Listing.php",
    "app/Models/User.php",

    "app/Http/Controllers/ListingSubmissionController.php",
    "app/Http/Controllers/ListingChangeRequestController.php",
    "app/Http/Controllers/ListingController.php",
    "app/Http/Controllers/Admin/ListingController.php",

    "app/Mail/ListingSubmittedForReview.php",
    "app/Mail/ListingApproved.php",
    "app/Mail/ListingRejected.php",
    "app/Mail/ListingChangeRequested.php",
    "app/Mail/ListingTakedownRequested.php",

    "config/listings.php",
    "config/mail.php",

    "resources/views/listings/submit.blade.php",
    "resources/views/listings/forms/_shared_fields.blade.php",
    "resources/views/listings/show.blade.php",
    "resources/views/listings/index.blade.php",

    "resources/views/listings/requests/create.blade.php",
    "resources/views/listings/request.blade.php",
    "resources/views/listings/requests/form.blade.php",
    "resources/views/listings/forms/_request_fields.blade.php",

    "resources/views/admin/listings/index.blade.php",
    "resources/views/admin/listings/edit.blade.php",
    "resources/views/admin/listings/create.blade.php",

    "resources/views/emails/listings/submitted-for-review.blade.php",
    "resources/views/emails/listings/approved.blade.php",
    "resources/views/emails/listings/rejected.blade.php",
    "resources/views/emails/listings/change-requested.blade.php",
    "resources/views/emails/listings/takedown-requested.blade.php"
)

# ----------------------------------------
# Folders to scan for additional likely files
# ----------------------------------------

$searchRoots = @(
    "routes",
    "app/Http/Controllers",
    "app/Mail",
    "app/Models",
    "config",
    "resources/views/listings",
    "resources/views/admin/listings",
    "resources/views/emails"
)

# ----------------------------------------
# File name hints for likely related files
# ----------------------------------------

$fileNameHints = @(
    "ListingSubmission",
    "ListingChangeRequest",
    "ListingRequest",
    "ListingApproval",
    "ListingRejected",
    "ListingApproved",
    "ListingSubmitted",
    "ListingController",
    "submission",
    "request",
    "takedown",
    "approve",
    "reject",
    "mail",
    "email"
)

# ----------------------------------------
# Keywords to find hidden related logic
# ----------------------------------------

$keywords = @(
    "supporting_documents",
    "attachment",
    "attachments",
    "UploadedFile",
    "file(",
    "mimes:",
    "max:5120",
    "multipart/form-data",

    "listings.submit.create",
    "listings.submit.store",
    "listings.requests.create",
    "listings.requests.store",
    "admin.listings.edit",
    "admin.listings.update",

    "ListingSubmittedForReview",
    "ListingApproved",
    "ListingRejected",
    "ListingChangeRequested",
    "ListingTakedownRequested",

    "Mail::to",
    "Mail::send",
    "Mailable",
    "attachments()",
    "Attachment::fromData",

    "submission_status",
    "pending",
    "approved",
    "rejected",

    "is_active",
    "is_verified",
    "is_featured",

    "other_legal_structure",
    "legal_structure",
    "legal_structure_individual_tip",
    "Sole Proprietorship",

    "request_type",
    "change request",
    "takedown",
    "take down",
    "authority",
    "proof of address",
    "proof of business"
)

# ----------------------------------------
# Allowed extensions
# ----------------------------------------

$allowedExtensions = @(
    ".php",
    ".blade.php",
    ".js",
    ".css"
)

# ----------------------------------------
# Skip folders
# ----------------------------------------

$skipFolderNames = @(
    "vendor",
    "node_modules",
    "storage",
    ".git"
)

# ----------------------------------------
# Helper functions
# ----------------------------------------

function Write-SectionHeader {
    param (
        [string]$Title
    )

    Add-Content -Path $outputFile -Value ("=" * 100)
    Add-Content -Path $outputFile -Value $Title
    Add-Content -Path $outputFile -Value ("=" * 100)
}

function Test-SkippedPath {
    param (
        [string]$PathToCheck
    )

    foreach ($folderName in $skipFolderNames) {
        if ($PathToCheck -match "(\\|/)$([regex]::Escape($folderName))(\\|/|$)") {
            return $true
        }
    }

    return $false
}

function Get-NormalizedPath {
    param (
        [string]$PathValue
    )

    try {
        return [System.IO.Path]::GetFullPath($PathValue).ToLowerInvariant()
    }
    catch {
        return $PathValue.ToLowerInvariant()
    }
}

function Add-FileToOutput {
    param (
        [string]$FilePath,
        [string]$Tag = "FILE"
    )

    Write-SectionHeader "${Tag}: $FilePath"

    if (Test-Path -LiteralPath $FilePath -PathType Leaf) {
        Get-Content -LiteralPath $FilePath | Add-Content -Path $outputFile
    }
    else {
        Add-Content -Path $outputFile -Value "[FILE NOT FOUND]"
    }

    Add-Content -Path $outputFile -Value ""
    Add-Content -Path $outputFile -Value ""
}

function Test-AllowedExtension {
    param (
        [string]$FileName
    )

    foreach ($ext in $allowedExtensions) {
        if ($FileName.ToLowerInvariant().EndsWith($ext.ToLowerInvariant())) {
            return $true
        }
    }

    return $false
}

# ----------------------------------------
# Start clean
# ----------------------------------------

if (Test-Path -LiteralPath $outputFile) {
    Remove-Item -LiteralPath $outputFile -Force
}

$exportedPaths = New-Object 'System.Collections.Generic.HashSet[string]'
$keywordMatchedFiles = New-Object 'System.Collections.Generic.HashSet[string]'
$hintMatchedFiles = New-Object 'System.Collections.Generic.HashSet[string]'

# ----------------------------------------
# 1. Export exact files first
# ----------------------------------------

Write-SectionHeader "EXACT FILE EXPORTS"

foreach ($file in $explicitFiles) {
    $normalized = Get-NormalizedPath $file

    if (-not $exportedPaths.Contains($normalized)) {
        [void]$exportedPaths.Add($normalized)
        Add-FileToOutput -FilePath $file -Tag "EXACT FILE"
    }
}

# ----------------------------------------
# 2. Find additional files by file name hint
# ----------------------------------------

Write-SectionHeader "FILENAME HINT MATCHES"

foreach ($root in $searchRoots) {
    Add-Content -Path $outputFile -Value ("SEARCH ROOT: " + $root)

    if (-not (Test-Path -LiteralPath $root -PathType Container)) {
        Add-Content -Path $outputFile -Value "[SEARCH ROOT NOT FOUND]"
        Add-Content -Path $outputFile -Value ""
        continue
    }

    $files = Get-ChildItem -LiteralPath $root -Recurse -File -ErrorAction SilentlyContinue | Where-Object {
        (-not (Test-SkippedPath $_.FullName)) -and (Test-AllowedExtension $_.Name)
    }

    foreach ($file in $files) {
        foreach ($hint in $fileNameHints) {
            if ($file.Name -like "*$hint*") {
                $normalized = Get-NormalizedPath $file.FullName

                if (-not $hintMatchedFiles.Contains($normalized)) {
                    [void]$hintMatchedFiles.Add($normalized)
                    Add-Content -Path $outputFile -Value ("MATCHED FILE: " + $file.FullName)
                }

                break
            }
        }
    }

    Add-Content -Path $outputFile -Value ""
}

Write-SectionHeader "FULL EXPORT OF FILENAME HINT MATCHES"

foreach ($normalizedPath in $hintMatchedFiles) {
    $resolvedPath = $null

    try {
        $resolvedPath = (Get-Item -LiteralPath $normalizedPath -ErrorAction Stop).FullName
    }
    catch {
        $resolvedPath = $normalizedPath
    }

    if (-not $exportedPaths.Contains($normalizedPath)) {
        [void]$exportedPaths.Add($normalizedPath)
        Add-FileToOutput -FilePath $resolvedPath -Tag "HINT MATCH FILE"
    }
}

# ----------------------------------------
# 3. Keyword search
# ----------------------------------------

Write-SectionHeader "KEYWORD SEARCH RESULTS"

foreach ($root in $searchRoots) {
    Add-Content -Path $outputFile -Value ("SEARCH ROOT: " + $root)

    if (-not (Test-Path -LiteralPath $root -PathType Container)) {
        Add-Content -Path $outputFile -Value "[SEARCH ROOT NOT FOUND]"
        Add-Content -Path $outputFile -Value ""
        continue
    }

    $files = Get-ChildItem -LiteralPath $root -Recurse -File -ErrorAction SilentlyContinue | Where-Object {
        (-not (Test-SkippedPath $_.FullName)) -and (Test-AllowedExtension $_.Name)
    }

    foreach ($file in $files) {
        $foundKeywordMatches = Select-String -Path $file.FullName -Pattern $keywords -SimpleMatch -CaseSensitive:$false -ErrorAction SilentlyContinue

        if ($foundKeywordMatches) {
            $normalized = Get-NormalizedPath $file.FullName

            if (-not $keywordMatchedFiles.Contains($normalized)) {
                [void]$keywordMatchedFiles.Add($normalized)

                Add-Content -Path $outputFile -Value ("MATCHED FILE: " + $file.FullName)

                foreach ($foundMatch in $foundKeywordMatches) {
                    $lineText = $foundMatch.Line.Trim()
                    Add-Content -Path $outputFile -Value ("  Line " + $foundMatch.LineNumber + ": " + $lineText)
                }

                Add-Content -Path $outputFile -Value ""
            }
        }
    }

    Add-Content -Path $outputFile -Value ""
}

# ----------------------------------------
# 4. Export full contents of keyword-matched files
# ----------------------------------------

Write-SectionHeader "FULL EXPORT OF KEYWORD-MATCHED FILES"

if ($keywordMatchedFiles.Count -eq 0) {
    Add-Content -Path $outputFile -Value "[NO KEYWORD MATCHES FOUND]"
    Add-Content -Path $outputFile -Value ""
}
else {
    foreach ($normalizedPath in $keywordMatchedFiles) {
        $resolvedPath = $null

        try {
            $resolvedPath = (Get-Item -LiteralPath $normalizedPath -ErrorAction Stop).FullName
        }
        catch {
            $resolvedPath = $normalizedPath
        }

        if (-not $exportedPaths.Contains($normalizedPath)) {
            [void]$exportedPaths.Add($normalizedPath)
            Add-FileToOutput -FilePath $resolvedPath -Tag "KEYWORD MATCH FILE"
        }
        else {
            Write-SectionHeader "KEYWORD MATCH FILE ALREADY EXPORTED: $resolvedPath"
            Add-Content -Path $outputFile -Value "[SKIPPED FULL EXPORT BECAUSE THIS FILE WAS ALREADY INCLUDED ABOVE]"
            Add-Content -Path $outputFile -Value ""
            Add-Content -Path $outputFile -Value ""
        }
    }
}

# ----------------------------------------
# Done
# ----------------------------------------

Write-Host "Done. Output saved to $outputFile"
Write-Host "Total unique files exported or referenced: $($exportedPaths.Count)"