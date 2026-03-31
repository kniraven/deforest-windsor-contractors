# ----------------------------------------
# ChatGPT file export helper
# ----------------------------------------
# What this does:
# 1. Exports a list of known important files
# 2. Exports any matching partial/component/controller/middleware/mail files if they exist
# 3. Searches for relevant keywords in likely project files
# 4. Avoids duplicates
# 5. Handles missing files/folders safely
#
# NOTE:
# Do NOT export the full .env file.
# If mail setup review is needed, manually paste only these lines separately
# with secrets removed:
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

$explicitFiles = @(
    "routes/web.php",
    "routes/auth.php",
    "bootstrap/app.php",

    "app/Models/Listing.php",
    "app/Models/User.php",

    "app/Http/Controllers/ListingController.php",
    "app/Http/Controllers/Admin/ListingController.php",

    "config/listings.php",
    "config/mail.php",
    "config/auth.php",

    "database/seeders/ListingSeeder.php",

    "resources/js/app.js",
    "resources/css/app.css",
    "vite.config.js",

    "resources/views/admin/listings/index.blade.php",
    "resources/views/admin/listings/create.blade.php",
    "resources/views/admin/listings/edit.blade.php",

    "resources/views/listings/index.blade.php",
    "resources/views/listings/submit.blade.php",
    "resources/views/listings/show.blade.php",
    "resources/views/listings/create.blade.php",
    "resources/views/listings/submit/create.blade.php",
    "resources/views/listings/forms/_shared_fields.blade.php",
    "resources/views/listings/forms/_admin_fields.blade.php"
)

# Wildcard patterns for likely shared UI / auth / admin / notification files
$extraFilePatterns = @(
    "app/Http/Controllers/*.php",
    "app/Http/Controllers/**/*.php",
    "app/Http/Middleware/*.php",
    "app/Http/Middleware/**/*.php",
    "app/Models/*.php",
    "app/Notifications/*.php",
    "app/Notifications/**/*.php",
    "app/Mail/*.php",
    "app/Mail/**/*.php",
    "app/Providers/*.php",
    "app/Providers/**/*.php",

    "database/migrations/*.php",

    "resources/views/components/*.blade.php",
    "resources/views/components/**/*.blade.php",
    "resources/views/layouts/*.blade.php",
    "resources/views/layouts/**/*.blade.php",
    "resources/views/auth/*.blade.php",
    "resources/views/auth/**/*.blade.php",
    "resources/views/listings/forms/*.blade.php",
    "resources/views/listings/forms/**/*.blade.php",
    "resources/views/listings/partials/*.blade.php",
    "resources/views/listings/partials/**/*.blade.php",

    "resources/js/**/*.js",
    "resources/css/**/*.css"
)

# Search roots for keyword scanning
$searchRoots = @(
    "routes",
    "bootstrap",
    "app/Models",
    "app/Http/Controllers",
    "app/Http/Middleware",
    "app/Notifications",
    "app/Mail",
    "app/Providers",
    "config",
    "database/migrations",
    "resources/views",
    "resources/js",
    "resources/css",
    "app/View"
)

# Keywords likely related to admin protection, submission handling, auth, and notifications
$keywords = @(
    "listings.submit.store",
    "listings.submit.create",
    "Route::resource",
    "Route::get",
    "Route::post",
    "Route::middleware",
    "Route::prefix",
    "admin",
    "auth",
    "guest",
    "verified",
    "middleware",
    "is_admin",
    "Gate::define",
    "can:",
    "authorize",
    "policy",
    "Notification",
    "notify(",
    "Mail::",
    "Mailable",
    "ShouldQueue",
    "submission_status",
    "pending",
    "approved",
    "rejected",
    "is_active",
    "is_verified",
    "is_featured",
    "latitude",
    "longitude",
    "mail",
    "smtp",
    "MAIL_",
    "datalist",
    "service_type",
    "service type",
    "autocomplete",
    "combobox",
    "dropdown",
    "suggestion",
    "suggestions",
    "x-data",
    "x-show",
    "x-model",
    "alpine",
    "listbox",
    "filter service",
    "type to filter"
)

$outputFile = "chatgpt-file-export.txt"

# File extensions allowed in keyword scanning
$allowedExtensions = @(
    ".php",
    ".blade.php",
    ".js",
    ".css",
    ".ts",
    ".vue"
)

