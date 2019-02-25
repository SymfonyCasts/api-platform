#!/usr/bin/env bash
# To create the database run once:
# APP_ENV=test php console doctrine:schema:create;
APP_ENV=test php console doctrine:schema:drop --force;
APP_ENV=test php console doctrine:schema:update --force;
APP_ENV=test php console doctrine:fixtures:load -n;
php phpunit $@
