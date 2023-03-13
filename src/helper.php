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
    $stack = [];
    $push = function ($thing) use (&$stack) {
        $stack[] = $thing;
    };
    $stack2 = new stack();
    $stack3 = $stack2->fun();
    ob_start();
    include($fname);
    $html = ob_get_clean();
    if ($layout) {
        $html = template(
            $layout,
            $data,
            array_merge($context, [
                'from' => $name, 'content' => $html, 'stack' => $stack,
                'stack2' => $stack2, 'stack3' => $stack3
            ])
        );
    }
    return $html;
}

class stack {
    public array $s;
    function push($thing) {
        $this->s[] = $thing;
    }
    function fun() {
        return function ($thing) {
            $this->s[] = $thing;
        };
    }
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

function normalize_files_array($files = []) {
    $normalized_array = [];

    foreach ($files as $index => $file) {
        if (!is_array($file['name'])) {
            $normalized_array[$index][] = $file;
            continue;
        }

        foreach ($file['name'] as $idx => $name) {
            $normalized_array[$index][$idx] = [
                'name' => $name,
                'type' => $file['type'][$idx],
                'tmp_name' => $file['tmp_name'][$idx],
                'error' => $file['error'][$idx],
                'size' => $file['size'][$idx]
            ];
        }
    }

    return $normalized_array;
}

function get_mime_type($file) {
    $finfo = finfo_open();
    $mimetype = finfo_file($finfo, $file, FILEINFO_MIME_TYPE);
    $ext = finfo_file($finfo, $file, FILEINFO_EXTENSION);
    $fext = explode("/", $ext)[0];
    if ($fext == '???') {
        $fext = "";
    } elseif ($fext == 'jpeg') {
        $fext = "jpg";
    }

    finfo_close($finfo);
    return [$mimetype, $fext, $ext];
}

function stream_to_file($name) {
    $tmpfname = tempnam(sys_get_temp_dir(), 'sh-');
    #file_put_contents($tmpfname, file_get_contents('php://input'));

    $in_stream = fopen("php://input", "rb");
    $out_stream = fopen($tmpfname, "w+b");
    $ok = stream_copy_to_stream($in_stream, $out_stream);
    fclose($in_stream);
    fclose($out_stream);

    #$mime="";
    $mime = get_mime_type($tmpfname);

    $error = 0;
    $size = filesize($tmpfname);

    if (!$size) {
        $error = UPLOAD_ERR_NO_FILE;
    }
    return [
        'name' => $name,
        'type' => 'stream',
        'tmp_name' => $tmpfname,
        'error' => $error,
        'size' => $size,
        'mime' => $mime,
        'extension' => $mime[1]
    ];
}

function get_image_dimensions($fname, $mime) {
    $valid = ['image/jpeg', 'image/jpg', 'image/png'];
    if (!in_array($mime, $valid)) {
        return false;
    }
    $info = getimagesize($fname);
    if (!$info) {
        return false;
    }
    $info['xy'] = $info[0] . 'x' . $info[1];
    return $info;
}

function script_tag($src, $attrs = "", $cb = null) {
    if ($cb == 'ts') $src .= '?ts=' . \time();
    $attrs = explode(" ", $attrs);
    $attrs = array_filter($attrs, 'trim');
    $attrs = array_reduce($attrs, function ($res, $attr) {
        if ($attr == 'module') $attr = ['type', $attr];
        $res[] = $attr;
        return $res;
    }, [['src', $src]]);
    return html_tag('script', $attrs);
}
function style_tag($src, $cb = null) {
    if ($cb == 'ts') $src .= '?ts=' . \time();
    return html_tag('link', [['rel', 'stylesheet'], ['href', $src]]);
}
function html_tag($tag, $attrs = []) {
    $attrs = array_reduce($attrs, function ($res, $item) {
        if (is_array($item)) {
            $item = sprintf('%s="%s"', $item[0], htmlspecialchars($item[1]));
        }
        return $res . " " . $item;
    }, "");
    return sprintf('<%s%s></%s>', $tag, $attrs, $tag);
}
