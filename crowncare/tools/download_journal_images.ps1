# Downloads the Journal photos from the CrownCare Wix site into images/journal/.
# Run from PowerShell:
#   powershell -ExecutionPolicy Bypass -File "C:\xampp\htdocs\E-comm_Team-Project\crowncare\tools\download_journal_images.ps1"

$target = Join-Path $PSScriptRoot "..\images\journal"
New-Item -ItemType Directory -Force -Path $target | Out-Null

$images = [ordered]@{
    0  = "67839a_67f8195489d1438395d54cae37962414~mv2.webp"
    2  = "67839a_498799c1713c4764af132046303fc651~mv2.webp"
    5  = "67839a_57d950a57162443c9dd6d92ad83ddb89~mv2.jpg"
    6  = "67839a_fc4d517623984f14b384340b71dcf62e~mv2.jpg"
    7  = "67839a_81ee9ea50fbb4cd9b293554010149dea~mv2.jpg"
    9  = "67839a_ab1f2a56988148529c7c3854e9a159bd~mv2.jpg"
    12 = "e1b2e8_83df5793b4ee4b35add7c5429fc4a462~mv2.webp"
    13 = "e1b2e8_3c3461ac7bd34e6890117c3c3a59580e~mv2.webp"
    15 = "e1b2e8_f66952bac70d490ca0d2d2b21fa6a8a9~mv2.webp"
}

foreach ($key in $images.Keys) {
    $url = "https://static.wixstatic.com/media/$($images[$key])/v1/fit/w_1600,h_1600,q_85/img.jpg"
    $file = Join-Path $target "journal-$key.jpg"
    try {
        Invoke-WebRequest -Uri $url -OutFile $file -UseBasicParsing
        Write-Host "Saved journal-$key.jpg"
    } catch {
        Write-Host "Could not download journal-$key.jpg: $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host "Done. Press Ctrl+F5 on the Journal page."
