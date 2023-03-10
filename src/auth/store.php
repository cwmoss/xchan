<?php

namespace xchan\auth;

use function xchan\dbg;

class store {

    public $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function create_user($data) {
        $this->db->insert('users', $data);
    }

    public function login_user($user, $password) {
        // password_verify
        // password_needs_rehash
        $res = $this->db->select_first_row('SELECT * from users WHERE email=:email', ['email' => $user]);
        dbg("res", $res);
        if ($res) {
            if (true === password_verify($password, $res['password'])) {
                return [
                    'name' => $res['name'],
                    'avatar' => $res['avatar']
                ];
            }
        }
        return false;
    }
}
