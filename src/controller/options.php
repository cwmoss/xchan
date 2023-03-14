<?php

namespace xchan\controller;

use Psr\Http\Message\ServerRequestInterface as R;
use function xchan\dbg;

class options extends base {

    public function __invoke(R $request) {
        $user = $request->getAttribute('user');

        return $this->html_response('options', ['user' => $user]);
    }
}
