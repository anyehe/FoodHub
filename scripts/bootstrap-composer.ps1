$ErrorActionPreference = 'Stop'
Set-Location (Join-Path $PSScriptRoot '..\backend')

Write-Host 'FoodHub Composer bootstrap' -ForegroundColor Cyan

if (-not (Get-Command composer -ErrorAction SilentlyContinue)) {
    throw 'Composer was not found on PATH. Install Composer, close this terminal, open a new terminal, and retry.'
}

composer validate --no-check-lock
if ($LASTEXITCODE -ne 0) { throw 'composer.json validation failed.' }

if (Test-Path 'composer.lock') {
    Write-Host 'composer.lock found. Installing locked dependencies...' -ForegroundColor Green
    composer install --prefer-dist --no-interaction
    if ($LASTEXITCODE -ne 0) { throw 'Composer install failed.' }
    exit 0
}

Write-Warning 'composer.lock is not present in this source package. A first dependency resolution is required.'
Write-Host 'Testing access to Packagist over HTTPS...' -ForegroundColor Yellow

# Composer can be affected by IPv6 routing problems on Windows. Try a direct
# IPv4 HTTPS request first; if that works, force Composer to use IPv4.
$ipv4Ok = $false
try {
    $curl = & curl.exe -4 -I --silent --show-error --max-time 20 https://repo.packagist.org/packages.json 2>&1
    if ($LASTEXITCODE -eq 0) { $ipv4Ok = $true }
} catch {
    $ipv4Ok = $false
}

if ($ipv4Ok) {
    Write-Host 'Packagist is reachable over IPv4. Forcing Composer to use IPv4 for this run.' -ForegroundColor Green
    $env:COMPOSER_IPRESOLVE = '4'
} else {
    $test = Test-NetConnection repo.packagist.org -Port 443 -WarningAction SilentlyContinue
    if (-not $test.TcpTestSucceeded) {
        Write-Host ''
        Write-Host 'Network diagnostics:' -ForegroundColor Yellow
        try { Resolve-DnsName repo.packagist.org -ErrorAction Stop | Format-Table Name,Type,IPAddress } catch { Write-Warning 'DNS lookup for repo.packagist.org failed.' }
        Write-Host ('HTTPS_PROXY=' + ($env:HTTPS_PROXY ?? '<not set>'))
        Write-Host ('HTTP_PROXY=' + ($env:HTTP_PROXY ?? '<not set>'))
        Write-Host ''
        throw 'Cannot reach repo.packagist.org:443. The project files are valid, but Composer cannot obtain dependency metadata. Check DNS/firewall/proxy/VPN/Internet access, then rerun this script.'
    }
}

Write-Host 'Resolving dependencies and creating composer.lock...' -ForegroundColor Yellow
composer update --no-install --prefer-dist --no-interaction
if ($LASTEXITCODE -ne 0) { throw 'Composer dependency resolution failed.' }

composer install --prefer-dist --no-interaction
if ($LASTEXITCODE -ne 0) { throw 'Composer install failed after lock generation.' }

Write-Host 'Composer bootstrap completed successfully.' -ForegroundColor Green
