<?php

namespace j\api;

use Exception;
use j\api\server\SwooleHttp;
use j\di\Container;
use j\log\Log;
use j\log\LogInterface;

/**
 * Class WatchLogServer
 * @package j\watchLog
 */
class ServerCommand {
    /**
     * @var array
     */
    protected $options;

    /**
     * @var string
     */
    private $vendorPath;

    /**
     * WatchLog constructor.
     * @param array $options
     * @param string $vendorPath
     */
    public function __construct(array $options = []){
        if(!$options){
            $options = getopt("a:t:b:dhv");
        }

        $this->options = $options;
    }


    public static function usage(){
        echo <<<STR
php apiServer.php [options]

Options:
    -h, print this message
    -v, debug mode
    -d, run as a daemonize mode
    
    -b <bootstrap>
        bootstrap file, init config/di
    -a <action>, 
        start: start target server
        stop: stop the server 
        restart: restart the server
        status: show status
    -t <target>,
        doc: document server
        yar: yar server
        http: http server
        tcp: tcp server

STR;
    }


    /**
     * @param LogInterface $log
     * @throws Exception
     */
    function run($log = null){
        if(isset($this->options['h'])){
            $this->usage();
            return;
        }

        if(!isset($this->options['a']) || !$this->options['a']){
            self::usage();
            return;
        }

        if(!isset($this->options['t']) || !$this->options['t']){
            self::usage();
            return;
        }

        if(!isset($this->options['b'])){
            self::usage();
            return;
        }

        $bootstrap = $this->options['b'];
        if(!file_exists($bootstrap)){
            throw new Exception("bootstrap file not found");
        }
        include($bootstrap);

        $action = $this->options['a'];
        $target = $this->options['t'];

        if($action != 'start'){
            $this->manager($target, $action);
            return;
        }

        $diKey = 'apiServer.' . $target;
        $di = Container::getInstance();
        if(!$di->has($diKey)){
            $diKey = $target . "Swoole";
        }

        /** @var SwooleHttp $server */
        $server = Container::getInstance()->get($diKey);
        $server->options['daemonize'] = isset($this->options['d']) ? true : false;

        $log = null;
        if(isset($this->options['v'])){
            $log = new Log();
            $server->setLogger($log);
        }
        $server->run($log);
    }

    /**
     * @param string $serverName
     * @param string $action
     * @throws Exception
     */
    protected function manager($serverName, $action = 'shutdown')
    {
        if($action == 'stop'){
            $action = 'shutdown';
        }

        $url = "http://{$serverName}/cgi/manager/{$action}";
        $rs = file_get_contents($url);
        if(!$rs || !json_decode($rs, true)){
            throw new Exception("$action fail, empty return on this server");
        }
        echo $rs. "\n";
    }
}
