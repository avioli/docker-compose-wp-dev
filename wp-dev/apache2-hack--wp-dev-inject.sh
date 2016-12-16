#!/bin/bash

### ATTENTION!!! This file has to start with `apache2` :) -- It is a hack.

set -e

[ -z "$WP_CORE_DIR" ] && WP_CORE_DIR=/var/www/html

if [ -e "$WP_CORE_DIR/wp-config.php" ]; then
  if [ ! -e "$WP_CORE_DIR/wp-config.bak" ]; then
    cp "$WP_CORE_DIR/wp-config.php" "$WP_CORE_DIR/wp-config.bak"
  fi

  echo "<?php @include_once('wp-dev/wp-dev.php'); ?>" | cat - "$WP_CORE_DIR/wp-config.bak" > "$WP_CORE_DIR/wp-config.php"
  echo "Injected wp-dev into wp-config.php"
else
  echo "NO wp-config.php FOUND!"
fi

exec "php-fpm"
