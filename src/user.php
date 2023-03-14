<?php

namespace xchan;

use DateTime;

class user {
    public string $name;
    public string $avatar;
    public string $email;
    public DateTime $expires;
    public array $scopes;
    public bool $has_changes = false;

    function __construct(array $token) {
        $this->name = $token['user']->name;
        $this->avatar = $token['user']->avatar ?? '';
        $this->expires = (new DateTime)->setTimestamp($token['exp']);
        $this->scopes = $token['scopes'];
    }

    function update_avatar($avatar) {
        $this->avatar = $avatar;
        $this->has_changes = true;
    }
}
