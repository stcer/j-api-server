<?php

namespace j\api\client;

use j\api\base\HttpClient as Client;
use Exception;

/**
 * Class SwooleYar
 * @package j\api\client
 */
class HttpJson extends BaseAbstract {

    /**
     * @var string
     */
    public static $serverUrl = 'http://jzf.x1.cn/api/www/index.php';

    /**
     * @var string
     */
    public $serverAddress = '';

    /**
     * @var static[]
     */
    static $instance;

    /**
     * @param $api
     * @param $args
     * @param array $init
     * @return mixed
     * @throws Exception
     */
    public function callApi($api, $args = [], $init = array()){
        $server = ($this->serverAddress ?: static::$serverUrl);

        $url = $server . '?api=' . $api;
        $query = '&' . http_build_query(['args' => $args, 'init' => $init]);

        $http = new Client();
        $data = $http->post($url, $query);
        $data = json_decode($data, true);

        if(is_array($data) && $data['code'] == 200){
            return $data['data'];
        }

        throw new Exception($data['message']);
    }

    /**
     * @param $request
     * @return array
     */
    public function asyncCalls($request){
        $data = [];
        $this->formatRequests($request);
        foreach($request as $i => $r){
            $data[$i] = $this->callApi($r['api'], $r['params']);
        }
        return $data;
    }

    /**
     * @param $request
     * @return array
     */
    public function calls($request){
        return $this->asyncCalls($request);
    }
}