<?php

namespace xchan\auth;

class registration {

    public $db;
    public function __construct($db) {
        $this->db = $db;
    }

    public function register($data) {
        $ok = $this->validate($data);
        if ($ok === true) {
            $name = explode('@', $data['email'])[0];
            $password = password_hash($data['password'], null);
            $refresh = \xchan\gen_secret(64);
            $this->db->create_user([
                'name' => $name, 'email' => $data['email'],
                'password' => $password,
                'refresh' => $refresh
            ]);
            return ['name' => $name, 'avatar' => null];
        }
        return $ok;
    }

    public function validate($data) {
        if (strlen($data['password']) < 8) {
            return "Password too short.";
        }
        if (!preg_match('/@/', $data['email'])) {
            return "Email is invalid";
        }
        return true;
    }
}
