<?php

namespace Shark\Plugin;

use \Phalcon\Flash\Session;

class Flash extends Session
{
    public function error($message) {
        $this->message('danger', $message);
    }

    public function notice($message) {
        $this->message('info', $message);
    }
}