just playing...

X_LISTEN=0.0.0.0:8081 ./vendor/bin/php-watcher --watch src --watch resources public/index.php --ext=php,twig

composer require seregazhuk/php-watcher:dev-master
X_LISTEN=0.0.0.0:8081 ./vendor/bin/php-watcher --watch src --watch resources --ext=php,twig public/index.php
