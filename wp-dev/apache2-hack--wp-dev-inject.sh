#!/bin/bash

### ATTENTION!!! This file has to start with `apache2` :) -- It is a hack.

set -eu

if [ -e /var/www/html/wp-config.php ]; then
  if [ ! -e /var/www/html/wp-config.bak ]; then
    cp /var/www/html/wp-config.php /var/www/html/wp-config.bak
  fi

  echo "<?php @include_once('wp-dev/wp-dev.php'); ?>" | cat - /var/www/html/wp-config.bak > /var/www/html/wp-config.php
  echo "Injected wp-dev into wp-config.php"
else
  echo "NO wp-config.php FOUND!"
fi

exec "php-fpm"
