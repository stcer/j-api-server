<?php

namespace j\api\client;

use j\network\tcp\Client;

/**
 * Class Tcp
 * @package j\api\client
 */
class Tcp extends Base  {
    /**
     * @var Client
     */
    public $client;
    public static $defaultServer = '127.0.0.1';
    public static $defaultPort = 8063;

    /**
     * @var static[]
     */
    static $instance;

    public $server = '';
    public $port = 0;
    public $timeout = 0.5;

    protected function getClient(){
        if(isset($this->client)){
            return $this->client;
        }

        $host = $this->server ?: static::$defaultServer;
        $port = $this->port ?: static::$defaultPort;
        $this->client = new Client($host, $port, $this->timeout);
        return $this->client;
    }

    /**
     * @param $api
     * @param $args
     * @param array $init
     * @return mixed
     */
    public function callApi($api, $args, $init = array()){
        return $this->getClient()->send($api, ['args' => $args, 'init' => $init]);
    }

    public function asyncCalls($request){
        return $this->getClient()->batchSend($this->normalRequests($request));
    }

    public function calls($request){
        return $this->getClient()->batchSend($this->normalRequests($request));
    }

    /**
     * @param $requests
     * @return array
     */
    protected function normalRequests($requests){
        $this->formatRequests($requests);
        
        $tmp = [];
        foreach($requests as $key => $value){
            $tmp[$key] = [
                'call' => $value['api'],
                'data' => [
                    'args' => $value['params'],
                    'init' => isset($value['init']) ? $value['init'] : [],
                ]
            ];
        }

        return $tmp;
    }
}
