$ErrorActionPreference = 'Stop'
if (Get-NetTCPConnection -State Listen -LocalAddress '127.127.126.32' -LocalPort 3306 -ErrorAction SilentlyContinue) {
    Write-Host 'MySQL is already listening on 127.127.126.32:3306.'
    return
}
$mysqlExe = 'C:\OSPanel\modules\MySQL-8.4\bin\mysqld.exe'
$configFile = 'C:\OSPanel\modules\MySQL-8.4\my.ini'
if (!(Test-Path -LiteralPath $mysqlExe) -or !(Test-Path -LiteralPath $configFile)) {
    throw 'Open Server MySQL 8.4 was not found.'
}
Start-Process -FilePath $mysqlExe -ArgumentList '--defaults-file=C:/OSPanel/modules/MySQL-8.4/my.ini' -WindowStyle Hidden
Write-Host 'MySQL start requested. Connect to trade in DBeaver after a few seconds.'