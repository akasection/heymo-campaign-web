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
  mingw-w64-x86_64-readline \
  mingw-w64-x86_64-postgresql  # required to build pdo_pgsql for PostgreSQL" | Out-Host

Write-Host "Done."
Write-Host ""
Write-Host "Next (new terminal):"
Write-Host "  # NOTE: 'mise install php@8.1' uses a DIFFERENT build (asdf 'php' plugin)."
Write-Host "  # pdo_pgsql is not compiled in by default by vfox-php; append"
Write-Host "  # '--with-pdo-pgsql' to the plugin's bin/install configure flags, then:"
Write-Host "  mise install vfox:version-fox/vfox-php@8.1"
Write-Host "  mise use -p vfox:version-fox/vfox-php@8.1"
Write-Host ""
Write-Host "Verify:"
Write-Host "  php -m"
