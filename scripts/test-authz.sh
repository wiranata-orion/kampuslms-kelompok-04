#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."
PHP_BIN="${PHP_BIN:-php}"

APP_ENV=testing \
DB_CONNECTION=sqlite \
DB_DATABASE=:memory: \
CACHE_STORE=array \
SESSION_DRIVER=array \
"$PHP_BIN" vendor/bin/phpunit --no-configuration --bootstrap vendor/autoload.php \
	tests/Feature/AuthorizationPolicyTest.php
