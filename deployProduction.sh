#!/bin/bash

BASEDIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

cd $BASEDIR
chown -R kimai:www-data .

composer install --no-dev --optimize-autoloader -n
./bin/console kimai:update

chmod -R g+r .
chmod -R g+rw var/
chmod -R g+rw public/avatars/
