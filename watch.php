<?php
// fswatch -0 -x ./ | xargs -n1 -I{} php watch.php {}
// fswatch -0 -x ./ | xargs -0 -n 1 -I {} php watch.php {}
// * fswatch -0 -x ./ | xargs -0 -I {} php watch.php {}
/*

--no-spinner
X_LISTEN=0.0.0.0:8081 ./vendor/bin/php-watcher --watch src --watch resources public/index.php --ext=php,twig --no-spinner
Array
(
    [0] => watch.php
    [1] => /Users/rw/dev/play/xchan/watch.php AttributeModified IsFile Updated
)
Array
(
    [0] => watch.php
    [1] => /Users/rw/dev/play/xchan/resources/test Created IsFile
)
Array
(
    [0] => watch.php
    [1] => /Users/rw/dev/play/xchan/resources/test Created IsFile Renamed
)
Array
(
    [0] => watch.php
    [1] => /Users/rw/dev/play/xchan/resources/test2 IsFile Renamed
)

while (!feof(STDIN)) {
    $line = fgets(STDIN);
    print $line;
    print "\n";
}

*/
print_r($argv);
