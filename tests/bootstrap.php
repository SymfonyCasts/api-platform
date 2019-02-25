<?php

if (isset($_ENV['BOOTSTRAP_RESET_DATABASE']) && $_ENV['BOOTSTRAP_RESET_DATABASE'] === '1') {
    passthru( '
        APP_ENV=test php bin/console doctrine:schema:drop --force;
        APP_ENV=test php bin/console doctrine:schema:update --force;
        APP_ENV=test php bin/console doctrine:fixtures:load -n;
    '
    );
    echo 'Recreated the database schema and loaded the DataFixture' . PHP_EOL;
}

require __DIR__.'/../config/bootstrap.php';
