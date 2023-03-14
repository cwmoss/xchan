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

$factory = new Clue\React\SQLite\Factory();
$db = $factory->openLazy(__DIR__ . '/../var/app.db');

$broadcast = new ThroughStream();

$store = new sqlite($db);
$authstore = new auth\store($store);

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

$auth = new xchan\auth($authstore, 'xchan', ['views' => __DIR__ . '/../resources']);

$container = new FrameworkX\Container([]);
$app = new FrameworkX\App($container, $ws, $auth);

$hostport = $container->getEnv('X_LISTEN') ?? '127.0.0.1:8080';
dbg("running on $hostport");

$app->get('/', function (R $request) use ($store, $hostport) {
    $user = $request->getAttribute('user');
    dbg("++ path", $request->getUri()->getPath());
    $posts = $store->select('SELECT * from posts ORDER BY created_at DESC LIMIT 50');
    $html = template('posts', ['user' => $user, 'posts' => $posts], ['base' => __DIR__ . '/../resources']);
    return React\Http\Message\Response::html(
        $html
    );
});

$app->get('/options', function (R $request) use ($store, $hostport) {
    $user = $request->getAttribute('user');
    $html = template('options', ['user' => $user], ['base' => __DIR__ . '/../resources']);
    $res = new P;
    # $res->
    return React\Http\Message\Response::html(
        $html
    );
});

$app->get('/audio', function (R $request) {

    return new P(
        P::STATUS_OK,
        ['Content-Type' => 'audio/m4a'],
        file_get_contents(__DIR__ . '/../resources/bling.m4a')
    );
});

$app->post('/posts', function (R $request) use ($store, $hostport, $broadcast) {
    $user = $request->getAttribute('user');

    $data = json_decode((string) $request->getBody());
    $now = date("Y-m-d H:i:s");
    $store->insert("posts", [
        'title' => $data->title, 'body' => $data->body,
        'created_by' => $user->name,
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
    dbg("+++ the user", $user, $request->getUri()->getPath());
    $post = $store->select_first_row('SELECT * from posts WHERE id=:id', ['id' => $id]);
    $replies = $store->select('SELECT * from replies WHERE post_id=:id', ['id' => $id]);
    $html = template('post', ['user' => $user, 'post' => $post, 'replies' => $replies], ['base' => __DIR__ . '/../resources']);
    return React\Http\Message\Response::html(
        $html
    );
});

$app->get('/users/{name}', function (Psr\Http\Message\ServerRequestInterface $request) {
    return React\Http\Message\Response::plaintext(
        "Hello " . $request->getAttribute('name') . "!!\n" . memory_get_usage()
    );
});


$app->get('/xassets/{path}', function (R $request) {
    dbg('+++ asset', $request->getAttribute('path'));
    return React\Http\Message\Response::json(
        ['res' => 'ok']
    );
});

$app->get('/assets/{path}', new FrameworkX\FilesystemHandler(__DIR__ . '/../public/assets/'));
$app->get('/avatar/{path}', new FrameworkX\FilesystemHandler(__DIR__ . '/../var/avatar/'));

$app->post('/upload/avatar', function (R $request) use ($store) {
    $user = $request->getAttribute('user');
    $img = $request->getBody();
    $dir = __DIR__ . '/../var/avatar/';
    $old = $user->avatar;
    $new = md5($user->name) . '-' . time() . '.png';
    $fname =  $dir . $new;
    file_put_contents($fname, $img);

    dbg("+++ the user", $user);
    $res = $store->query(
        'UPDATE users SET avatar = :avatar WHERE name = :name',
        ['name' => $user->name, 'avatar' => $new]
    );
    $user->update_avatar($new);

    if ($old) unlink($dir . $old);
    return React\Http\Message\Response::json(
        ['res' => 'ok']
    );
});

$app->run();
