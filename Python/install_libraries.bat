@echo off
echo Installing required Python libraries...

REM Ensure pip is up-to-date
python -m pip install --upgrade pip

REM Install each required library
python -m pip install mysql-connector-python

echo All libraries have been installed.
pause