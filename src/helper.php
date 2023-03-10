<?php

namespace xchan;

use Ahc\Jwt\JWT;
use Ahc\Jwt\JWTException;

function dbg($txt, ...$vars) {
    // im servermodus wird der zeitstempel automatisch gesetzt
    //	$log = [date('Y-m-d H:i:s')];
    $log = [];
    if (!is_string($txt)) {
        array_unshift($vars, $txt);
    } else {
        $log[] = $txt;
    }
    $log[] = join(' ~ ', array_map(fn ($v) => json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $vars));

    error_log(join(' ', $log));
}


function template($name, $data, $context = []) {
    $fname = "{$context['base']}/{$name}.html";
    $layout = "";
    extract($data);
    ob_start();
    include($fname);
    $html = ob_get_clean();
    if ($layout) {
        $html = template(
            $layout,
            $data,
            array_merge($context, ['from' => $name, 'content' => $html])
        );
    }
    return $html;
}

function gen_secret($bytes = 32) {
    return rtrim(strtr(base64_encode(random_bytes($bytes)), '+/', '-_'), '=');
}

function gen_secret_hex($bytes = 32) {
    return bin2hex(random_bytes($bytes));
}

function gen_password($len = 15) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' .
        '0123456789-!?@#$%#';

    $str = '';
    $max = strlen($chars) - 1;

    for ($i = 0; $i < $len; $i++) {
        $str .= $chars[random_int(0, $max)];
    }

    return $str;
}

function gen_jwt_secret() {
    return gen_secret(64);
}

function gen_jwt($secret, $user) {
    $token = (new JWT($secret, 'HS256', 1800))->encode(['user' => $user, 'scopes' => ['user']]);
    return $token;
}

function check_jwt($secret, $token) {
    // TODO: let it crash
    if (!$secret || !$token) {
        return false;
    }

    try {
        $payload = (new JWT($secret, 'HS256', 1800))->decode($token);
    } catch (JWTException $e) {
        $payload = false;
    }
    return $payload;
}

/*
https://github.com/hansott/psr7-cookies/blob/master/src/SetCookie.php
*/
function cookie_value($name, $value, $expires = 0, $path = '/', $domain = false, $secure = false, $httponly = true, $samesite = 'Strict'): string {
    $headerValue = sprintf('%s=%s', $name, urlencode($value));

    if ($expires !== 0) {
        $headerValue .= sprintf(
            '; expires=%s',
            gmdate('D, d M Y H:i:s T', $expires)
        );
    }

    if (empty($path) === false) {
        $headerValue .= sprintf('; path=%s', $path);
    }

    if (empty($domain) === false) {
        $headerValue .= sprintf('; domain=%s', $domain);
    }

    if ($secure) {
        $headerValue .= '; secure';
    }

    if ($httponly) {
        $headerValue .= '; httponly';
    }

    if ($samesite !== '') {
        $headerValue .= sprintf('; samesite=%s', $samesite);
    }

    return $headerValue;
}
