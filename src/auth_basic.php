<?php

namespace xchan;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;

class auth {

    public $realm = 'xchan';
    public $attribute = 'user';

    public function __invoke(ServerRequestInterface $request, callable $next) {
        // optionally return response without passing to next handler
        // return React\Http\Message\Response::plaintext("Done.\n");

        // optionally modify request before passing to next handler
        // $request = $request->withAttribute('admin', false);
        $ip = $request->getServerParams()['REMOTE_ADDR'];
        if ($ip === '127.0.0.1') {
            //    return $next($request->withAttribute('is_local', true));
        }
        dbg("headers", $request->getHeaders(), $request->getHeaderLine('Authorization'));

        $userpass = $this->parse_header($request->getHeaderLine('Authorization'));
        if (isset($userpass['username']) && $userpass['username']) {
            return $next($request->withAttribute($this->attribute, $userpass['username']));
        }



        return new Response(
            Response::STATUS_UNAUTHORIZED,
            [
                'WWW-Authenticate' => sprintf('Basic realm="%s"', $this->realm)
            ]
        );
    }

    /**
     * Parses the authorization header for a basic authentication.
     */
    private function parse_header(string $header): ?array {
        if (strpos($header, 'Basic') !== 0) {
            return null;
        }

        $header = base64_decode(substr($header, 6));

        if ($header === false) {
            return null;
        }

        $header = explode(':', $header, 2);

        return [
            'username' => $header[0],
            'password' => isset($header[1]) ? $header[1] : null,
        ];
    }
}
