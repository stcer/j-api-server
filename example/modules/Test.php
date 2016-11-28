<?php

namespace api;

/**
 * Class Test
 * @package api
 */
class Test {
    public function getName(){
        return 'TestListener';
    }

    /**
     * @desc('统计数量')
     * @param int $uid 会员id
     * @return int
     */
    public function count($uid = 1){
        return $uid + 100;
    }

    public function notAllowApiAccess(){
        return 0;
    }
}