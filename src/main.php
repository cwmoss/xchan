<?php

use Ratchet\RFC6455\Messaging\Message;
use Voryx\WebSocketMiddleware\WebSocketConnection;
use Voryx\WebSocketMiddleware\WebSocketMiddleware;
use React\Stream\ThroughStream;
use xchan\sqlite;
use function xchan\dbg;
use Psr\Http\Message\ServerRequestInterface as R;

$factory = new Clue\React\SQLite\Factory();
$db = $factory->openLazy(__DIR__ . '/../var/app.db');

$broadcast = new ThroughStream();

$store = new sqlite($db);

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
$auth = new xchan\auth;

$container = new FrameworkX\Container([]);
$app = new FrameworkX\App($container, $ws, $auth);

$hostport = $container->getEnv('X_LISTEN') ?? '127.0.0.1:8080';
dbg("running on $hostport");

$app->get('/', function (R $request) use ($store, $hostport) {
    $user = $request->getAttribute('user');
    $posts = $store->select('SELECT * from posts ORDER BY created_at DESC LIMIT 50');
    ob_start();
    include(__DIR__ . '/../resources/posts.html');
    $html = ob_get_clean();
    return React\Http\Message\Response::html(
        $html
    );
});

$app->post('/posts', function (R $request) use ($store, $hostport, $broadcast) {
    $user = $request->getAttribute('user');
    $data = json_decode((string) $request->getBody());
    $now = date("Y-m-d H:i:s");
    $store->insert("posts", [
        'title' => $data->title, 'body' => $data->body,
        'created_by' => $user,
        'created_at' => $now, 'updated_at' => $now
    ]);
    $broadcast->write('new message: ' . $data->title);
    return React\Http\Message\Response::json(
        ['res' => 'ok']
    );
});

$app->get('/posts/{id}', function (R $request) use ($store, $hostport) {
    $user = $request->getAttribute('user');
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
