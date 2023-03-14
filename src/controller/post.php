<?php

namespace xchan\controller;

use Psr\Http\Message\ServerRequestInterface as R;
use function xchan\dbg;

class post extends base {

    public function __invoke(R $request) {
        $user = $request->getAttribute('user');
        $id = $request->getAttribute('id');

        if ($request->getMethod() == 'POST') {
            return $this->create($user, $request);
        }
        if ($id) {
            return $this->show($user, $id);
        }
        return $this->index($user);

        // dbg("+++ the user", $user, $request->getUri()->getPath());

    }

    public function index($user) {
        //         dbg("++ path", $request->getUri()->getPath());
        $posts = $this->db->select('SELECT * from posts ORDER BY created_at DESC LIMIT 50');
        return $this->html_response('posts', ['user' => $user, 'posts' => $posts]);
    }

    public function show($user, $id) {
        $post = $this->db->select_first_row('SELECT * from posts WHERE id=:id', ['id' => $id]);
        $replies = $this->db->select('SELECT * from replies WHERE post_id=:id', ['id' => $id]);
        return $this->html_response('post', ['user' => $user, 'post' => $post, 'replies' => $replies]);
    }

    public function create($user, R $request) {
        $data = $this->json_load($request);
        $now = date("Y-m-d H:i:s");
        $this->db->insert("posts", [
            'title' => $data->title, 'body' => $data->body,
            'created_by' => $user->name,
            'created_at' => $now, 'updated_at' => $now
        ]);
        $this->broadcast->write('new message: ' . $data->title);
        return $this->json_response(true);
    }
}
