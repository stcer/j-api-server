<?php

namespace j\api;

/**
 * Class Exception
 * @package j\api
 */
class Exception extends \Exception {

    const API_NOT_FOUND = 401;

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
     * Exception constructor.
     * @param string $message
     * @param int $code
     * @param array $info
     * @param Exception|null $previous
     */
    public function __construct(
        $message = null, $code = 0,
        $info = [], $previous = null
    ) {
        if($message || $code || $previous){
            parent::__construct($message, $code, $previous);
        }

        if($info){
            $this->setInfo($info);
        }
    }

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