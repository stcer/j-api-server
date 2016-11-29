<?php

namespace j\api\client;
use j\api\Exception;

/**
 * Class Base
 */
abstract class Base {
    /**
     * @var string
     */
    public $prefix;

    protected $params = [];

    /**
     * SwooleYar constructor.
     * @param array $params
     * @param $prefix
     */
    public function __construct($prefix = '', array $params = []) {
        $this->params = $params;
        $this->prefix = $prefix;
    }

    /**
     * @var static[]
     */
    static $instance;

    /**
     * @param $api
     * @param array $init
     * @return self
     */
    public static function getInstance($api = '', $init = array()) {
        $key = $api ?: 'default';
        if(isset(static::$instance[$key])){
            return static::$instance[$key];
        }

        static::$instance[$key] = new static($api, $init);
        return static::$instance[$key];
    }

    /**
     * @param $api
     * @param $args
     * @param array $init
     * @return mixed
     */
    abstract function callApi($api, $args, $init = array());

    /**
     * @param $requests
     * @return mixed
     */
    abstract function calls($requests);

    /**
     * @param $name
     * @param $args
     * @return mixed
     */
    function __call($name, $args){
        $api = $this->prefix . "." . $name;
        return $this->callApi($api, $args, $this->params);
    }

    /**
     * @param $requests
     * @return mixed
     * @throws Exception
     */
    protected function formatRequests(& $requests){
        foreach($requests as $key => $r){
            $c = count($r);

            if(!($c == 2 || $c === 3)){
                throw new Exception("Invalid request params");
            }

            if(!is_numeric(key($r))){
                continue;
            }

            $requests[$key] = [
                'api' => $r[0],
                'params' => $r[1],
                ];
            if(isset($c[2])){
                $requests[$key]['init'] = $r[2];
            }
        }
        return $requests;
    }

    /**
     * @param $args
     * @param array $init
     * @param string $url
     * @return string
     */
    protected function packRequest($args, $init = [], $url = ''){
        $data = json_encode(['args' => $args, 'init' => $init]) ;
        if($url){
            $data = $url . (strpos($url, '?') ? "&" : '?') . urlencode($data);
        }
        return $data;
    }

    /**
     * @var string
     */
    public static $serverUrl = '';

    /**
     * @var string
     */
    public $serverAddress = '';

    /**
     * @param string $suffix
     * @param string $type
     * @return string
     * @throws Exception
     */
    protected function getRemoteUrl($suffix = '', $type = "args"){
        $url = $this->serverAddress ?: static::$serverUrl;
        if(!$url){
            throw new Exception("Invalid server address");
        }
        if($type == 'args'){
            return $url .  (strpos($url, '?') ? '&' : '?')  . $suffix;
        } else {
            return $url . $suffix;
        }
    }
}