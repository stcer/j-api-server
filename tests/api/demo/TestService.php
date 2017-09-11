<?php

namespace j\api\demo;

use \j\api\Base;

/**
 * Class TestService
 *
 * @apiInit(
 *     {"name": "a", "desc" : "test init param", "value" : 1},
 *     {"name": "b", "desc" : "test init param", "value" : 2}
 *     )
 */
class TestService extends Base {
    /**
     * @desc "测试方法"
     * @param string $name
     * @param int $op
     * @param array $args
     * @args {
        name : "string",
        test : {type: int, default: 10}
     * }
     */
    public function hello($name = '', $op = 0, array $args = []){

    }
}