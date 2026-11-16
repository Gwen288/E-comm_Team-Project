# CrownCare: delete the brand pages and old files that are no longer used.
# Run from the crowncare folder:  powershell -ExecutionPolicy Bypass -File .\remove_brand_files.ps1
$files = @(
  'actions\add_brand_action.php',
  'actions\update_brand_action.php',
  'actions\delete_brand_action.php',
  'views\admin\brand.php',
  'views\admin\brand_form.php',
  'database\migrations\003_crowncare_products.sql'
)
foreach ($f in $files) {
  $path = Join-Path $PSScriptRoot $f
  if (Test-Path $path) { Remove-Item $path; Write-Host "Deleted $f" } else { Write-Host "Already gone: $f" }
}
Write-Host 'Done.'
