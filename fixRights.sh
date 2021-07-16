#!/bin/bash

BASEDIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

cd $BASEDIR
./bin/console kimai:update

chown -R :www-data .
chmod -R g+r .
chmod -R g+rw var/
chmod -R g+rw public/avatars/
