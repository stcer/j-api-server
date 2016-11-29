<?php

namespace j\api\server;

use syar\Server;
use j\api\Exception;
use j\api\base\Strings;
use syar\Token;

/**
 * Class SwooleYar
 * @package j\api\server
 */
class SwooleYar extends Base {
    public $host = '0.0.0.0';
    public $port = '8062';
    public $options = [];

    /**
     * @var static[]
     */
    static $instance;

    /**
     * @var callback
     */
    public $onServerCreate;

    /**
     * @param Token $request [path, call_method, method_params, $_GET]
     * @param bool $isDocument
     * @return mixed|string
     * @throws Exception
     * @throws \Exception
     */
    public function handle($request, $isDocument = false){
        if($isDocument){
            return "<h1>Swoole yar document</h1>";
        }

        $api = $request->getApi();
        $api = rtrim($api, '.yar');
        if(!$api){
            throw new Exception("Invalid request");
        }

        $get = $request->getOption();
        $init = isset($get['init']) ? $get['init'] : array();

        // get rs
        $args = $request->getArgs();
        if($this->charset == 'gbk'){
            $args = Strings::toGbk($args);
        }

        $rs =  $this->execute($api, $init, $args, $get);
        if($this->charset == 'gbk'){
            $rs = Strings::utf8($rs);
        }

        return $rs;
    }

    public function run(){
        $server = new Server($this->host, $this->port);
        $server->setDispatcher(array($this, 'handle'));

        if(is_callable($this->onServerCreate)){
            call_user_func($this->onServerCreate, $server);
        }

        $server->run($this->options);
    }
}