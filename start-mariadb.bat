@echo off
echo Starting MariaDB server...
start /b "" "D:\tools\mariadb\bin\mysqld.exe" --defaults-file="D:\tools\mariadb\data\my.ini" --console
echo MariaDB server started on port 3306.
