# 概述

基于 php5.5+, 用于实现 api接口, 实现应用系统前后端分离, 如下特性
1. rpc远程调用形式
1. 轻量化, 编写接口简单, 只需要实现 handle() 方法
2. 易扩展, 方便将原来复杂api抽出启用新的api class实现
2. 高性能, 支持接口批量调用, 同时分发到多进程并发执行
2. 多种通信协议支持, 支持基于swoole实现的tcp, yar, http协议调用
2. 高并发, 独立服务至少是ng + fpm5倍以上效率, 至少提升300% QPS
2. 基于stcer/j-api-doc实现文档自动生成, 集成简易模式测试

## 安装

服务端安装
```
composer require stcer/j-api
```

客户端安装
```
composer require stcer/j-api-client
```

## 依赖

1. php >= 5.5
1. stcer/j-core
1. stcer/j-api-doc, 自动生成服务api文档
1. stcer/syar, 如果启用swoole yar服务端
1. stcer/j-tcp, 如果使用tcp协议通信
1. stcer/j-http, 如果启用swoole http服务端
1. ext-yar, 如果通信方式为yar协议进行rpc远程调用
2. ext-swoole, 如果使用不依赖ng + fpm的独立服务
4. ext-msgpack, 如果通信数据打包方式为msgpack

## 服务配置

```
# 参考 example/init.inc.php
$_tmpDir = PATH_ROOT . '/tmp/';

return [
   'ns' => 'api\\action\\',
   'classSuffix' => '',
   'logFile' => $_tmpDir . '/log/api.log',
   'logMode' => 31,

   'doc' => [
       'port' => 8500,
       'host' => '0.0.0.0',
       'baseDir' => __DIR__ . '/action/',
       'fileSuffix' => '/Service.php$/',
       'testUrl' => 'http://w.api.jz.x2.cn/index.php?api=%action%', # 修改成当前测试地址
   ],

   'http' => [
       'debug' => 1,
       'port' => 8501,
       'host' => '0.0.0.0',
       'daemonize' => false,
       'pid' => $_tmpDir . "/pid/http.pid",
       'log' => $_tmpDir . '/log/http_swoole.log',
       'swoole' => [
           'worker_num' => 10,
           'task_worker_num' => 10,
           'package_max_length' => 1024 * 4,
           'max_request' => 10,
       ]
   ],

   'yar' => [
       'debug' => 1,
       'port' => 8502,
       'host' => '0.0.0.0',
       'daemonize' => false,
       'pid' => $_tmpDir . "/pid/yar.pid",
       'log' => $_tmpDir . '/log/yar_swoole.log',
       'swoole' => [
           'worker_num' => 10,
           'max_request' => 20,
           'task_worker_num' => 10,
       ]
   ],

   'tcp' => [
       'debug' => 1,
       'port' => 8503,
       'host' => '0.0.0.0',
       'daemonize' => false,
       'pid' => $_tmpDir . "/pid/tcp.pid",
       'log' => $_tmpDir . '/log/tcp.log',
       'swoole' => [
           'worker_num' => 10,
           'max_request' => 20,
           'task_worker_num' => 10,
       ]
   ],
];
```

## 服务端命令行工具

用于启动各类服务

```
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

# http server
php ../bin/apiServer.php -b init.inc.php -a start -t http -d

# doc server
php bin/apiServer.php -t doc -a start -b example/init.inc.php -v

```

## 测试

## 启动测试服务

使用命令行工具启动api独立服务
```
php bin/apiServer.php -t http -a start -b example/init.inc.php
php bin/apiServer.php -t tcp -a start -b example/init.inc.php 
php bin/apiServer.php -t yar -a start -b example/init.inc.php
```

配置nginx启用fpm模式下的api服务
```
# 参考示例
server {
    server_name  api.j7.x2.cn;
    root  /data/www/jframe/j-api/example/www; # 替换成你的地址
    index index.php;
    include php7.0.conf;
}
```

## 客户端
安装客户端
> composer require stcer/j-api-client

配置服务端地址
> @see example/client/init.inc.php

运行测试
> php example/client/clientTest.php

性能测试
>  php example/client/benchmarkTest.php

```
# 完成44次远程设计
array(5) {
  ["http"]=> float(0.42368),        nginx + fpm + php7
  ["yar"]=> float(0.45212),         nginx + fpm + php7 + ext-yar
  ["httpSwoole"]=> float(0.0587),   php7 + swoole2.2
  ["yarSwoole"]=> float(0.07076),   php7 + swoole2.2
  ["tcp"]=>  float(0.06179),        php7 + swoole2.2
}
```

# 设计

## 请求参数打包方式
*   http 请求, 通过http_build_query将 args, init参数进行编码， 解决参数为数组时的情况
*   tcp client默认使用 MSGPACK 方式编码数据包
*   yar默认支持 MSGPACK, json, php序列化

## 字符编码
*   服务端api接口数据编码方式受 $server->charset控制
*   所有客户端的请求参数为utf8编码

## 地址格式
*   Fpm http : server_url?api=&args=&init=
*   Swoole http: 
    1. server_url/api/API_NAME?args=&init= 
    2. server_url/calls?data=REQUESTS
*   Swoole yar: (method=yar)
    1. server_url/API_NAME?args=&init= 
    2. server_url/multiple

## TODO

1. 版本支持, 参考api-simple
2. mock数据支持, 参考 api-simple
3. server 日志, 错误及调试日志


## 异常记录

注意文档编码

```
未设置task_worker_nums
 WARNING swManager_check_exit_status: worker#6 abnormal exit, status=0, signal=11
```