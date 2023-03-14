<?php

namespace xchan;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;

/*
https://stackoverflow.com/questions/38274111/psr-7-attributes-on-response-object
*/

class auth {

    public auth\store $db;
    public string $realm;
    public array $opts;
    public $attribute = 'user';
    private string $secret;
    public $paths = [
        '/auth/login', '/auth/register'
    ];

    public function __construct(auth\store $db, string $secret, string $realm, array $opts) {
        $this->db = $db;
        $this->realm = $realm;
        $this->opts = array_merge(['on_error' => 'redirect'], $opts);
        $this->secret = $secret;
        // $this->secret = '123456abcd'; //  gen_secret();
    }

    public function __invoke(ServerRequestInterface $request, callable $next) {

        $cookies = $request->getCookieParams();
        dbg(
            "++ cookies",
            $cookies,
            (string) $request->getUri(),
            $request->getUri()->getPath(),
            $request->getMethod(),
            $request->getRequestTarget()
        );
        $path = $request->getUri()->getPath();
        if (in_array($path, $this->paths)) {
            $html_or_user = $this->handle_service($path, $request->getMethod(), $request->getParsedBody() ?? []);
            // dbg("++ html or user", $html_or_user);
            if (is_array($html_or_user)) {
                $status = Response::STATUS_OK;
                $hdrs = ['Set-Cookie' => cookie_value($this->realm, gen_jwt($this->secret, $html_or_user))];
                // if redirect
                $hdrs['Location'] = '/';
                $status = Response::STATUS_FOUND;
                return new Response(
                    $status,
                    $hdrs,
                    'hello friend!'
                );
            } else {
                return Response::html($html_or_user);
            }
        }

        $tokendata = check_jwt($this->secret, $cookies[$this->realm] ?? null);
        if ($tokendata === false) {

            return $this->unauthorized();
        }

        dbg("+ valid user", $tokendata);
        if ($path == '/auth/logout') {
            $hdrs = ['Set-Cookie' => cookie_value($this->realm, $cookies[$this->realm], time() - (300 * 24 * 60 * 60))];
            $html = template('logout', [], ['base' => $this->opts['views']]);
            return new Response(Response::STATUS_OK, $hdrs, $html);
        }

        $user = new user($tokendata);
        $resp = $next($request->withAttribute($this->attribute, $user));

        dbg('+++ user update 000', $user);
        if ($user->has_changes) {
            dbg('+++ user update', $user);
            $updated_jwt = cookie_value($this->realm, gen_jwt($this->secret, ['name' => $user->name, 'avatar' => $user->avatar]));
            dbg('new jwt', $updated_jwt);
            return $resp->withHeader('Set-Cookie', $updated_jwt);
        }
        return $resp;
    }

    public function unauthorized() {
        if ($this->opts['on_error'] == 'redirect') {
            return new Response(
                Response::STATUS_FOUND,
                [
                    'Location' => '/auth/login'
                ]
            );
        } else {
            return new Response(
                Response::STATUS_UNAUTHORIZED,
                [],
                'Please Login'
            );
        }
    }
    public function handle_service($path, $method, $data = []) {
        dbg("++ post data ", $data);
        if ($path == '/auth/login') {
            $data = $data + ['user' => '', 'password' => ''];
            if ($method == 'GET') {
                return template('login', [], ['base' => $this->opts['views']]);
            } elseif ($method == 'POST') {
                dbg("++ login", $data);
                $login = $this->db->login_user($data['user'], $data['password']);
                dbg("++ login", $login);

                if ($login === false) {
                    $data['error'] = 'Login failed';
                    return template('login', $data, ['base' => $this->opts['views']]);
                } else {
                    return $login;
                }
                // return ['name' => 'Hans', 'avatar' => 'ju'];
            }
        } elseif ($path == '/auth/register') {
            $data = $data + ['email' => '', 'password' => ''];
            if ($method == 'GET') {
                return template('register', [], ['base' => $this->opts['views']]);
            } elseif ($method == 'POST') {
                $reg = new auth\registration($this->db);
                $user_or_error = $reg->register($data);
                if (is_string($user_or_error)) {
                    $data['error'] = $user_or_error;
                    return template('register', $data, ['base' => $this->opts['views']]);
                } else {
                    return $user_or_error;
                }
            }
        }
    }
}
