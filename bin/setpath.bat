SetLocal EnableDelayedExpansion

set KeyName=Path
set OldInstallDir=%1
set NewInstallDir=%2

if "%OldInstallDir%" == "" (
    echo Old installation directory missing. Exiting.
) else (
    if "%NewInstallDir%" == "" (
        echo New installation directory missing. Exiting.
    ) else (
        set OldPath=%Path%
        set NewPath=!OldPath:%OldInstallDir%=!
        set NewPathWithNewInstallDir=!%NewPath%%NewInstallDir%!

        echo %NewPathWithNewInstallDir%

        setx /m "%KeyName%" "%NewPathWithNewInstallDir%";

        pause
    )
)

EndLocal