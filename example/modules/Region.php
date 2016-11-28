<?php

namespace api;

use j\db\Table;

/**
 * Class 地区组件
 * @package comm
 */
class Region{

    protected static $instance;

    /**
     * @return Region
     */
    public static function getInstance() {
        if (!isset(static::$instance)) {
            self::$instance = new static();
        }

        return static::$instance;
    }

    protected $table = 'region';
    protected $tableInstance;

    /**
     * @return \j\db\Table
     */
    protected function tableInstance(){
        if(!isset($this->tableInstance)){
            $this->tableInstance = Table::factory($this->table);
            $this->tableInstance->setWhereConf('pids', array('pid', 'in'));
        }
        return $this->tableInstance;
    }

    /**
     * @param array $cond
     * @return \j\db\driver\mysqli\ResultSet
     * @throws Exception
     */
    protected function getQuery($cond = array()){
        if(is_numeric($cond)){
            $cond = array('id' => $cond);
        }
        if(!is_array($cond)){
            throw new Exception("Invalid cid", Exception::Arguments);
        }
        if(!isset($cond['_fields'])){
            $cond['_fields'] = ['id', 'name', 'pid'];
        }
        return $this->tableInstance()->find($cond);
    }

    /**
     * 获取下级子
     * @param $cid
     * @return array 所有子
     */
    public function getChild($cid) {
        $cid = intval($cid);
        return $this->getQuery(['pid' => $cid])->toArray('id');
    }

    public function getChildren($cid, $maxLevel = 10){
        $level = 1;
        $data = $this->getChild($cid);
        $ids = array_keys($data);
        while($ids && $level < $maxLevel){
            $level++;
            $child = $this->getQuery(['pids' => $ids])->toArray('id');
            if($child){
                $ids = array_keys($child);
                $data += $child;
            }else{
                break;
            }
        }

        return $data;
    }

    public function getParent($cid) {
        $info = $this->getQuery($cid)->current();
        if(!$info || !$info['pid']){
            return [];
        }

        return $this->getQuery($info['pid'])->current();
    }

    public function getParents($cid, $self = false,$maxLevel = 10){
        $parents = array();
        if($self){
            $parents[$cid] = $this->getCat($cid);
        }
        $i = 0;
        while($parent = $this->getParent($cid)){
            if($i++ > $maxLevel){
                break;
            }
            $parents[$parent['id']] = $parent;
            $cid = $parent['id'];
        }

        return array_reverse($parents, true);
    }

    public function getName($cid){
        $info = $this->getQuery($cid)->current();
        if($info){
            return $info['name'];
        }

        return '';
    }

    public function getCat($cid){
        return $this->getQuery($cid)->current();
    }

    public function getFullName($cid, $spChar = ' - '){
        $parents = $this->getParents($cid);
        $names = array();
        foreach ($parents as $node) {
            $names[] = $node['name'];
        }
        $names[] = $this->getName($cid);
        return implode($names, $spChar);
    }

    /**
     * 类别深度
     * @param $cid
     * @return int
     */
    public function getDeep($cid){
        return count($this->getParents($cid));
    }
}