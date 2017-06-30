<?php

namespace j\api;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FilesystemIterator;
use RegexIterator;

use ReflectionMethod;
use Reflection;
use ReflectionClass;

use Phalcon\Annotations\Adapter\Memory as MemoryAdapter;
use Phalcon\Annotations\Reader;
use Phalcon\Annotations\Reflection as PhalconReflection;

/**
 * Class Document
 * @package j\api
 */
class Document extends Base{

    public $apiPath;
    public $apiFilePattern = '/\.php$/';

    /**
     * @var Loader
     */
    public $loader;

    /**
     * @param $apiPath
     */
    public function setApiPath($apiPath){
        $this->apiPath = rtrim($apiPath, '/');
    }

    /**
     * Document constructor.
     * @param $loader
     */
    public function __construct($loader = null) {
        if(!$loader){
            $loader = Loader::getInstance();
        }
        $this->loader = $loader;
    }

    /**
     * @param $path
     * @param $pattern
     * @param bool $recursion
     * @return RegexIterator
     */
    protected function getFiles($path, $pattern, $recursion = false){
        if($recursion){
            $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        } else {
            $it = new FilesystemIterator($path);
        }

        return new RegexIterator($it, $pattern);
    }

    protected $excludeClasses = [
        'base',
        'exception',
        ];

    function addExcludeClass($class){
        $this->excludeClasses[] = $class;
    }

    function getApiList(){
        $files = $this->getFiles($this->apiPath, $this->apiFilePattern, true);

        $data = array();
        foreach($files as $file){
            $name = str_replace($this->apiPath . '/', '', $file->getPathName());
            $name = str_replace('.php', '', $name);
            $name = preg_replace("/{$this->loader->classSuffix}$/", '', $name);
            $name = str_replace('/', '.', $name);

            if(in_array(strtolower($name), $this->excludeClasses)){
                continue;
            }
            $data[] = $name;
        }

        return $data;
    }

    function getInitParams($api){
        $className = $this->loader->getClass($api, [], true);

        $reader = new Reader();
        $parsing = $reader->parse($className);
        $reflection = new PhalconReflection($parsing);
        $classAnnotations = $reflection->getClassAnnotations();

        if(!$classAnnotations || !$classAnnotations->has('apiInit')){
            return [];
        }

        $annotation = $classAnnotations->get('apiInit');
        $params =  $annotation->getArguments();
        if(!is_array($params)){
            return [];
        }

        return $params;
    }

    function getApiDocument($api, $initParams = []){
        $object = $this->loader->getClass($api, $initParams);
        $class = new ReflectionClass(get_class($object));
        $data = [
            'method' => $this->getMethods($class),
            'document' => $class->getDocComment()
            ];
        if($object instanceof Base
            && method_exists($object, 'getModel')
            && ($model = $object->getModel())
        ){
            $class = new ReflectionClass(get_class($model));
            $data['document'] .= "\n" . $class->getDocComment();
            $allows = null;

            if(method_exists($object, 'getDefaultModelMethods')){
                $allows = $object->getDefaultModelMethods();
            }
            $methods = $this->getMethods($class, $allows);
            $data['method'] = array_merge($data['method'], $methods) ;
        }
        return $data;
    }

    protected $excludeMethods = [
        'getDefaultModelMethods',
        'getErrors',
        '__construct',
        '__get',
        '__set',
        '__call',
        '__sleep',
        '__sleep',
        '__toString',
        '__invoke',
        '__clone',
        'getInstance',
        'getModel',
        'handle',
        'isPublic',
        'bootstrap',
        'yar',
        'setAction'
    ];

    /**
     * @param \ReflectionClass $class
     * @param array $allows
     * @param bool $simple
     * @return array
     */
    protected function getMethods($class, $allows = null, $simple = true){
        //$properties = $class->getProperties();
        $data = array();

        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        $className = $class->getName();
        foreach($methods as $method){
            $name = $method->getName();
            if(in_array($name, $this->excludeMethods) || is_array($allows) && !in_array($name, $allows)){
                continue;
            }

            $item = array();
            $item['doc'] = $method->getDocComment();
            $item['desc'] = $this->getMethodTitle($className, $name);

            $item['name'] = $name;
            $item['modifier'] = implode(' ', Reflection::getModifierNames($method->getModifiers()));
            $item['args'] = [];

            $func = '';
            $func .=  ($simple ? '' : $item['modifier']) . ' ' . $item['name'] . "(" ;
            $params = $method->getParameters();
            $argsString = [];
            foreach($params as $param){
                $arg = []; $expression = '';
                $arg['name'] = $param->getName();
                $expression .= $arg['name'];
                if($param->isDefaultValueAvailable()){
                    $arg['value'] = $param->getDefaultValue();
                    $expression .=  " = " . str_replace("\n", "", var_export($arg['value'], true));
                } else {
                    $arg['value'] = "";
                }
                $item['args'][] = $arg;
                $argsString[] = $expression;
            }
            $func .= implode(', ', $argsString) . ")";
            $item['func'] = $func;

            $data[] = $item;
        }

        return $data;
    }

    /**
     * @param string $class
     * @param string $method
     * @return string
     */
    protected function getMethodTitle($class, $method){
        $reader = new MemoryAdapter();
        $annotations = $reader->getMethod($class, $method);
        if(!$annotations->has('desc')){
            return '';
        }

        $annotation = $annotations->get('desc');
        if($annotation->numberArguments() == 0){
            return '';
        }

        $title = $annotation->getArguments()[0];
        if(!is_string($title)){
            return '';
        }

        return $title;
    }
}