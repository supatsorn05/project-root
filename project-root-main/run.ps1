param([int]$ApiPort=8080, [int]$WebPort=5173)
$ErrorActionPreference = "Stop"

# --- find php & npm ---
$phpCmd = (Get-Command php -ErrorAction SilentlyContinue).Source
if (-not $phpCmd) { throw "PHP not found in PATH" }

$npmCmd = (Get-Command npm.cmd -ErrorAction SilentlyContinue).Source
if (-not $npmCmd) { $npmCmd = (Get-Command npm -ErrorAction SilentlyContinue).Source }
if (-not $npmCmd) { throw "npm not found in PATH" }

# --- install frontend deps ---
Push-Location frontend
if (Test-Path package-lock.json) { & $npmCmd ci } else { & $npmCmd install }
Pop-Location

# --- start PHP built-in server ---
$php = Start-Process -PassThru -NoNewWindow -FilePath $phpCmd `
  -ArgumentList "-S","127.0.0.1:$ApiPort","-t","backend"

# --- start Vite via cmd.exe (/c "<npm> run dev ...") to avoid Win32 error ---
$viteArgs = "/c `"$npmCmd`" run dev -- --host 0.0.0.0 --port $WebPort"
$vite = Start-Process -PassThru -NoNewWindow -FilePath "cmd.exe" `
  -ArgumentList $viteArgs -WorkingDirectory "frontend"

Write-Host "`nFrontend: http://localhost:$WebPort"
Write-Host "Backend : http://127.0.0.1:$ApiPort  (e.g. /api/health.php)"
Write-Host "Press Ctrl+C to stop.`n"

# wait until Vite exits, then stop PHP
Wait-Process -Id $vite.Id
try { Stop-Process -Id $php.Id -ErrorAction SilentlyContinue } catch {}
