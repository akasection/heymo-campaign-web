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
  libpng freetype jpeg webp libsodium gmp readline zlib libxslt \
  libpq  # required to build pdo_pgsql for PostgreSQL

echo "Done installing dependencies."
echo
echo "Next:"
echo "  # NOTE: 'mise install php@8.1' uses a DIFFERENT build (asdf 'php' plugin)."
echo "  # pdo_pgsql is not compiled in by default by vfox-php; on macOS append"
echo "  # '--with-pdo-pgsql' to the plugin's bin/install configure flags, then:"
echo "  mise install vfox:version-fox/vfox-php@8.1"
echo "  mise use -p vfox:version-fox/vfox-php@8.1"
echo
echo "Verify extensions:"
echo "  php -m | grep -Ei 'xml|openssl|mbstring|intl|zip|curl|sqlite|gd|sodium|gmp|readline|pdo_pgsql'"
