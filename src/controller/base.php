<?php

namespace xchan\controller;

use Psr\Http\Message\ServerRequestInterface as R;
use React\Http\Message\Response as P;

use xchan\configuration;
use xchan\user;
use xchan\sqlite;
use function xchan\template;

class base {
    public $db;
    public $template_path;
    public $var;
    public $broadcast;
    public ?user $user = null;

    public function __construct(sqlite $db, configuration $conf) {
        $this->db = $db;
        $this->template_path = $conf->templates;
        $this->var = $conf->templates . '/../var';
        $this->broadcast = $conf->broadcast;
    }

    public function __invoke(R $request) {
        $this->user = $request->getAttribute('user');
        $id = $request->getAttribute('id');

        if ($request->getMethod() == 'POST') {
            return $this->create($request);
        }
        if ($id) {
            return $this->show($id);
        }
        return $this->index();

        // dbg("+++ the user", $user, $request->getUri()->getPath());

    }

    public function html_response($view, $data) {
        $data += ['user' => $this->user];
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
