#!/bin/bash

git reset --hard HEAD
git pull
composer install
yarn install

php artisan cache:clear
php artisan config:clear

php artisan migrate --force
yarn run prod
