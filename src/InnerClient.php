<?php

namespace j\api;

use j\api\client\Base as Abase;

/**
 * Class Inner
 * @package j\api\client
 */
class InnerClient extends Abase{
    static public $apiMaps = [
        'region' => 'comm.region'
        ];


    /**
     * @param $api
     * @param $args
     * @param array $init
     * @return mixed
     * @throws \j\api\Exception
     */
    public function callApi($api, $args, $init = array()){
        $class = Loader::getInstance()->getClass($api, $init);
        return $class->handle($args);
    }

    function calls($requests) {
        // TODO: Implement calls() method.
    }
}