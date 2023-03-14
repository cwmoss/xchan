<?php

use Ratchet\RFC6455\Messaging\Message;
use Voryx\WebSocketMiddleware\WebSocketConnection;
use Voryx\WebSocketMiddleware\WebSocketMiddleware;
use React\Stream\ThroughStream;
use Psr\Http\Message\ServerRequestInterface as R;
use React\Http\Message\Response as P;

use xchan\sqlite;
use xchan\auth;

use function xchan\dbg;
use function xchan\template;

$base = __DIR__ . '/../';

// sleep(2);
// `touch $base/resources/testx.php`;
`composer build`;

$broadcast = new ThroughStream();

/*
$factory = new Clue\React\SQLite\Factory();
$db = $factory->openLazy(__DIR__ . '/../var/app.db');
$store = new sqlite($db);
$authstore = new auth\store($store);
$auth = new xchan\auth($authstore, 'xchan', ['views' => __DIR__ . '/../resources']);
*/

$ws = new WebSocketMiddleware(['/ws'], function (WebSocketConnection $conn) use ($broadcast) {
    $broadcast->on('data', function ($data) use ($conn) {
        $conn->send($data);
    });

    $conn->on('message', function (Message $message) use ($conn, $broadcast) {
        dbg("++ message", (string) $message);
        // $conn->send($message);
        $broadcast->write($message);
    });
});


$config = include(__DIR__ . '/_config.php');
$container = new FrameworkX\Container($config);

$app = new FrameworkX\App($container, $ws, xchan\auth::class);

$hostport = $container->getEnv('X_LISTEN') ?? '127.0.0.1:8080';
dbg("running on $hostport");

$app->get('/', \xchan\controller\post::class);
$app->get('/posts/{id}', \xchan\controller\post::class);
$app->post('/posts', \xchan\controller\post::class);

$app->get('/options', \xchan\controller\options::class);

$app->post('/upload/avatar', \xchan\controller\upload::class);

$app->get('/users/{name}', function (Psr\Http\Message\ServerRequestInterface $request) {
    return React\Http\Message\Response::plaintext(
        "Hello " . $request->getAttribute('name') . "!!\n" . memory_get_usage()
    );
});


$app->get('/assets/{path}', new FrameworkX\FilesystemHandler(__DIR__ . '/../public/assets/'));
$app->get('/avatar/{path}', new FrameworkX\FilesystemHandler(__DIR__ . '/../var/avatar/'));



$app->run();
