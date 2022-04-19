#/bin/bash

rm -rf ./vendor/
sudo apt-get install php-curl
sudo apt-get install php-mbstring 
./composer/composer.phar update
./composer/composer.phar install
