# Starts local MySQL (if not running) and the Laravel dev server.
# Usage (PowerShell, from this folder):  .\start-dev.ps1
$bin  = "C:\Program Files\MySQL\MySQL Server 8.4\bin"
$data = "C:\Users\sagar\mysql-data"

if (-not (Get-Process mysqld -ErrorAction SilentlyContinue)) {
    Start-Process "$bin\mysqld.exe" -ArgumentList "--datadir=`"$data`"", "--port=3306", "--console" `
        -WindowStyle Hidden -RedirectStandardError "$data\err.log"
    Start-Sleep 8
}

$env:OPENSSL_CONF = "C:\Users\sagar\tools\openssl.cnf"   # needed for web-push key generation
php artisan serve --port=8000
