#Requires -RunAsAdministrator
$ErrorActionPreference = "Stop"

Write-Host "Installing PHP build dependencies for mise on Windows..."

# Check choco
if (-not (Get-Command choco -ErrorAction SilentlyContinue)) {
  Write-Error "Chocolatey is not installed. Install from https://chocolatey.org/install"
  exit 1
}

choco upgrade chocolatey -y

# Build and tooling
choco install -y git 7zip strawberryperl nasm yasm visualstudio2022buildtools

# pkg-config and common libs via msys2
choco install -y msys2

# Install libs inside MSYS2 (for pkg-config discoverability)
$msys = "C:\tools\msys64\usr\bin\bash.exe"
if (-not (Test-Path $msys)) {
  Write-Error "MSYS2 bash not found at $msys"
  exit 1
}

& $msys -lc "pacman -Syu --noconfirm" | Out-Host
& $msys -lc "pacman -S --needed --noconfirm \
  pkgconf \
  mingw-w64-x86_64-libxml2 \
  mingw-w64-x86_64-openssl \
  mingw-w64-x86_64-oniguruma \
  mingw-w64-x86_64-icu \
  mingw-w64-x86_64-libzip \
  mingw-w64-x86_64-curl \
  mingw-w64-x86_64-sqlite3 \
  mingw-w64-x86_64-libpng \
  mingw-w64-x86_64-freetype \
  mingw-w64-x86_64-libjpeg-turbo \
  mingw-w64-x86_64-libwebp \
  mingw-w64-x86_64-libsodium \
  mingw-w64-x86_64-gmp \
  mingw-w64-x86_64-readline" | Out-Host

Write-Host "Done."
Write-Host ""
Write-Host "Next (new terminal):"
Write-Host "  mise uninstall php@8.1"
Write-Host "  mise install php@8.1"
Write-Host "  mise use -p php@8.1"
Write-Host ""
Write-Host "Verify:"
Write-Host "  php -m"
