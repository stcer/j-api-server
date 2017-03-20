<?php

namespace j\api;

use j\api\server\Base;
use j\di\Container;
use j\di\ServiceProviderInterface;

use j\network\http\Server as HttpServer;
use j\api\server\FpmApp as FpmApp;
use j\api\server\FpmYar as YarFpm;
use j\api\server\SwooleHttp as HttpSwoole;
use j\api\server\SwooleYar as YarSwoole;
use syar\Server as YarServer;

use j\error\Exception;
use j\log\Log;
use j\log\File as FileLog;
use j\base\Config;

class ApiServerProvider implements ServiceProviderInterface{

    /**
     * @var Config
     */
    protected $config;

    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $container A container instance
     */
    public function register($container){
        $this->config = $container->get('config');

        $container->set('httpFpm', function(){
            return $this->appHttpFpm();
        });
        $container->set('yarFpm', function(){
            return $this->appYarFpm();
        });
        $container->set('httpSwoole', function(){
            return $this->appHttpSwoole();
        });
        $container->set('yarSwoole', function(){
            return $this->appYarSwoole();
        });
    }
    
    protected function getConf($key, $def = null){
        return $this->config->get($key, $def);
    }

    /**
     * @param Base $app
     */
    protected function initApp($app){
        $app->isInner = true;
        $app->getLoader()->setNsPrefix($this->getConf('apiServer.api.ns'));
        // $app->getLoader()->classSuffix = 'Service';
        $app->setLogger($this->getApiLog());
    }

    protected function getApiLog(){
        $file = $this->getConf('apiServer.logFile', PATH_ROOT . '/tmp/log/api.log');
        $log = new FileLog($file);
        $log->setMask($this->getConf('apiServer.logMode', 31));
        return $log;
    }

    protected function getServerLog($options){
        if(isset($options['debug']) && $options['debug']){
            return new Log();
        } elseif(isset($options['log'])) {
            return new FileLog($options['log']);
        }
        return null;
    }

    /**
     * @return FpmApp
     */
    protected function appHttpFpm(){
        $app = new FpmApp();
        $this->initApp($app);
        return $app;
    }

    protected function appYarFpm(){
        $app = new YarFpm();
        $this->initApp($app);
        return $app;
    }

    /**
     * 'httpSwoole' => [
        'port' => 8601,
            'host' => '0.0.0.0',
            'daemonize' => !isDebug(),
            'worker_num' => 10,
            'max_request' => 1,
            'pid' => $_binDir . "/api_swoole_http.pid",
            'log' => $_tmpDir . '/log/httpSwoole.log',
        ],
     * @return HttpSwoole
     * @throws Exception
     */
    protected function appHttpSwoole(){
        $options = $this->getConf('apiServer.httpSwoole');
        if(!$options){
            throw new Exception("Invalid config for apiServer.httpSwoole");
        }

        $server = new HttpSwoole();
        $server->port = $options['port'];
        $server->options['pid_file'] = $options['pid'];
        $server->options['daemonize'] = $options['daemonize'];

        // init for http server
        $server->onServerCreate = function(HttpServer $server) use($options){
            $server->setOption('worker_num', $options['worker_num']);
            $server->setOption('max_request', $options['max_request']);
            $server->setLogger($this->getServerLog($options));
        };

        // init options for api document
        $apiPath = $this->getConf('apiServer.api.baseDir');
        $server->getDocReader()->setApiPath($apiPath);
        //$server->getDocReader()->apiFilePattern = '/Service.php$/';

        $server->testUrl = $this->getConf('apiServer.api.test');
        $this->initApp($server);
        return $server;
    }

    protected function appYarSwoole(){
        $options = $this->getConf('apiServer.yarSwoole');
        if(!$options){
            throw new Exception("Invalid config for apiServer.yarSwoole");
        }

        $server = new YarSwoole();
        $server->port = $options['port'];
        $server->options['pid_file'] = $options['pid'];
        $server->options['daemonize'] = $options['daemonize'];
        $server->onServerCreate = function(YarServer $server) use($options){
            $server->setLogger($this->getServerLog($options));
            $server->setOption('task_worker_num', $options['task_worker_num']);
            $server->setOption('worker_num', $options['worker_num']);
            $server->setOption('max_request', $options['max_request']);
        };

        $this->initApp($server);
        return $server;
    }
}