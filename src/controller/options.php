<?php

namespace xchan\controller;

use Psr\Http\Message\ServerRequestInterface as R;
use function xchan\dbg;

class options extends base {

    public function index() {

        return $this->html_response('options', []);
    }
}
