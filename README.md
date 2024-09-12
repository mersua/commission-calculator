# Commission calculator

To set up project's .env file:

    cd {progect/root/folder}
    copy .env.sample to .env
    change CURRENCY_API_KEY to actual value (please, take it from https://exchangeratesapi.io/)
    rest env's vars have already been defined

To run CLI app:

    composer install --no-dev
    php src/app.php input.txt

To run CLI tests:

    composer install
    vendor/bin/phpunit --configuration phpunit.xml