<?php

namespace xchan\controller;

use Psr\Http\Message\ServerRequestInterface as R;
use function xchan\dbg;

class upload extends base {

    public function create(R $request) {

        $img = $request->getBody();
        $dir = $this->var . '/avatar/';
        $old = $this->user->avatar;
        $new = md5($this->user->name) . '-' . time() . '.png';
        $fname =  $dir . $new;
        file_put_contents($fname, $img);

        dbg("+++ the user", $this->user);
        $res = $this->db->query(
            'UPDATE users SET avatar = :avatar WHERE name = :name',
            ['name' => $this->user->name, 'avatar' => $new]
        );
        $this->user->update_avatar($new);

        if ($old) @unlink($dir . $old);
        return $this->json_response(true);
    }
}
