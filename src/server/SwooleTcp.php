<?php

namespace j\api\server;

use j\network\tcp\Server as TcpServer;
use j\api\base\Strings;

/**
 * Class SwooleTcp
 * @package j\api\server
 */
class SwooleTcp extends Base {
    public $host = '0.0.0.0';
    public $port = '8063';
    public $options = [];

    /**
     * @throws \Exception
     */
    public function run(){
        $server = new TcpServer($this->host, $this->port);
        $server->setOption($this->options);

        /** @var  $protocol \j\network\Tcp\Protocol */
        $protocol = $server->getProtocol();
        $protocol->setCallback(function($request){
            //$init = isset($request['init']) ? $request['init'] : [];
            $data = $request['data'];
            if($this->charset == 'gbk'){
                $data = Strings::toGbk($data);
            }
            $rs = $this->execute(
                $request['call'],
                isset($data['init']) ? $data['init'] : [],
                $data['args']
            );
            if($this->charset == 'gbk'){
                $rs = Strings::utf8($rs);
            }
            return $rs;
        });

        if(is_callable($this->onServerCreate)){
            call_user_func($this->onServerCreate, $server);
        }

        if($logger = $server->getLogger()){
            $protocol->setLogger($logger);
        }

        $server->run();
    }
}
