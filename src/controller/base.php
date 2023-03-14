<?php

namespace xchan\controller;

use React\Http\Message\Response as P;
use xchan\configuration;
use xchan\sqlite;
use function xchan\template;

class base {
    public $db;
    public $template_path;
    public $var;
    public $broadcast;
    public function __construct(sqlite $db, configuration $conf) {
        $this->db = $db;
        $this->template_path = $conf->templates;
        $this->var = $conf->templates . '/../var';
        $this->broadcast = $conf->broadcast;
    }

    public function html_response($view, $data) {
        $html = template($view, $data, ['base' => $this->template_path]);
        return P::html(
            $html
        );
    }

    public function json_load($request) {
        return json_decode((string) $request->getBody());
    }

    public function json_response($data) {
        if ($data === true) {
            $data = ['res' => 'ok'];
        }
        return P::json($data);
    }
}
