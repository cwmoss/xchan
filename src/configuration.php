<?php

namespace xchan;

class configuration {
    public array $bucket = [];

    public function __construct(string $templates, $broadcast) {
        $this->bucket['templates'] = $templates;
        $this->bucket['broadcast'] = $broadcast;
    }

    public function __get($k) {
        return $this->bucket[$k] ?? null;
    }
}
