$url = "https://windows.php.net/downloads/releases/archives/php-8.3.13-nts-Win32-vs16-x64.zip"
$zipFile = "$env:TEMP\php.zip"
$installDir = "C:\php"

Write-Host "Downloading PHP 8.3.13 from archives..."
Invoke-WebRequest -Uri $url -OutFile $zipFile -UseBasicParsing

Write-Host "Extracting to $installDir..."
If (Test-Path $installDir) { Remove-Item -Path $installDir -Recurse -Force }
New-Item -ItemType Directory -Force -Path $installDir
Expand-Archive -Path $zipFile -DestinationPath $installDir -Force

Write-Host "Configuring php.ini..."
if (Test-Path "$installDir\php.ini-production") {
    Copy-Item "$installDir\php.ini-production" "$installDir\php.ini" -Force
}
elseif (Test-Path "$installDir\php.ini-development") {
    Copy-Item "$installDir\php.ini-development" "$installDir\php.ini" -Force
}

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
