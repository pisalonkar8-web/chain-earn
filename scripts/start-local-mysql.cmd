@echo off
if not exist "%~dp0..\.local\mysql\mysql" (
    echo Local MySQL data is not initialized.
    exit /b 1
)
start "Chain Earn MySQL" /B "C:\Program Files\MySQL\MySQL Server 8.4\bin\mysqld.exe" --no-defaults --datadir="%~dp0..\.local\mysql" --bind-address=127.0.0.1 --port=3306 --mysqlx=OFF --log-error=chain-earn.err > "%~dp0..\.local\mysql-start.log" 2>&1
echo Local MySQL starting on 127.0.0.1:3306. Check .local/mysql/chain-earn.err if it fails.
