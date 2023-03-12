just playing...

    composer install
    X_LISTEN=0.0.0.0:8081 php public/index.php

    # login with any name
    http://127.0.0.1:8081

    # test with websocat
    websocat ws://127.0.0.1:8081/ws

    # maybe use php-watcher
    X_LISTEN=0.0.0.0:8081 ./vendor/bin/php-watcher --watch src --watch resources public/index.php --ext=php,twig --no-spinner

    composer require seregazhuk/php-watcher:dev-master
    X_LISTEN=0.0.0.0:8081 ./vendor/bin/php-watcher --watch src --watch resources --ext=php,twig public/index.php

## TODO

    [o] auth
    [ ] validations
    [ ] email
    [ ] refresh token
    [ ] pubsub
    [ ] build/ watch
    [ ] some load tests