# Folders to skip during recursive scans
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

function Add-FileToOutput {
    param (
        [string]$FilePath,
        [string]$Tag = "FILE"
    )

    Write-SectionHeader "$($Tag): $FilePath"

    if (Test-Path -LiteralPath $FilePath -PathType Leaf) {
        Get-Content -LiteralPath $FilePath | Add-Content -Path $outputFile
    }
    else {
        Add-Content -Path $outputFile -Value "[FILE NOT FOUND]"
    }

    Add-Content -Path $outputFile -Value ""
    Add-Content -Path $outputFile -Value ""
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

# ----------------------------------------
# Start clean
# ----------------------------------------

if (Test-Path -LiteralPath $outputFile) {
    Remove-Item -LiteralPath $outputFile -Force
}

$exportedPaths = New-Object 'System.Collections.Generic.HashSet[string]'

# ----------------------------------------
# 1. Export explicit files
# ----------------------------------------

Write-SectionHeader "EXPLICIT FILE EXPORTS"

foreach ($file in $explicitFiles) {
    $normalized = Get-NormalizedPath $file

    if (-not $exportedPaths.Contains($normalized)) {
        [void]$exportedPaths.Add($normalized)
        Add-FileToOutput -FilePath $file -Tag "FILE"
    }
}

# ----------------------------------------
# 2. Export wildcard-matched files
# ----------------------------------------

Write-SectionHeader "PATTERN-MATCHED FILE EXPORTS"

foreach ($pattern in $extraFilePatterns) {
    Add-Content -Path $outputFile -Value ("PATTERN: " + $pattern)

    $matchedFiles = Get-ChildItem -Path $pattern -File -ErrorAction SilentlyContinue

    if (-not $matchedFiles -or $matchedFiles.Count -eq 0) {
        Add-Content -Path $outputFile -Value "[NO MATCHES]"
        Add-Content -Path $outputFile -Value ""
        continue
    }

    foreach ($matchedFile in $matchedFiles) {
        if (Test-SkippedPath $matchedFile.FullName) {
            continue
        }

        $normalized = Get-NormalizedPath $matchedFile.FullName

        if (-not $exportedPaths.Contains($normalized)) {
            [void]$exportedPaths.Add($normalized)
            Add-FileToOutput -FilePath $matchedFile.FullName -Tag "FILE"
        }
    }
}

# ----------------------------------------
# 3. Keyword scan to find likely hidden logic
# ----------------------------------------

Write-SectionHeader "KEYWORD SEARCH RESULTS"

$keywordMatchedFiles = New-Object 'System.Collections.Generic.HashSet[string]'

foreach ($root in $searchRoots) {
    Add-Content -Path $outputFile -Value ("SEARCH ROOT: " + $root)

    if (-not (Test-Path -LiteralPath $root -PathType Container)) {
        Add-Content -Path $outputFile -Value "[SEARCH ROOT NOT FOUND]"
        Add-Content -Path $outputFile -Value ""
        continue
    }

    $allFilesInRoot = Get-ChildItem -LiteralPath $root -Recurse -File -ErrorAction SilentlyContinue | Where-Object {
        $extensionMatch = $false

        foreach ($ext in $allowedExtensions) {
            if ($_.Name.ToLowerInvariant().EndsWith($ext.ToLowerInvariant())) {
                $extensionMatch = $true
                break
            }
        }

        $extensionMatch -and (-not (Test-SkippedPath $_.FullName))
    }

    foreach ($candidateFile in $allFilesInRoot) {
        $foundKeywordMatches = Select-String -Path $candidateFile.FullName -Pattern $keywords -SimpleMatch -CaseSensitive:$false -ErrorAction SilentlyContinue

        if ($foundKeywordMatches) {
            $normalized = Get-NormalizedPath $candidateFile.FullName

            if (-not $keywordMatchedFiles.Contains($normalized)) {
                [void]$keywordMatchedFiles.Add($normalized)

                Add-Content -Path $outputFile -Value ("MATCHED FILE: " + $candidateFile.FullName)

                foreach ($match in $foundKeywordMatches) {
                    $lineText = $match.Line.Trim()
                    Add-Content -Path $outputFile -Value ("  Line " + $match.LineNumber + ": " + $lineText)
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