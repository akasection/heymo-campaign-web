#!/usr/bin/env bash
set -euo pipefail

echo "Installing PHP build dependencies for mise on Ubuntu/Debian..."

# Must run as root/sudo
if [[ "${EUID:-$(id -u)}" -ne 0 ]]; then
  echo "Please run with sudo: sudo bash $0"
  exit 1
fi

apt update

# Core build toolchain + pkg-config
apt install -y \
  build-essential pkg-config autoconf bison re2c automake libtool

# Libraries for PHP extensions (xml, openssl, mbstring, intl, zip, curl, sqlite, gd, etc.)
apt install -y \
  libxml2-dev libssl-dev libonig-dev libicu-dev \
  libzip-dev libcurl4-openssl-dev libsqlite3-dev \
  libpng-dev libfreetype6-dev libjpeg-dev libwebp-dev \
  libsodium-dev libgmp-dev libreadline-dev zlib1g-dev \
  libbz2-dev libxslt1-dev libffi-dev

echo "Done installing dependencies."
echo
echo "Next:"
echo "  mise uninstall php@8.1 || true"
echo "  mise install php@8.1"
echo "  mise use -p php@8.1"
echo
echo "Verify extensions:"
echo "  php -m | grep -Ei 'xml|openssl|mbstring|intl|zip|curl|sqlite|gd|sodium|gmp|readline'"
echo "  composer check-platform-reqs"
