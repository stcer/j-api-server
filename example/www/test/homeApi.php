<?php

ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

use j\debug\Profiler;
use j\api\client\Base;
use j\api\client\SwooleYar as ClientYar;
use j\api\client\Tcp as ClientTcp;
use j\api\client\FpmYar as ClientFpmYar;
use j\api\client\HttpJson as FpmHttpJson;
use j\api\client\SwooleHttp;

$vendorPath = realpath(__DIR__ . "/../../../vendor/");
$loader = include($vendorPath . "/autoload.php");

/**
 * main()
 */
if(isset($_GET['type'])){
    header('Content-Type:text/html;charset=utf-8');
    echo "<pre/>";
    Profiler::start();
    HomeApiTest::run($_GET['type']);
    Profiler::stop(true);
} else {
    echo "<h1>家居网 api批量调用测试</h1>\n";
    echo "<p><a href='http://git.9z.cn/j_example.git/blob/master/api/test/homeApi.php' target='_blank'>测试代码</a> -- ";
    echo "<a href='http://jzf.x1.cn/xhprof/xhprof_html/index.php' target='_blank'>测试结果</a></p>\n";
    $links = [
        'fpmHttp', // 	280,161 microsecs, 153,360 bytes
        'swooleHttp', // 244,080 microsecs, 154,624 bytes
        'swooleYar', // 225,692 microsecs, 102,616 bytes
        'tcp', // 228,264 microsecs, 178,816 bytes
        ];
    foreach($links as $link){
        echo "<a href='?type={$link}' target='_blank'>{$link}</a> ";
    }
}

/**
 * Class LocalTest
 */
class HomeApiTest {
    /**
     * @param string $type
     * @return array
     */
    static function run($type) {
        $client = self::getClient($type);
        $id = 494;
        $params = ['brand_id' => $id, 'nums' => 2, 'page' => 1];
        $apis = [
            //'cats' => ['brand.goods.getCats', $params],
            'goodsList' => ['brand.goods.getList', $params],
            'companyList' => ['brand.Company.getList',  $params],
            'newsList' => ['brand.news.getList',   $params],
            'evaList' => ['brand.evaluate.getList',   $params],
            'brandCats' => ['brand.brand.getList', [
                'gcid' => '004002',
                'nums' => 2,
                'page' => 1
            ]],
        ];

        $timer = new \j\debug\Timer();
        $timer->start();
        foreach($apis as $api){
            $rs = $client->callApi($api[0], $api[1]);
        }

        echo "call 5 times: " .  $timer->stop();
        echo "<br />";

        $rs = $client->calls($apis);
        echo "call 1 times: " .  $timer->stop();
        echo "<br >\n";

        echo count($rs);
        echo "<br >\n";

        foreach($rs as $key => $data){
            if(isset($data['time'])){
                echo "{$key}:{$data['time']}<br />\n";
            }
        }
    }

    /**
     * @param $type
     * @return Base
     * @throws Exception
     */
    static function getClient($type) {
        switch ($type) {
            case 'fpmHttp' :
                $client = FpmHttpJson::getInstance();
                $client->serverAddress = 'http://api1.homenew.9z.cn/index.php';
                break;
            case 'tcp' :
            // 9103
                $client = ClientTcp::getInstance();
                $client->server = '192.168.0.252';
                $client->port = 9103;
                break;
            case 'swooleHttp' :
            // 9101
                $client = SwooleHttp::getInstance();
                $client->serverAddress = 'http://192.168.0.252:9101';
                break;
            case 'swooleYar' :
            // 9102
                ClientYar::$serverUrl = 'http://192.168.0.252:9102';
                $client = ClientYar::getInstance();
                break;
            default :
                throw new Exception("Invalid client type");
        }

        return $client;
    }
}
