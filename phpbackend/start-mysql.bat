@echo off
REM Start MySQL from XAMPP

echo Starting MySQL from XAMPP...
echo.

REM Try different XAMPP installation paths
if exist "C:\xampp\mysql\bin\mysqld.exe" (
    echo Found XAMPP at C:\xampp
    "C:\xampp\mysql\bin\mysqld.exe" --console
    goto end
)

if exist "C:\Program Files\xampp\mysql\bin\mysqld.exe" (
    echo Found XAMPP at C:\Program Files\xampp
    "C:\Program Files\xampp\mysql\bin\mysqld.exe" --console
    goto end
)

if exist "C:\Program Files (x86)\xampp\mysql\bin\mysqld.exe" (
    echo Found XAMPP at C:\Program Files (x86)\xampp
    "C:\Program Files (x86)\xampp\mysql\bin\mysqld.exe" --console
    goto end
)

echo.
echo ERROR: XAMPP MySQL not found!
echo Please ensure XAMPP is installed or start MySQL manually.
echo.
pause

:end
