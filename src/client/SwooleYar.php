<?php

namespace j\api\client;

use Yar_client;
use Yar_Concurrent_Client;

/**
 * Class SwooleYar
 * @package j\api\client
 */
class SwooleYar extends BaseAbstract {

    /**
     * @var string
     */
    public static $serverUrl = 'http://127.0.0.1:8062';

    /**
     * @var string
     */
    public $serverAddress = '';

    /**
    * @var static[]
    */
    static $instance;

    /**
     * @var string
     */
    protected static $defaultMethod = 'yar';

    /**
     * swoole yar server 调用方法为 yar时丢弃
     * @param $api
     * @param $args
     * @param array $init
     * @return mixed
     */
    public function callApi($api, $args, $init = array()) {
        $url = ($this->serverAddress ?: static::$serverUrl);
        $url .= "/" . $this->formatApi($api);
        $query = '?' . http_build_query(['init' => $init]);
        $client = new Yar_client($url . $query);
        return call_user_func_array(array($client, self::$defaultMethod), $args);
    }

    protected function formatApi($api){
        return str_replace('.', '/', $api);
    }

    /**
     * @param $request
     * @return mixed
     */
    public function calls($request) {
        // swoole yar server
        // request path : /multiple
        // request method : calls
        $client = new Yar_client(($this->serverAddress ?: static::$serverUrl) . "/multiple");

        $this->formatRequests($request);
        foreach($request as $key => $value){
            $request[$key]['method'] = self::$defaultMethod;
        }

        return call_user_func(array($client, "calls"), $request);
    }


    /**
     * swoole yar server 调用方法为 yar时丢弃
     * @param $request
     * @return array
     */
    public function asyncCalls($request) {
        $data = [];
        $server = ($this->serverAddress ?: static::$serverUrl);

        $this->formatRequests($request);
        foreach($request as $i => $r){
            $url = $server ."?api={$r['api']}";
            Yar_Concurrent_Client::call($url, self::$defaultMethod, $r['params'],
                function($rs) use ($i, &$data){
                    $data[$i] = $rs;
                });
        }
        Yar_Concurrent_Client::loop();
        return $data;
    }
}