<?php

namespace api\action;

use j\api\Base;

/**
 * Class TestListener
 * 这是一个测试接口，不作它用
 * @package api\action
 */
class Test extends Base{
    /**
     * @desc("name测试")
     * @param string $args
     * @return string
     */
    public function name($args){
        return  "server:" . $args;
    }

    /**
     * @return \api\Test
     */
    public function getModel(){
        return new \api\Test();
    }

    /**
     * @desc("搜索测试")
     * @param $key
     * @return string
     */
    public function search($key){
        return __METHOD__;
    }

    /**
     * @return array
     */
    public function getDefaultModelMethods(){
        return [
            'getName',
            'count'
            ];
    }
}