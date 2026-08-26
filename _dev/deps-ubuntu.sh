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
  libbz2-dev libxslt1-dev libffi-dev \
  libpq-dev  # required to build pdo_pgsql for PostgreSQL

echo "Done installing dependencies."
echo
echo "Enable pdo_pgsql (PostgreSQL) for the installed vfox-php:"
echo "  # NOTE: 'mise install php@8.1' uses a DIFFERENT build (asdf 'php' plugin)."
echo "  # Build pdo_pgsql as a shared extension against the installed PHP instead:"
echo "  cd /tmp && rm -rf pdo_pgsql_build && mkdir pdo_pgsql_build && cd pdo_pgsql_build"
echo "  tar xzf ~/.local/share/mise/downloads/vfox-version-fox-vfox-php-8.1.34/php-8.1.34.tar.gz"
echo "  cd php-8.1.34/ext/pdo_pgsql"
echo "  phpize && ./configure --with-pdo-pgsql=/usr && make -j\$(nproc)"
echo "  cp modules/pdo_pgsql.so ~/.local/share/mise/installs/vfox-version-fox-vfox-php/8.1.34/lib/php/extensions/no-debug-non-zts-20210902/"
echo "  echo 'extension=pdo_pgsql.so' > ~/.local/share/mise/installs/vfox-version-fox-vfox-php/8.1.34/conf.d/pdo_pgsql.ini"
echo
echo "Verify extensions:"
echo "  php -m | grep -Ei 'xml|openssl|mbstring|intl|zip|curl|sqlite|gd|sodium|gmp|readline|pdo_pgsql'"
echo "  composer check-platform-reqs"
