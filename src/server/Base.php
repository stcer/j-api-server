<?php

namespace j\api\server;

use j\api\Loader;
use j\api\Base as Action;
use j\api\Exception;
use j\log\TraitLog;

/**
 * Class Base
 * @package j\api\server
 */
class Base {

    use TraitLog;

    public $loader;
    public $isInner = 1;
    public $charset = "utf8";

    /**
     * @var callback
     */
    public $onServerCreate;

    /**
     * @return Loader
     */
    function getLoader(){
        if(!isset($this->loader)){
            $this->loader = Loader::getInstance();
        }
        return $this->loader;
    }

    /**
     * @param Action $class
     * @param $api
     * @param $req
     * @return bool
     */
    protected function authentication($api, $class, $req){
        return $this->isInner;
    }

    /**
     * @param string $api rpc调用的路径
     * @param array $init
     * @param array $args RPC调用的参数
     * @param array $request
     * @return mixed
     * @throws Exception
     */
    protected function execute($api, $init = [], $args = [], $request = []){
        $classLoader = $this->getLoader();
        $apiObject = $classLoader->getClass($api, $init);
        if(!$this->authentication($api, $apiObject, $request)){
            throw new Exception("Authentication error");
        }

        return $apiObject->handle($args, $request);
    }

    /**
     * 格式化请求参数, 以支持json格式传递请求数据
     * @param $request
     */
    protected function normalizesRequest(& $request){
        if(isset($request['_format'])
            && $request['_format'] = 'json'
            && isset($request['_query'])
            && is_string($request['_query'])
        ){
            $request = json_decode($request['_query'], true) + $request;
        }
    }
}