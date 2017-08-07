<?php

namespace j\api\demo;

use \j\api\Base;

/**
 * Class TestService
 *
 * @apiInit(
 *     {name: 'a', 'desc' : 'test init param', 'value' : 1},
 *     {name: 'b', 'desc' : 'test init param', 'value' : 2}
 *     )
 */
class TestService extends Base {
    /**
     * @desc("测试方法")
     * @param string $name
     */
    public function hello($name = ''){

    }
}