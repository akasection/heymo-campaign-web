#!/usr/bin/env bash
set -euo pipefail

echo "Installing PHP build dependencies for mise on macOS..."

# Ensure Homebrew exists
if ! command -v brew >/dev/null 2>&1; then
  echo "Homebrew is not installed. Install it first: https://brew.sh"
  exit 1
fi

brew update

# Core build tooling
brew install \
  pkg-config autoconf bison re2c automake libtool

# Libraries for PHP extensions
brew install \
  libxml2 openssl@3 oniguruma icu4c libzip curl sqlite \
  libpng freetype jpeg webp libsodium gmp readline zlib libxslt

echo "Done installing dependencies."
echo
echo "Next:"
echo "  mise uninstall php@8.1 || true"
echo "  mise install php@8.1"
echo "  mise use -p php@8.1"
echo
echo "Verify extensions:"
echo "  php -m | grep -Ei 'xml|openssl|mbstring|intl|zip|curl|sqlite|gd|sodium|gmp|readline'"
