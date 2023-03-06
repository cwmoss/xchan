<?php

use Ratchet\RFC6455\Messaging\Message;
use Voryx\WebSocketMiddleware\WebSocketConnection;
use Voryx\WebSocketMiddleware\WebSocketMiddleware;
use xchan\sqlite;
use function xchan\dbg;

$factory = new Clue\React\SQLite\Factory();
$db = $factory->openLazy(__DIR__ . '/../var/app.db');

$store = new sqlite($db);

$ws = new WebSocketMiddleware(['/ws'], function (WebSocketConnection $conn) {
    $conn->on('message', function (Message $message) use ($conn) {
        dbg("++ message", (string) $message);
        $conn->send($message);
    });
});

$container = new FrameworkX\Container([]);
$app = new FrameworkX\App($container, $ws);

$hostport = $container->getEnv('X_LISTEN') ?? '127.0.0.1:8080';
dbg("running on $hostport");

$app->get('/', function () use ($store, $hostport) {
    $posts = $store->select('SELECT * from posts');
    ob_start();
    include(__DIR__ . '/../resources/posts.html');
    $html = ob_get_clean();
    return React\Http\Message\Response::html(
        $html
    );
});

$app->get('/posts/{id}', function (Psr\Http\Message\ServerRequestInterface $request) use ($store, $hostport) {
    $id = $request->getAttribute('id');
    $post = $store->select_first_row('SELECT * from posts WHERE id=:id', ['id' => $id]);
    $replies = $store->select('SELECT * from replies WHERE post_id=:id', ['id' => $id]);
    ob_start();
    include(__DIR__ . '/../resources/post.html');
    $html = ob_get_clean();
    return React\Http\Message\Response::html(
        $html
    );
});

$app->get('/users/{name}', function (Psr\Http\Message\ServerRequestInterface $request) {
    return React\Http\Message\Response::plaintext(
        "Hello " . $request->getAttribute('name') . "!!\n" . memory_get_usage()
    );
});

$app->run();
