<?php

namespace j\api\server;

use Yar_Server as Server;
use j\tool\ArrayUtils;
use j\api\Exception;

class FpmYar extends Base{

    /**
     * @var static[]
     */
    static $instance;

    /**
     * 由api来确定class及action
     * 远程调用的method应该永远为 yar
     *
     * @throws Exception
     * @throws \Exception
     */
    public function run(){
        $api = $this->getApi($_REQUEST);
        $init = (array)ArrayUtils::gav($_REQUEST, 'init');
        $class = $this->getLoader()->getClass($api, $init);

        $server = new Server($class);
        $server->handle();
    }

    /**
     * @return mixed|null|string
     * @throws Exception
     */
    protected function getApi($req){
        if(isset($this->api)){
            return $this->api;
        }

        if(isset($req['q'])){
            $api = $req['q'];
        }else{
            $api = ArrayUtils::gav($req, 'api');
        }

        $api = str_replace("/", ".", $api);
        if(!preg_match('/^[a-zA-Z\.]+$/', $api)){
            throw new Exception("Invalid api(1)");
        }

        $this->api = $api;
        return $api;
    }
}