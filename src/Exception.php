<?php

namespace j\api;

use j\api\base\Exception as AException;

/**
 * Class Exception
 * @package j\api
 */
class Exception extends AException {
    /**
     * Exception constructor.
     * @param string $message
     * @param int $code
     * @param array $info
     * @param AException|null $previous
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
}