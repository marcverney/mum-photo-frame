#!/bin/sh

# Download installer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"

# Check signature
EXPECTED_SIGNATURE=$(curl https://composer.github.io/installer.sig)
ACTUAL_SIGNATURE=$(php -r "echo hash_file('SHA384', 'composer-setup.php');")
if [ "$ACTUAL_SIGNATURE" != "$EXPECTED_SIGNATURE" ]; then
    >&2 echo 'ERROR: Invalid installer signature'
    rm composer-setup.php
    exit 1
fi

# Install
php composer-setup.php && mv composer.phar /usr/local/bin/composer
RESULT=$?
rm composer-setup.php
exit "$RESULT"
