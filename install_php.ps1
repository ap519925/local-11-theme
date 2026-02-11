$version = "8.3.16"
$downloadUrl = "https://windows.php.net/downloads/releases/php-$version-nts-Win32-vs16-x64.zip"
$zipFile = "$env:TEMP\php.zip"
$installDir = "C:\php"

Write-Host "Downloading PHP $version..."
try {
    Invoke-WebRequest -Uri $downloadUrl -OutFile $zipFile
} catch {
    Write-Host "Version $version not found, trying 8.3.15..."
    $version = "8.3.15"
    $downloadUrl = "https://windows.php.net/downloads/releases/php-$version-nts-Win32-vs16-x64.zip"
    Invoke-WebRequest -Uri $downloadUrl -OutFile $zipFile
}

Write-Host "Extracting to $installDir..."
# Force creation of directory
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
