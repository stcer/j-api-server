<?php

namespace j\api\base;

use Exception as E;

/**
 * Class InfoNotFound
 * @package j\mvc\exception
 */
class Exception extends E {
    /**
     * @var
     */
    protected $info;

    /**
     * @var int
     */
    protected $code = 502;


    /**
     * @var int
     */
    protected $level = E_WARNING;

    /**
     * @param mixed $info
     */
    public function setInfo($info) {
        $this->info = $info;
    }

    /**
     * @return mixed
     */
    public function getInfo() {
        return $this->info;
    }
}