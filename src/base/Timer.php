<?php
namespace j\api\base;

/**
 * Class Timer
 * @package j\api\base
 */
class Timer{
    protected $startTime;

    function __construct($autoStart = false){
        if($autoStart){
            $this->start();
        }
    }

    function start(){
        $this->startTime = $this->_time();
    }

    function stop($echo = false, $str = ''){
        $time = $this->_time();
        $times = round($time - $this->startTime, 5);
        $this->startTime = $time;

        if($echo){
            echo $str . $times . "\n";
        }
        return $times;
    }

    protected function _time(){
        $mtime = microtime ();
        $mtime = explode (' ', $mtime);
        return $mtime[1] + $mtime[0];
    }
}
