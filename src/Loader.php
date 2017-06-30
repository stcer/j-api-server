<?php

namespace j\api;

use Closure;

/**
 * Class Loader
 * @package j\api
 */
class Loader{

    public $classSuffix = '';
    protected $nsPrefix = '\\api\\action\\';
    protected $apiMap = [];

    /**
     * @var Closure
     */
    public $onCreate;

    /**
     * @param string $nsPrefix
     */
    public function setNsPrefix($nsPrefix) {
        $this->nsPrefix = $nsPrefix;
    }

    /**
     * @var
     */
    private static $instance;

    /**
     * @param string $type
     * @return Loader
     */
    public static function getInstance($type = 'default') {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @var Base[]
     */
    protected static $classCaches = [];


    /**
     * @param $api
     * @param array $initParams
     * @param bool $returnName
     * @return Base|string
     * @throws Exception
     */
    function getClass($api, $initParams = [], $returnName = false){
        $api = trim($api, ".");
        /** @var Base $class */
        if(isset(self::$classCaches[$api])){
            // read cache
            list($class, $action) = self::$classCaches[$api];
            if(method_exists($class, 'setAction')){
                $class->setAction($action);
            }
            return $class;
        }

        if(isset($this->apiMap[$api])){
            list($className, $action) = $this->apiMap[$api];
        } else {
            list($className, $action) = $this->findClass($api);
        }

        if($returnName){
            // only class name
            return $className;
        }

        $class = !is_object($className) ? new $className : $className ;
        if(method_exists($class, 'bootstrap')){
            $class->bootstrap($initParams);
        }

        if(method_exists($class, 'setAction')){
            $class->setAction($action);
        }

        if(isset($this->onCreate)){
            call_user_func($this->onCreate, $class, $api);
        }

        self::$classCaches[$api] = [$class, $action];
        return $class;
    }

    /**
     * @param $api
     * @param string $oldApi
     * @return array
     * @throws Exception
     */
    protected function findClass($api, $oldApi = ''){
        if(!is_string($api)){
            throw new Exception("Invalid api({$api})", Exception::API);
        }

        if(!$oldApi){
            $oldApi = $api;
        }

        $path = explode('.', $api);
        $className = array_pop($path);
        $apiClass = $this->nsPrefix
            . ($path ? (implode('\\', $path)) . "\\" : '')
            . ucfirst($className)
            . $this->classSuffix
            ;
        if(!class_exists($apiClass)){
            if($path){
                return $this->findClass(implode('.', $path), $oldApi);
            }else{
                throw new Exception("Api not found({$api}:{$apiClass})", Exception::API);
            }
        }

        if($api != $oldApi)
            $action = str_replace($api . '.', '', $oldApi);
        else {
            $action = '';
        }

        return [$apiClass, $action];
    }


    /**
     * @param Base $class
     * @param $api
     * @param $oldApi
     */
    protected function setAction($class, $api, $oldApi){
        if($api != $oldApi)
            $action = str_replace($api . '.', '', $oldApi);
        else {
            $action = '';
        }
        $class->setAction($action);
    }
}