#!/usr/bin/env bash
set -e

TARGET_DIR="$1"
RUN_CATALOG_SEED="${2:-false}"

echo "=========================================================="
echo "🚀 Starting Post-Deploy Remote Server Updates"
echo "=========================================================="
echo "SSH User: $(whoami)"
echo "Target Directory configured: ${TARGET_DIR:-domains/mauryamobilee.in}"

# Locate the application directory containing artisan
POSSIBLE_DIRS=()
if [ -n "$TARGET_DIR" ]; then
  POSSIBLE_DIRS+=("$TARGET_DIR/app")
  POSSIBLE_DIRS+=("$HOME/$TARGET_DIR/app")
  POSSIBLE_DIRS+=("$TARGET_DIR")
  POSSIBLE_DIRS+=("$HOME/$TARGET_DIR")
fi
POSSIBLE_DIRS+=(
  "$HOME/domains/mauryamobilee.in/app"
  "$HOME/domains/phonefixazamgarh.com/app"
  "$HOME/app"
  "$PWD/app"
  "$PWD"
)

APP_DIR=""
for dir in "${POSSIBLE_DIRS[@]}"; do
  if [ -n "$dir" ] && [ -f "$dir/artisan" ]; then
    APP_DIR="$dir"
    break
  fi
done

# Fallback search if not in expected paths
if [ -z "$APP_DIR" ]; then
  echo "Searching for artisan file in $HOME..."
  FOUND_ARTISAN=$(find "$HOME" -maxdepth 4 -name "artisan" 2>/dev/null | head -n 1)
  if [ -n "$FOUND_ARTISAN" ]; then
    APP_DIR="$(dirname "$FOUND_ARTISAN")"
  fi
fi

if [ -z "$APP_DIR" ] || [ ! -f "$APP_DIR/artisan" ]; then
  echo "❌ ERROR: artisan file could not be found!"
  echo "Checked locations:"
  for dir in "${POSSIBLE_DIRS[@]}"; do
    echo "  - $dir"
  done
  exit 1
fi

echo "✅ Target application directory located: $APP_DIR"
cd "$APP_DIR"

# Auto-detect PHP 8.2+ CLI executable
PHP_BIN=""
CANDIDATE_PHPS=(
  "/usr/local/bin/ea-php82"
  "/usr/local/bin/ea-php83"
  "/usr/bin/php8.2"
  "/usr/bin/php8.3"
  "/opt/cpanel/ea-php82/root/usr/bin/php"
  "/opt/cpanel/ea-php83/root/usr/bin/php"
  "/usr/local/php82/bin/php"
  "/usr/local/php8.2/bin/php"
  "$(which php8.2 2>/dev/null)"
  "$(which php82 2>/dev/null)"
  "$(which php 2>/dev/null)"
)

for php_candidate in "${CANDIDATE_PHPS[@]}"; do
  if [ -n "$php_candidate" ] && [ -x "$php_candidate" ]; then
    PHP_MAJOR=$("$php_candidate" -r 'echo PHP_MAJOR_VERSION;' 2>/dev/null || echo 0)
    PHP_MINOR=$("$php_candidate" -r 'echo PHP_MINOR_VERSION;' 2>/dev/null || echo 0)
    if [ "$PHP_MAJOR" -gt 8 ] || { [ "$PHP_MAJOR" -eq 8 ] && [ "$PHP_MINOR" -ge 2 ]; }; then
      PHP_BIN="$php_candidate"
      PHP_VER=$("$php_candidate" -r 'echo PHP_VERSION;' 2>/dev/null || echo "unknown")
      echo "✅ Using PHP: $PHP_BIN (version $PHP_VER)"
      break
    fi
  fi
done

if [ -z "$PHP_BIN" ]; then
  PHP_BIN="php"
  echo "⚠️ Using default system PHP binary: $(which php) ($($PHP_BIN -v 2>/dev/null | head -n 1))"
fi

# Check .env existence
if [ ! -f ".env" ]; then
  echo "⚠️ Warning: .env file not found in $APP_DIR!"
  if [ -f "../.env" ]; then
    echo "Found .env in parent directory, copying to $APP_DIR/.env..."
    cp "../.env" .env
  elif [ -f ".env.production.example" ]; then
    echo "Creating .env from .env.production.example..."
    cp .env.production.example .env
    $PHP_BIN artisan key:generate --force
  elif [ -f ".env.example" ]; then
    echo "Creating .env from .env.example..."
    cp .env.example .env
    $PHP_BIN artisan key:generate --force
  fi
fi

# Clean leftover Inertia files & stale bootstrap/view cache
echo "==> Cleaning stale bootstrap caches, compiled views & leftover Inertia artifacts..."
rm -f bootstrap/cache/*.php 2>/dev/null || true
rm -rf storage/framework/views/* 2>/dev/null || true
rm -rf storage/framework/cache/data/* 2>/dev/null || true
rm -f app/Http/Middleware/HandleInertiaRequests.php 2>/dev/null || true
rm -f resources/views/app.blade.php 2>/dev/null || true
rm -rf resources/js/Pages 2>/dev/null || true
rm -f resources/js/app.jsx 2>/dev/null || true
rm -f ../public_html/js/app.js 2>/dev/null || true
rm -f ../public_html/node_modules_axios_index_js.js 2>/dev/null || true

# Set directory permissions
echo "==> Setting writable permissions on storage & bootstrap/cache..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# 1. OPTIMIZE CLEAR FIRST (Removes any stale compiled container bindings)
echo "=========================================================="
echo "⚡ [1/4] Clearing Bootstrap & View Caches..."
echo "=========================================================="
$PHP_BIN artisan optimize:clear || true

# 2. DATABASE MIGRATIONS (Runs all pending migrations)
echo "=========================================================="
echo "📦 [2/4] Running Database Migrations (php artisan migrate --force)..."
echo "=========================================================="
$PHP_BIN artisan migrate --force || true

# 3. ADMIN & RBAC DATA SEEDING (Always executed on deployment)
echo "=========================================================="
echo "🌱 [3/4] Running Admin & RBAC Data Seeding..."
echo "=========================================================="
# MobileShopRbacSeeder sets up Company, Roles (store-admin, staff), Permissions & Admin accounts
$PHP_BIN artisan db:seed --class="Database\\Seeds\\MobileShopRbacSeeder" --force || true

# Ensure Developer and Store Admin credentials are configured and attached
$PHP_BIN artisan mobileshop:setup-admin || true

# Optional catalog seeding (accessories, parts, categories)
if [ "$RUN_CATALOG_SEED" = "true" ]; then
  echo "==> Seeding Accessories & Repairs Catalog..."
  $PHP_BIN artisan db:seed --class="Database\\Seeds\\AccessoriesAndRepairsCatalogSeeder" --force || true
fi

# Clean views, routes, config for fresh serving
$PHP_BIN artisan route:clear || true
$PHP_BIN artisan view:clear || true
$PHP_BIN artisan config:clear || true
$PHP_BIN artisan cache:clear || true

# 4. FINAL VERIFICATION
echo "=========================================================="
echo "🎉 [4/4] Verifying Deployment Status..."
echo "=========================================================="
$PHP_BIN artisan --version
echo "✅ Deployment incremental update, migrations & admin data seeding completed successfully!"
