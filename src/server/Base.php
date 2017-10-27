<?php

namespace j\api\server;

use j\api\Loader;
use j\api\Base as Action;
use j\api\Exception;
use j\api\Document;
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
    public $testUrl = '/api/%action%';

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
     * @param $api
     * @param array $init
     * @param array $args
     * @param array $request
     * @return mixed
     * @throws Exception
     */
    protected function execute($api, $init = [], $args = [], $request = []){
        if($this->docEnable
            && strpos($api, $this->docApiPrefix) === 0
        ){
            // parse doc
            return $this->processDocument($api, $args);
        }

        $classLoader = $this->getLoader();
        $class = $classLoader->getClass($api, $init);
        if(!$this->authentication($api, $class, $request)){
            throw new Exception("Authentication error", Exception::SIGN);
        }

        return $class->handle($args, $request);
    }

    /** @var  Document */
    public $docReader;
    public $docApiPrefix = 'document.';
    public $docEnable = true;

    /**
     * @return Document
     */
    function getDocReader(){
        if(!isset($this->docReader)){
            $this->docReader = new Document($this->getLoader());
        }
        return $this->docReader;
    }

    /**
     * @param $api
     * @param $args
     * @return mixed
     * @throws Exception
     */
    protected function processDocument($api, $args){
        $validApi = [
            'document.getApiList',
            'document.getInitParams',
            'document.getApiDocument',
            'document.testUrl',
        ];
        if(!in_array($api, $validApi)){
            throw new Exception("Invalid document request");
        }

        $doc = $this->getDocReader();
        if(!isset($doc->apiPath)){
            throw new Exception("Invalid api path for document reader");
        }

        if($api == 'document.testUrl'){
            return $this->testUrl;
        }

        $method = str_replace('document.', '', $api);
        return call_user_func_array(array($doc, $method), $args);
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