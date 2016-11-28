<?php


namespace api\action\test;

use j\api\Base;

/**
 * Class Search
 * 测试搜索接口，从test.search中独立成对象
 * @package api\action\test
 */
class Search extends Base{

    /**
     * @param string $key 搜索关键字
     * @return string
     */
    public function execute($key){
        return __METHOD__;
    }

}