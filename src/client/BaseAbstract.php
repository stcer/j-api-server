<?php


namespace j\api\client;

/**
 * Class Base
 */
abstract class BaseAbstract {
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
     */
    protected function formatRequests(& $requests){
        foreach($requests as $key => $r){
            $c = count($r);
            if(!($c == 2 || $c === 3)){
                continue;
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
}