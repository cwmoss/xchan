<?php

namespace xchan\controller;

use Psr\Http\Message\ServerRequestInterface as R;
use function xchan\dbg;

class upload extends base {

    public function __invoke(R $request) {
        $user = $request->getAttribute('user');
        $img = $request->getBody();
        $dir = $this->var . '/avatar/';
        $old = $user->avatar;
        $new = md5($user->name) . '-' . time() . '.png';
        $fname =  $dir . $new;
        file_put_contents($fname, $img);

        dbg("+++ the user", $user);
        $res = $this->db->query(
            'UPDATE users SET avatar = :avatar WHERE name = :name',
            ['name' => $user->name, 'avatar' => $new]
        );
        $user->update_avatar($new);

        if ($old) @unlink($dir . $old);
        return $this->json_response(true);
    }
}
