$baseUri = "https://windows.php.net/downloads/releases/"
$archiveUri = "https://windows.php.net/downloads/releases/archives/"

Write-Host "Finding PHP version..."
try {
    $content = (Invoke-WebRequest -Uri $baseUri -UseBasicParsing).Content
    # Match php-8.3.*-nts-Win32-vs16-x64.zip
    if ($content -match '(php-8\.3\.\d+-nts-Win32-vs16-x64\.zip)') {
        $filename = $matches[1]
        $downloadUrl = $baseUri + $filename
    }
    else {
        # Try archives if releases fails
        throw "No 8.3 version found in releases"
    }
}
catch {
    Write-Host "Checking archives..."
    $content = (Invoke-WebRequest -Uri $archiveUri -UseBasicParsing).Content
    if ($content -match '(php-8\.3\.\d+-nts-Win32-vs16-x64\.zip)') {
        $filename = $matches[1]
        $downloadUrl = $archiveUri + $filename
    }
    else {
        throw "Could not find a valid PHP version."
    }
}

$zipFile = "$env:TEMP\php.zip"
$installDir = "C:\php"

Write-Host "Downloading $filename..."
Invoke-WebRequest -Uri $downloadUrl -OutFile $zipFile -UseBasicParsing

Write-Host "Extracting to $installDir..."
If (!(Test-Path $installDir)) { New-Item -ItemType Directory -Force -Path $installDir }
Expand-Archive -Path $zipFile -DestinationPath $installDir -Force

Write-Host "Configuring php.ini..."
Copy-Item "$installDir\php.ini-production" "$installDir\php.ini" -Force
$phpIni = Get-Content "$installDir\php.ini"
$phpIni = $phpIni -replace ';extension=curl', 'extension=curl'
$phpIni = $phpIni -replace ';extension=mbstring', 'extension=mbstring'
$phpIni = $phpIni -replace ';extension=openssl', 'extension=openssl'
$phpIni | Set-Content "$installDir\php.ini"

Write-Host "Adding PHP to User Environment Path..."
[System.Environment]::SetEnvironmentVariable("Path", $env:Path + ";$installDir", [System.EnvironmentVariableTarget]::User)
$env:Path += ";$installDir"

Write-Host "PHP Installed Successfully!"
php -v
